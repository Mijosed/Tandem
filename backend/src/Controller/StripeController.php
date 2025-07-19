<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\Payment;
use App\Entity\Subscription;
use App\Service\StripeService;
use App\Repository\UserRepository;
use App\Repository\SubscriptionRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;

#[Route('/api/stripe', name: 'stripe_')]
class StripeController extends AbstractController
{
    public function __construct(
        private StripeService $stripeService,
        private EntityManagerInterface $entityManager,
        private UserRepository $userRepository,
        private SubscriptionRepository $subscriptionRepository
    ) {}

    #[Route('/config', name: 'config', methods: ['GET'])]
    public function getConfig(): JsonResponse
    {
        return new JsonResponse([
            'publishableKey' => $this->stripeService->getPublishableKey()
        ]);
    }

    #[Route('/create-payment-intent', name: 'create_payment_intent', methods: ['POST'])]
    public function createPaymentIntent(Request $request): JsonResponse
    {
        error_log("=== DÉBUT createPaymentIntent CARTE BANCAIRE UNIQUEMENT ===");
        
        try {
            // Récupérer l'utilisateur depuis le header X-User-ID
            $userId = $request->headers->get('X-User-ID');
            if (!$userId) {
                error_log("User ID manquant dans les headers");
                return new JsonResponse(['error' => 'User ID required'], 400);
            }

            error_log("User ID reçu: " . $userId);

            $user = $this->userRepository->find($userId);
            if (!$user) {
                error_log("Utilisateur non trouvé: " . $userId);
                return new JsonResponse(['error' => 'User not found'], 404);
            }

            error_log("Utilisateur trouvé: " . $user->getEmail());

            $data = json_decode($request->getContent(), true);
            $plan = $data['plan'] ?? 'monthly';
            
            // Prix selon le plan
            $amount = match($plan) {
                'monthly' => 999, // 9.99€
                'yearly' => 9900, // 99€
                default => 999
            };

            error_log("Création PaymentIntent pour montant: {$amount}€ (carte bancaire uniquement)");

            $paymentIntent = $this->stripeService->createPaymentIntent($user, $amount);
            
            error_log("PaymentIntent créé: " . $paymentIntent->id);

            // Créer ou récupérer l'abonnement de l'utilisateur
            $subscription = $user->getSubscription();
            if (!$subscription) {
                $subscription = new Subscription();
                $subscription->setUser($user);
                $subscription->setPlan($plan);
                $subscription->setStatus('incomplete');
                $subscription->setCurrentPeriodStart(new \DateTime());
                $subscription->setCurrentPeriodEnd(new \DateTime('+1 month'));
                $this->entityManager->persist($subscription);
                $user->setSubscription($subscription);
            }

            // Créer l'enregistrement de paiement
            $payment = new Payment();
            $payment->setAmount($amount);
            $payment->setCurrency('EUR');
            $payment->setStatus('pending');
            $payment->setStripePaymentIntentId($paymentIntent->id);
            $payment->setSubscription($subscription);

            $this->entityManager->persist($payment);
            $this->entityManager->flush();

            error_log("Données sauvegardées en base");

            return new JsonResponse([
                'clientSecret' => $paymentIntent->client_secret,
                'paymentIntentId' => $paymentIntent->id,
                'amount' => $amount,
                'currency' => 'eur',
                'paymentMethodTypes' => ['card'] // Confirmer que seules les cartes sont acceptées
            ]);

        } catch (\Exception $e) {
            error_log("Erreur dans createPaymentIntent: " . $e->getMessage());
            error_log("Stack trace: " . $e->getTraceAsString());
            return new JsonResponse([
                'error' => $e->getMessage()
            ], 500);
        }
    }

    #[Route('/create-subscription', name: 'create_subscription', methods: ['POST'])]
    public function createSubscription(Request $request): JsonResponse
    {
        // Récupérer l'utilisateur depuis le header X-User-ID (temporaire pour dev)
        $userId = $request->headers->get('X-User-ID');
        
        if (!$userId) {
            return new JsonResponse(['error' => 'User ID required'], 400);
        }

        $user = $this->userRepository->find($userId);
        if (!$user) {
            return new JsonResponse(['error' => 'User not found'], 404);
        }

        try {
            $data = json_decode($request->getContent(), true);
            $priceId = $data['priceId'] ?? 'price_premium_monthly';

            $stripeSubscription = $this->stripeService->createSubscription($user, $priceId);

            // Créer ou mettre à jour l'abonnement en base
            $subscription = $user->getSubscription();
            if (!$subscription) {
                $subscription = new Subscription();
                $subscription->setUser($user);
            }

            $subscription->setPlan('premium');
            $subscription->setStatus('pending');
            $subscription->setStripeSubscriptionId($stripeSubscription->id);
            $subscription->setCurrentPeriodStart(new \DateTime());
            $subscription->setCurrentPeriodEnd(new \DateTime('+1 month'));

            $this->entityManager->persist($subscription);
            $this->entityManager->flush();

            return new JsonResponse([
                'subscriptionId' => $stripeSubscription->id,
                'clientSecret' => $stripeSubscription->latest_invoice->payment_intent->client_secret
            ]);

        } catch (\Exception $e) {
            return new JsonResponse(['error' => $e->getMessage()], 500);
        }
    }

