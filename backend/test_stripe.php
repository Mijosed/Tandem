<?php

require_once __DIR__ . '/vendor/autoload.php';

use Stripe\Stripe;
use Stripe\StripeClient;
use Symfony\Component\Dotenv\Dotenv;

// Charger le fichier .env
$dotenv = new Dotenv();
$dotenv->load(__DIR__ . '/.env');

// Charger les variables d'environnement
$secretKey = $_ENV['STRIPE_SECRET_KEY'] ?? '';

if (empty($secretKey)) {
    echo "ERREUR: Clé secrète Stripe manquante\n";
    exit(1);
}

echo "Test de connexion à Stripe...\n";
echo "Clé secrète (masquée): " . substr($secretKey, 0, 20) . "...\n";

try {
    Stripe::setApiKey($secretKey);
    $stripe = new StripeClient($secretKey);
    
    // Test simple : créer un PaymentIntent directement
    echo "Test de création PaymentIntent...\n";
    
    $paymentIntent = $stripe->paymentIntents->create([
        'amount' => 999,
        'currency' => 'eur',
        'automatic_payment_methods' => [
            'enabled' => true,
        ],
        'metadata' => [
            'test' => 'true'
        ]
    ]);
    
    echo "✅ PaymentIntent créé: " . $paymentIntent->id . "\n";
    echo "Client Secret: " . substr($paymentIntent->client_secret, 0, 20) . "...\n";
    echo "✅ Connexion Stripe réussie!\n";
    
} catch (\Exception $e) {
    echo "❌ Erreur Stripe: " . $e->getMessage() . "\n";
    exit(1);
}

echo "\nTest terminé.\n";
