<?php

namespace App\Command;

use App\Entity\User;
use App\Entity\Subscription;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[AsCommand(
    name: 'app:create-premium-user',
    description: 'Crée un utilisateur de test avec un abonnement premium'
)]
class CreatePremiumUserCommand extends Command
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private UserPasswordHasherInterface $passwordHasher
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $io->title('Création d\'un utilisateur premium de test');

        try {
            // Vérifier si l'utilisateur existe déjà
            $existingUser = $this->entityManager->getRepository(User::class)
                ->findOneBy(['email' => 'premium@test.com']);

            if ($existingUser) {
                $io->warning('L\'utilisateur premium@test.com existe déjà.');
                
                // Mettre à jour son abonnement
                $subscription = $existingUser->getSubscription();
                if (!$subscription) {
                    $subscription = new Subscription();
                    $subscription->setUser($existingUser);
                }
                
                $subscription->setPlan('premium');
                $subscription->setStatus('active');
                $subscription->setCurrentPeriodStart(new \DateTime());
                $subscription->setCurrentPeriodEnd(new \DateTime('+1 month'));
                
                $this->entityManager->persist($subscription);
                $this->entityManager->flush();
                
                $io->success('Abonnement premium activé pour l\'utilisateur existant !');
                
                return Command::SUCCESS;
            }

            // Créer un nouvel utilisateur
            $user = new User();
            $user->setEmail('premium@test.com');
            $user->setFirstName('Premium');
            $user->setLastName('User');
            $user->setRoles(['ROLE_USER']);
            $user->setIsActive(true);

            // Hasher le mot de passe
            $hashedPassword = $this->passwordHasher->hashPassword($user, 'premium123');
            $user->setPassword($hashedPassword);

            $this->entityManager->persist($user);

            // Créer l'abonnement premium
            $subscription = new Subscription();
            $subscription->setUser($user);
            $subscription->setPlan('premium');
            $subscription->setStatus('active');
            $subscription->setCurrentPeriodStart(new \DateTime());
            $subscription->setCurrentPeriodEnd(new \DateTime('+1 month'));

            $this->entityManager->persist($subscription);
            $this->entityManager->flush();

            $io->success('Utilisateur premium créé avec succès !');
            
            $io->table(
                ['Propriété', 'Valeur'],
                [
                    ['Email', 'premium@test.com'],
                    ['Mot de passe', 'premium123'],
                    ['Plan', 'Premium'],
                    ['Statut', 'Actif'],
                    ['Valide jusqu\'au', $subscription->getCurrentPeriodEnd()->format('Y-m-d H:i:s')],
                    ['ID Utilisateur', $user->getId()]
                ]
            );

            $io->note([
                'Vous pouvez maintenant vous connecter avec :',
                'Email: premium@test.com',
                'Mot de passe: premium123',
                '',
                'Cet utilisateur a accès aux offres d\'emploi premium.'
            ]);

            return Command::SUCCESS;
            
        } catch (\Exception $e) {
            $io->error('Erreur lors de la création de l\'utilisateur : ' . $e->getMessage());
            return Command::FAILURE;
        }
    }
}
