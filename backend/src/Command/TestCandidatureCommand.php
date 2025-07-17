<?php

namespace App\Command;

use App\Entity\Candidature;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'app:test-candidature',
    description: 'Test creation of candidature'
)]
class TestCandidatureCommand extends Command
{
    public function __construct(
        private EntityManagerInterface $entityManager
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $user = $this->entityManager->find(User::class, 12);
        if (!$user) {
            $output->writeln('Utilisateur 12 non trouvé');
            return Command::FAILURE;
        }

        $candidature = new Candidature();
        $candidature->setTitrePoste('Test Développeur');
        $candidature->setEntreprise('Test Company');
        $candidature->setStatut('a_faire');
        $candidature->setDateDepot(new \DateTime('2025-01-15'));
        $candidature->setNotes('Test de création');
        $candidature->setUser($user);

        try {
            $this->entityManager->persist($candidature);
            $this->entityManager->flush();
            $output->writeln('Candidature créée avec succès, ID: ' . $candidature->getId());
            return Command::SUCCESS;
        } catch (\Exception $e) {
            $output->writeln('Erreur: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }
}
