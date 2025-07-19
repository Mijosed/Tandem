<?php

namespace App\Service;

use Stripe\Stripe;
use Stripe\StripeClient;
use Stripe\PaymentIntent;
use Stripe\Customer;
use App\Entity\User;
use App\Entity\Subscription;

class StripeService
{
    private StripeClient $stripe;
    private string $secretKey;
    private string $publishableKey;

    public function __construct()
    {
        $this->secretKey = $_ENV['STRIPE_SECRET_KEY'] ?? '';
        $this->publishableKey = $_ENV['STRIPE_PUBLISHABLE_KEY'] ?? '';
        
        if (empty($this->secretKey) || empty($this->publishableKey)) {
            throw new \Exception('Clés Stripe manquantes dans les variables d\'environnement');
        }
        
        Stripe::setApiKey($this->secretKey);
        $this->stripe = new StripeClient($this->secretKey);
    }

    public function getPublishableKey(): string
    {
        return $this->publishableKey;
    }

    /**
     * Créer un client Stripe pour un utilisateur
     */
    public function createCustomer(User $user): Customer
    {
        return $this->stripe->customers->create([
            'email' => $user->getEmail(),
            'name' => $user->getFirstName() . ' ' . $user->getLastName(),
            'metadata' => [
                'user_id' => $user->getId()
            ]
        ]);
    }

    /**
     * Créer un PaymentIntent pour un paiement premium (carte bancaire uniquement)
     */
    public function createPaymentIntent(User $user, int $amount = 999): PaymentIntent
    {
        try {
            return $this->stripe->paymentIntents->create([
                'amount' => $amount, // 9.99€ en centimes
                'currency' => 'eur',
                'payment_method_types' => ['card'], // Seulement les cartes bancaires
                'metadata' => [
                    'user_id' => $user->getId(),
                    'user_email' => $user->getEmail(),
                    'plan' => 'premium'
                ]
            ]);
        } catch (\Exception $e) {
            throw new \Exception('Erreur lors de la création du PaymentIntent: ' . $e->getMessage());
        }
    }

    /**
     * Créer un abonnement mensuel récurrent
     */
    public function createSubscription(User $user, string $priceId = 'price_premium_monthly'): \Stripe\Subscription
    {
        // Créer ou récupérer le client Stripe
        $customer = $this->createCustomer($user);

        return $this->stripe->subscriptions->create([
            'customer' => $customer->id,
            'items' => [
                ['price' => $priceId]
            ],
            'payment_behavior' => 'default_incomplete',
            'payment_settings' => [
                'save_default_payment_method' => 'on_subscription'
            ],
            'expand' => ['latest_invoice.payment_intent'],
            'metadata' => [
                'user_id' => $user->getId()
            ]
        ]);
    }

    /**
     * Récupérer un PaymentIntent
     */
    public function getPaymentIntent(string $paymentIntentId): PaymentIntent
    {
        return $this->stripe->paymentIntents->retrieve($paymentIntentId);
    }

    /**
     * Récupérer un abonnement
     */
    public function getSubscription(string $subscriptionId): \Stripe\Subscription
    {
        return $this->stripe->subscriptions->retrieve($subscriptionId);
    }

    /**
     * Annuler un abonnement
     */
    public function cancelSubscription(string $subscriptionId): \Stripe\Subscription
    {
        return $this->stripe->subscriptions->cancel($subscriptionId);
    }

    /**
     * Vérifier un webhook Stripe
     */
    public function verifyWebhook(string $payload, string $signature): \Stripe\Event
    {
        $webhookSecret = $_ENV['STRIPE_WEBHOOK_SECRET'] ?? '';
        
        return \Stripe\Webhook::constructEvent(
            $payload,
            $signature,
            $webhookSecret
        );
    }

    /**
     * Créer les prix de base dans Stripe (à exécuter une fois)
     */
    public function createPrices(): array
    {
        // Créer d'abord le produit
        $product = $this->stripe->products->create([
            'name' => 'Accès Premium Tandem',
            'description' => 'Accès illimité aux offres d\'emploi en alternance'
        ]);

        // Prix mensuel premium
        $monthlyPrice = $this->stripe->prices->create([
            'unit_amount' => 999, // 9.99€
            'currency' => 'eur',
            'recurring' => ['interval' => 'month'],
            'product' => $product->id
        ]);

        // Prix annuel premium (avec réduction)
        $yearlyPrice = $this->stripe->prices->create([
            'unit_amount' => 9900, // 99€ (économie de 20€)
            'currency' => 'eur',
            'recurring' => ['interval' => 'year'],
            'product' => $product->id
        ]);

        return [
            'monthly' => $monthlyPrice,
            'yearly' => $yearlyPrice
        ];
    }
}
