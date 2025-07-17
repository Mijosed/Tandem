<?php

namespace App\Controller;

use App\Entity\Payment;
use App\Entity\Subscription;
use App\Repository\PaymentRepository;
use App\Repository\SubscriptionRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/subscription', name: 'api_subscription_')]
class SubscriptionController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private SubscriptionRepository $subscriptionRepository,
        private PaymentRepository $paymentRepository,
        private UserRepository $userRepository
    ) {}

    #[Route('/{userId}', name: 'get', methods: ['GET'])]
    public function getSubscription(int $userId): JsonResponse
    {
        $user = $this->userRepository->find($userId);
        if (!$user) {
            return $this->json(['error' => 'Utilisateur non trouvé'], 404);
        }

        $subscription = $this->subscriptionRepository->findByUser($userId);
        if (!$subscription) {
            return $this->json(['error' => 'Abonnement non trouvé'], 404);
        }

        return $this->json([
            'subscription' => [
                'id' => $subscription->getId(),
                'plan' => $subscription->getPlan(),
                'status' => $subscription->getStatus(),
                'nextBilling' => $subscription->getNextBilling()?->format('Y-m-d'),
                'isPremium' => $subscription->isPremium(),
                'createdAt' => $subscription->getCreatedAt()->format('Y-m-d H:i:s'),
                'updatedAt' => $subscription->getUpdatedAt()->format('Y-m-d H:i:s')
            ]
        ]);
    }

    #[Route('/{userId}/upgrade', name: 'upgrade', methods: ['POST'])]
    public function upgradeSubscription(int $userId, Request $request): JsonResponse
    {
        $user = $this->userRepository->find($userId);
        if (!$user) {
            return $this->json(['error' => 'Utilisateur non trouvé'], 404);
        }

        $subscription = $this->subscriptionRepository->findByUser($userId);
        if (!$subscription) {
            return $this->json(['error' => 'Abonnement non trouvé'], 404);
        }

        $data = json_decode($request->getContent(), true);
        
        if ($subscription->getPlan() === 'premium') {
            return $this->json(['error' => 'Utilisateur déjà premium'], 400);
        }

        // Créer un PaymentIntent avec Stripe
        $clientSecret = $this->createStripePaymentIntent($user, 9.99);

        if (!$clientSecret) {
            return $this->json(['error' => 'Erreur lors de la création du paiement'], 500);
        }

        // Créer l'enregistrement de paiement
        $payment = new Payment();
        $payment->setUser($user);
        $payment->setAmount(9.99);
        $payment->setCurrency('EUR');
        $payment->setStatus('pending');
        $payment->setStripeClientSecret($clientSecret);
        $payment->setDescription('Mise à niveau vers Premium');

        $this->entityManager->persist($payment);
        $this->entityManager->flush();

        return $this->json([
            'clientSecret' => $clientSecret,
            'amount' => 999, // En centimes pour Stripe
            'currency' => 'eur',
            'paymentId' => $payment->getId()
        ]);
    }

    #[Route('/{userId}/cancel', name: 'cancel', methods: ['POST'])]
    public function cancelSubscription(int $userId): JsonResponse
    {
        $user = $this->userRepository->find($userId);
        if (!$user) {
            return $this->json(['error' => 'Utilisateur non trouvé'], 404);
        }

        $subscription = $this->subscriptionRepository->findByUser($userId);
        if (!$subscription) {
            return $this->json(['error' => 'Abonnement non trouvé'], 404);
        }

        if ($subscription->getPlan() === 'free') {
            return $this->json(['error' => 'Impossible d\'annuler un abonnement gratuit'], 400);
        }

        // Annuler l'abonnement Stripe
        if ($subscription->getStripeSubscriptionId()) {
            $this->cancelStripeSubscription($subscription->getStripeSubscriptionId());
        }

        // Mettre à jour l'abonnement
        $subscription->setStatus('cancelled');
        $subscription->setCancelledAt(new \DateTime());

        $this->entityManager->flush();

        return $this->json([
            'message' => 'Abonnement annulé avec succès',
            'subscription' => [
                'plan' => $subscription->getPlan(),
                'status' => $subscription->getStatus(),
                'cancelledAt' => $subscription->getCancelledAt()->format('Y-m-d H:i:s')
            ]
        ]);
    }

    #[Route('/{userId}/payments', name: 'payments', methods: ['GET'])]
    public function getPayments(int $userId): JsonResponse
    {
        $user = $this->userRepository->find($userId);
        if (!$user) {
            return $this->json(['error' => 'Utilisateur non trouvé'], 404);
        }

        $payments = $this->paymentRepository->findCompletedPayments($userId);

        return $this->json([
            'payments' => array_map(function($payment) {
                return [
                    'id' => $payment->getId(),
                    'amount' => $payment->getAmount(),
                    'currency' => $payment->getCurrency(),
                    'status' => $payment->getStatus(),
                    'description' => $payment->getDescription(),
                    'date' => $payment->getPaidAt()?->format('Y-m-d'),
                    'createdAt' => $payment->getCreatedAt()->format('Y-m-d H:i:s')
                ];
            }, $payments)
        ]);
    }

    #[Route('/webhook/stripe', name: 'stripe_webhook', methods: ['POST'])]
    public function handleStripeWebhook(Request $request): JsonResponse
    {
        $payload = $request->getContent();
        $sig_header = $request->headers->get('stripe-signature');

        try {
            // Vérifier la signature du webhook Stripe
            // $event = \Stripe\Webhook::constructEvent($payload, $sig_header, $endpoint_secret);
            
            $event = json_decode($payload, true);
            
            switch ($event['type']) {
                case 'payment_intent.succeeded':
                    $this->handlePaymentSuccess($event['data']['object']);
                    break;
                case 'payment_intent.payment_failed':
                    $this->handlePaymentFailed($event['data']['object']);
                    break;
                case 'invoice.payment_succeeded':
                    $this->handleSubscriptionPayment($event['data']['object']);
                    break;
                case 'customer.subscription.deleted':
                    $this->handleSubscriptionCancelled($event['data']['object']);
                    break;
                default:
                    // Event non géré
                    break;
            }

            return $this->json(['status' => 'success']);
        } catch (\Exception $e) {
            return $this->json(['error' => 'Webhook error: ' . $e->getMessage()], 400);
        }
    }

    private function createStripePaymentIntent($user, float $amount): ?string
    {
        // Simulation - remplacez par l'intégration Stripe réelle
        return 'pi_' . uniqid() . '_secret_' . uniqid();
    }

    private function cancelStripeSubscription(string $subscriptionId): bool
    {
        // Simulation - remplacez par l'intégration Stripe réelle
        return true;
    }

    private function handlePaymentSuccess(array $paymentIntent): void
    {
        $payment = $this->paymentRepository->findByStripePaymentIntentId($paymentIntent['id']);
        if ($payment) {
            $payment->setStatus('completed');
            $payment->setPaidAt(new \DateTime());

            // Mettre à jour l'abonnement si c'est un upgrade
            if ($payment->getDescription() === 'Mise à niveau vers Premium') {
                $subscription = $this->subscriptionRepository->findByUser($payment->getUser()->getId());
                if ($subscription) {
                    $subscription->setPlan('premium');
                    $subscription->setStatus('active');
                    $nextBilling = new \DateTime();
                    $nextBilling->add(new \DateInterval('P1M'));
                    $subscription->setNextBilling($nextBilling);
                }
            }

            $this->entityManager->flush();
        }
    }

    private function handlePaymentFailed(array $paymentIntent): void
    {
        $payment = $this->paymentRepository->findByStripePaymentIntentId($paymentIntent['id']);
        if ($payment) {
            $payment->setStatus('failed');
            $this->entityManager->flush();
        }
    }

    private function handleSubscriptionPayment(array $invoice): void
    {
        // Gérer les paiements récurrents
        // Mettre à jour la prochaine date de facturation
    }

    private function handleSubscriptionCancelled(array $subscription): void
    {
        // Gérer l'annulation d'abonnement côté Stripe
        // Mettre à jour le statut local
    }
}
