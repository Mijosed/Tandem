<?php

namespace App\Command;

use App\Entity\User;
use App\Entity\Job;
use App\Entity\Candidature;  
use App\Entity\Notification;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'app:show-data')]
class ShowDataCommand extends Command
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        parent::__construct();
        $this->entityManager = $entityManager;
    }

    protected function configure(): void
    {
        $this->setDescription('Show fixture data loaded in the database');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $userRepository = $this->entityManager->getRepository(User::class);
        $jobRepository = $this->entityManager->getRepository(Job::class);
        $candidatureRepository = $this->entityManager->getRepository(Candidature::class);  // ← Changer
        $notificationRepository = $this->entityManager->getRepository(Notification::class);

        $users = $userRepository->findAll();
        $jobs = $jobRepository->findAll();
        $candidatures = $candidatureRepository->findAll();  
        $notifications = $notificationRepository->findAll();

        $output->writeln('<info>Users:</info>');
        foreach ($users as $user) {
            $output->writeln(sprintf('- %s (%s)', $user->getEmail(), $user->getFirstName() . ' ' . $user->getLastName()));
        }

        $output->writeln('<info>Jobs:</info>');
        foreach ($jobs as $job) {
            $output->writeln(sprintf('- %s at %s', $job->getTitle(), $job->getCompany()));
        }

        $output->writeln('<info>Candidatures:</info>');  
        foreach ($candidatures as $candidature) {  
            $output->writeln(sprintf('- User: %s, Poste: %s chez %s, Statut: %s', 
                $candidature->getUser()->getEmail(), 
                $candidature->getTitrePoste(),  
                $candidature->getEntreprise(), 
                $candidature->getStatut()       
            ));
        }

        $output->writeln('<info>Notifications:</info>');
        foreach ($notifications as $notification) {
            $output->writeln(sprintf('- %s: %s', $notification->getTitle(), $notification->getMessage()));
        }

        return Command::SUCCESS;
    }
}