    #[Route('/webhook', name: 'webhook', methods: ['POST'])]
    public function webhook(Request $request): Response
    {
        $payload = $request->getContent();
        $signature = $request->headers->get('stripe-signature');

        try {
            $event = $this->stripeService->verifyWebhook($payload, $signature);

            // Traiter les différents types d'événements
            switch ($event->type) {
                case 'payment_intent.succeeded':
                    $this->handlePaymentIntentSucceeded($event->data->object);
                    break;

                case 'invoice.payment_succeeded':
                    $this->handleInvoicePaymentSucceeded($event->data->object);
                    break;

                case 'customer.subscription.deleted':
                    $this->handleSubscriptionDeleted($event->data->object);
                    break;

                default:
                    // Événement non géré
                    break;
            }

            return new Response('', 200);

        } catch (\Exception $e) {
            return new Response('Webhook error: ' . $e->getMessage(), 400);
        }
    }

    #[Route('/activate-subscription/{userId}', name: 'activate_subscription', methods: ['POST'])]
    public function activateSubscription(int $userId): JsonResponse
    {
        $user = $this->userRepository->find($userId);
        if (!$user) {
            return new JsonResponse(['error' => 'User not found'], 404);
        }

        try {
            // Créer ou mettre à jour l'abonnement
            $subscription = $user->getSubscription();
            if (!$subscription) {
                $subscription = new Subscription();
                $subscription->setUser($user);
                $this->entityManager->persist($subscription);
            }

            $subscription->setPlan('premium');
            $subscription->setStatus('active');
            $subscription->setCurrentPeriodStart(new \DateTime());
            $subscription->setCurrentPeriodEnd(new \DateTime('+1 month'));

            $this->entityManager->flush();

            return new JsonResponse([
                'success' => true,
                'message' => 'Abonnement activé avec succès',
                'subscription' => [
                    'plan' => $subscription->getPlan(),
                    'status' => $subscription->getStatus(),
                    'currentPeriodEnd' => $subscription->getCurrentPeriodEnd()->format('Y-m-d H:i:s')
                ]
            ]);

        } catch (\Exception $e) {
            return new JsonResponse([
                'error' => 'Erreur lors de l\'activation: ' . $e->getMessage()
            ], 500);
        }
    }

    #[Route('/subscription-status/{userId}', name: 'subscription_status', methods: ['GET'])]
    public function getSubscriptionStatus(int $userId): JsonResponse
    {
        $user = $this->userRepository->find($userId);
        if (!$user) {
            return new JsonResponse(['error' => 'User not found'], 404);
        }

        $subscription = $user->getSubscription();
        
        if (!$subscription) {
            return new JsonResponse([
                'plan' => 'free',
                'status' => 'inactive',
                'isPremium' => false
            ]);
        }

        $isPremium = $subscription->getPlan() === 'premium' && 
                    $subscription->getStatus() === 'active' &&
                    $subscription->getCurrentPeriodEnd() > new \DateTime();

        return new JsonResponse([
            'plan' => $subscription->getPlan(),
            'status' => $subscription->getStatus(),
            'isPremium' => $isPremium,
            'currentPeriodEnd' => $subscription->getCurrentPeriodEnd()?->format('Y-m-d H:i:s')
        ]);
    }

    private function handlePaymentIntentSucceeded($paymentIntent): void
    {
        // Trouver le paiement en base
        $payment = $this->entityManager->getRepository(Payment::class)
            ->findOneBy(['stripePaymentIntentId' => $paymentIntent->id]);

        if ($payment) {
            $payment->setStatus('completed');
            $payment->setPaidAt(new \DateTime());

            // Activer l'abonnement premium
            $subscription = $payment->getSubscription();
            $user = $subscription->getUser();
            
            if (!$subscription) {
                $subscription = new Subscription();
                $subscription->setUser($user);
            }

            $subscription->setPlan('premium');
            $subscription->setStatus('active');
            $subscription->setCurrentPeriodStart(new \DateTime());
            
            // Durée selon le montant payé
            $duration = $payment->getAmount() >= 5000 ? '+1 year' : '+1 month';
            $subscription->setCurrentPeriodEnd(new \DateTime($duration));

            $this->entityManager->persist($subscription);
            $this->entityManager->flush();
        }
    }

    private function handleInvoicePaymentSucceeded($invoice): void
    {
        // Gérer le renouvellement d'abonnement
        if (isset($invoice->subscription)) {
            $subscription = $this->subscriptionRepository
                ->findOneBy(['stripeSubscriptionId' => $invoice->subscription]);

            if ($subscription) {
                $subscription->setStatus('active');
                $subscription->setCurrentPeriodStart(new \DateTime('@' . $invoice->period_start));
                $subscription->setCurrentPeriodEnd(new \DateTime('@' . $invoice->period_end));

                $this->entityManager->flush();
            }
        }
    }

    private function handleSubscriptionDeleted($stripeSubscription): void
    {
        $subscription = $this->subscriptionRepository
            ->findOneBy(['stripeSubscriptionId' => $stripeSubscription->id]);

        if ($subscription) {
            $subscription->setStatus('cancelled');
            $this->entityManager->flush();
        }
    }
}
