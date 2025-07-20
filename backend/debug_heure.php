<?php

require_once 'vendor/autoload.php';

// Simuler une requête pour tester la sérialisation
$testData = [
    'titrePoste' => 'Test Direct PHP',
    'entreprise' => 'Debug Corp',
    'statut' => 'a_faire',
    'dateDepot' => '2025-07-20T00:00:00Z',
    'dateEntretien' => '2025-07-21T00:00:00Z',
    'heureEntretien' => '14:30',
    'notes' => 'Test debug heure',
    'user' => '/api/users/12'
];

echo "=== TEST HEURE ENTRETIEN ===\n";
echo "Données d'entrée :\n";
var_dump($testData);
echo "\nheureEntretien: " . $testData['heureEntretien'] . " (length: " . strlen($testData['heureEntretien']) . ")\n";

// Test de validation longueur
if (strlen($testData['heureEntretien']) > 8) {
    echo "⚠️ ATTENTION: heureEntretien dépasse la limite de 8 caractères!\n";
} else {
    echo "✅ heureEntretien respecte la limite de 8 caractères\n";
}
