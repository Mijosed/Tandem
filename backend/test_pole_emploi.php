<?php

$clientId = 'PAR_tandem_78fe03a5ed5d79ce26e0b08112055d1dcb1912e578e50dc2516a0e0507c97466';
$clientSecret = '6b18f1b1833ba22c22d34a37d6a22b0dd4647d9d4d333ef570182dddc7e78d85';

$data = http_build_query([
    'grant_type' => 'client_credentials',
    'client_id' => $clientId,
    'client_secret' => $clientSecret,
    'scope' => 'api_offresdemploiv2 o2dsoffre'
]);

$context = stream_context_create([
    'http' => [
        'method' => 'POST',
        'header' => 'Content-Type: application/x-www-form-urlencoded',
        'content' => $data
    ]
]);

echo "Tentative de connexion à l'API Pôle Emploi...\n";

$result = file_get_contents('https://entreprise.pole-emploi.fr/connexion/oauth2/access_token?realm=/partenaire', false, $context);

if ($result === false) {
    echo "Erreur lors de la requête\n";
    $error = error_get_last();
    echo "Erreur: " . $error['message'] . "\n";
} else {
    echo "Réponse reçue:\n";
    echo $result . "\n";
    
    $response = json_decode($result, true);
    if (isset($response['access_token'])) {
        echo "Token obtenu avec succès!\n";
        echo "Token: " . substr($response['access_token'], 0, 20) . "...\n";
    } else {
        echo "Pas de token dans la réponse\n";
        print_r($response);
    }
}
