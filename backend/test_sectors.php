<?php
/**
 * Script pour récupérer et afficher tous les secteurs d'activité
 * disponibles dans l'API France Travail
 */

require_once __DIR__ . '/vendor/autoload.php';

use App\Service\PoleEmploiService;
use Symfony\Component\HttpClient\HttpClient;
use Psr\Log\NullLogger;

// Configuration
$clientId = 'VOTRE_CLIENT_ID'; // Remplacez par votre client ID
$clientSecret = 'VOTRE_CLIENT_SECRET'; // Remplacez par votre client secret

// Initialisation du service
$httpClient = HttpClient::create();
$logger = new NullLogger();
$poleEmploiService = new PoleEmploiService($httpClient, $logger, $clientId, $clientSecret);

try {
    echo "Récupération des secteurs d'activité...\n\n";
    
    $sectors = $poleEmploiService->getSectors();
    
    if (empty($sectors)) {
        echo "Aucun secteur trouvé ou erreur de récupération.\n";
        echo "Vérifiez vos identifiants API dans le fichier.\n";
        exit;
    }
    
    echo "Secteurs d'activité disponibles :\n";
    echo "================================\n\n";
    
    foreach ($sectors as $sector) {
        printf("Code: %s - %s\n", 
            $sector['code'] ?? 'N/A', 
            $sector['libelle'] ?? 'N/A'
        );
    }
    
    echo "\n\nTotal : " . count($sectors) . " secteurs trouvés\n";
    
    // Recherche spécifique pour les codes 01 et 02
    echo "\n\nRecherche des codes 01 et 02 :\n";
    echo "==============================\n";
    
    foreach ($sectors as $sector) {
        $code = $sector['code'] ?? '';
        if ($code === '01' || $code === '02') {
            printf("Code: %s - %s\n", $code, $sector['libelle'] ?? 'N/A');
        }
    }
    
} catch (Exception $e) {
    echo "Erreur : " . $e->getMessage() . "\n";
    echo "\nVérifiez :\n";
    echo "1. Que vos identifiants API sont corrects\n";
    echo "2. Que vous avez accès à l'API France Travail\n";
    echo "3. Que votre connexion internet fonctionne\n";
}
