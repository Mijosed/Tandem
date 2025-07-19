<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/test-simple', name: 'test_simple_')]
class SimpleTestController extends AbstractController
{
    #[Route('/ping', name: 'ping', methods: ['GET'])]
    public function ping(): JsonResponse
    {
        error_log("=== PING SIMPLE ===");
        return new JsonResponse(['message' => 'pong', 'time' => date('Y-m-d H:i:s')]);
    }

    #[Route('/stripe-direct', name: 'stripe_direct', methods: ['POST'])]
    public function stripeDirect(): JsonResponse
    {
        error_log("=== STRIPE DIRECT ===");
        
        try {
            // Direct Stripe call sans aucune dépendance
            $stripe = new \Stripe\StripeClient($_ENV['STRIPE_SECRET_KEY']);
            
            $paymentIntent = $stripe->paymentIntents->create([
                'amount' => 999,
                'currency' => 'eur',
                'automatic_payment_methods' => [
                    'enabled' => true,
                ],
                'metadata' => [
                    'test' => 'direct_no_dependencies'
                ]
            ]);

            error_log("PaymentIntent direct créé: " . $paymentIntent->id);

            return new JsonResponse([
                'success' => true,
                'clientSecret' => $paymentIntent->client_secret,
                'paymentIntentId' => $paymentIntent->id
            ]);

        } catch (\Exception $e) {
            error_log("Erreur Stripe direct: " . $e->getMessage());
            return new JsonResponse([
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
