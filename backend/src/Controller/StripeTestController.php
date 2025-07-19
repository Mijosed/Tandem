<?php

namespace App\Controller;

use App\Service\StripeService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/stripe-test', name: 'stripe_test_')]
class StripeTestController extends AbstractController
{
    public function __construct(
        private StripeService $stripeService
    ) {}

    #[Route('/config', name: 'config', methods: ['GET'])]
    public function getConfig(): JsonResponse
    {
        return new JsonResponse([
            'publishableKey' => $this->stripeService->getPublishableKey()
        ]);
    }

    #[Route('/simple-payment', name: 'simple_payment', methods: ['POST'])]
    public function createSimplePayment(): JsonResponse
    {
        try {
            // Créer un PaymentIntent simple sans utilisateur
            $stripe = new \Stripe\StripeClient($_ENV['STRIPE_SECRET_KEY']);
            
            $paymentIntent = $stripe->paymentIntents->create([
                'amount' => 999,
                'currency' => 'eur',
                'automatic_payment_methods' => [
                    'enabled' => true,
                ],
                'metadata' => [
                    'test' => 'simple_payment'
                ]
            ]);

            return new JsonResponse([
                'clientSecret' => $paymentIntent->client_secret,
                'paymentIntentId' => $paymentIntent->id
            ]);

        } catch (\Exception $e) {
            return new JsonResponse([
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
