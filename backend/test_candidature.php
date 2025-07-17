<?php

require_once __DIR__ . '/vendor/autoload.php';

use Doctrine\ORM\Tools\Setup;
use Doctrine\ORM\EntityManager;

$paths = [__DIR__ . '/src/Entity'];
$isDevMode = true;

$dbParams = [
    'driver'   => 'pdo_pgsql',
    'host'     => 'database',
    'port'     => 5432,
    'user'     => 'admin',
    'password' => 'admin',
    'dbname'   => 'tandem',
];

$config = Setup::createAnnotationMetadataConfiguration($paths, $isDevMode);
$entityManager = EntityManager::create($dbParams, $config);

// Test de création d'une candidature
$user = $entityManager->find(\App\Entity\User::class, 12);
if (!$user) {
    echo "Utilisateur 12 non trouvé\n";
    exit;
}

$candidature = new \App\Entity\Candidature();
$candidature->setTitrePoste('Test Développeur');
$candidature->setEntreprise('Test Company');
$candidature->setStatut('a_faire');
$candidature->setDateDepot(new \DateTime('2025-01-15'));
$candidature->setNotes('Test de création');
$candidature->setUser($user);

try {
    $entityManager->persist($candidature);
    $entityManager->flush();
    echo "Candidature créée avec succès, ID: " . $candidature->getId() . "\n";
} catch (\Exception $e) {
    echo "Erreur: " . $e->getMessage() . "\n";
}
