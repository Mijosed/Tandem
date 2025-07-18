<?php

namespace App\DataFixtures;

use App\Entity\User;
use App\Entity\Candidature;
use App\Entity\Job;
use App\Entity\Notification;
use App\Entity\ScheduleEvent;
use App\Entity\Subscription;
use App\Entity\Payment;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    public function __construct(
        private UserPasswordHasherInterface $passwordHasher
    ) {}

    public function load(ObjectManager $manager): void
    {
        // Créer des utilisateurs
        $users = $this->createUsers($manager);
        
        // Créer des abonnements
        $this->createSubscriptions($manager, $users);
        
        // Créer des offres d'emploi
        $jobs = $this->createJobs($manager);
        
        // Créer des candidatures
        $this->createApplications($manager, $users, $jobs);
        
        // Créer des notifications
        $this->createNotifications($manager, $users);
        
        // Créer des événements de calendrier
        $this->createScheduleEvents($manager, $users);
        
        // Créer des paiements
        $this->createPayments($manager, $users);

        $manager->flush();
    }

    private function createUsers(ObjectManager $manager): array
    {
        $users = [];

        // Admin user
        $admin = new User();
        $admin->setEmail('admin@tandem.com');
        $admin->setFirstName('Admin');
        $admin->setLastName('User');
        $admin->setRoles(['ROLE_ADMIN']);
        $admin->setPassword($this->passwordHasher->hashPassword($admin, 'password'));
        $admin->setIsActive(true);
        $manager->persist($admin);
        $users[] = $admin;

        // Regular users
        $regularUsers = [
            ['email' => 'jean.dupont@example.com', 'firstName' => 'Jean', 'lastName' => 'Dupont'],
            ['email' => 'marie.martin@example.com', 'firstName' => 'Marie', 'lastName' => 'Martin'],
            ['email' => 'pierre.bernard@example.com', 'firstName' => 'Pierre', 'lastName' => 'Bernard'],
            ['email' => 'sophie.dubois@example.com', 'firstName' => 'Sophie', 'lastName' => 'Dubois'],
            ['email' => 'lucas.moreau@example.com', 'firstName' => 'Lucas', 'lastName' => 'Moreau'],
        ];

        foreach ($regularUsers as $userData) {
            $user = new User();
            $user->setEmail($userData['email']);
            $user->setFirstName($userData['firstName']);
            $user->setLastName($userData['lastName']);
            $user->setRoles(['ROLE_USER']);
            $user->setPassword($this->passwordHasher->hashPassword($user, 'password'));
            $user->setIsActive(true);
            $manager->persist($user);
            $users[] = $user;
        }

        return $users;
    }

    private function createSubscriptions(ObjectManager $manager, array $users): void
    {
        foreach ($users as $index => $user) {
            $subscription = new Subscription();
            $subscription->setUser($user);
            
            // Premier utilisateur admin premium, quelques autres premium
            if ($index === 0 || $index === 1 || $index === 3) {
                $subscription->setPlan('premium');
                $subscription->setStatus('active');
                $nextBilling = new \DateTime();
                $nextBilling->add(new \DateInterval('P1M'));
                $subscription->setNextBilling($nextBilling);
            } else {
                $subscription->setPlan('free');
                $subscription->setStatus('active');
            }
            
            $manager->persist($subscription);
        }
    }

    private function createJobs(ObjectManager $manager): array
    {
        $jobs = [];
        
        $jobsData = [
            [
                'title' => 'Développeur Full Stack en Alternance',
                'company' => 'Tech Solutions',
                'location' => 'Paris (75)',
                'type' => 'Alternance',
                'salary' => '1250€/mois',
                'description' => 'Rejoignez notre équipe dynamique en tant que développeur full stack en alternance. Vous travaillerez sur des projets innovants avec React, Node.js et PostgreSQL.',
                'isPremium' => true,
                'source' => 'indeed'
            ],
            [
                'title' => 'Alternance Data Scientist',
                'company' => 'Data Corp',
                'location' => 'Lyon (69)',
                'type' => 'Alternance',
                'salary' => '1400€/mois',
                'description' => 'Opportunité unique en Data Science. Vous utiliserez Python, les frameworks de Machine Learning et travaillerez sur des projets Big Data.',
                'isPremium' => true,
                'source' => 'indeed'
            ],
            [
                'title' => 'Développeur Frontend React',
                'company' => 'Web Agency',
                'location' => 'Marseille (13)',
                'type' => 'Alternance',
                'salary' => '1100€/mois',
                'description' => 'Poste de développeur frontend spécialisé en React. Vous participerez à la création d\'interfaces utilisateur modernes et responsive.',
                'isPremium' => false,
                'source' => 'manual'
            ],
            [
                'title' => 'Alternance DevOps',
                'company' => 'Cloud Systems',
                'location' => 'Toulouse (31)',
                'type' => 'Alternance',
                'salary' => '1300€/mois',
                'description' => 'Alternance DevOps avec focus sur AWS, Docker, Kubernetes. Vous apprendrez les meilleures pratiques de déploiement et CI/CD.',
                'isPremium' => true,
                'source' => 'indeed'
            ],
            [
                'title' => 'Développeur Mobile Flutter',
                'company' => 'Mobile First',
                'location' => 'Nantes (44)',
                'type' => 'Alternance',
                'salary' => '1200€/mois',
                'description' => 'Développement d\'applications mobiles avec Flutter. Vous travaillerez sur des projets cross-platform innovants.',
                'isPremium' => false,
                'source' => 'manual'
            ]
        ];

        foreach ($jobsData as $jobData) {
            $job = new Job();
            $job->setTitle($jobData['title']);
            $job->setCompany($jobData['company']);
            $job->setLocation($jobData['location']);
            $job->setType($jobData['type']);
            $job->setSalary($jobData['salary']);
            $job->setDescription($jobData['description']);
            $job->setIsPremium($jobData['isPremium']);
            $job->setSource($jobData['source']);
            $job->setPostedDate(new \DateTime('-' . rand(1, 30) . ' days'));
            $job->setIsActive(true);
            
            $manager->persist($job);
            $jobs[] = $job;
        }

        return $jobs;
    }

    private function createApplications(ObjectManager $manager, array $users, array $jobs): void
    {
        $statuses = ['pending', 'followed_up', 'interview', 'rejected', 'accepted'];
        
        for ($i = 1; $i < count($users); $i++) {
            $user = $users[$i];
            $numApplications = rand(2, 5);
            
            for ($j = 0; $j < $numApplications; $j++) {
                $candidature = new Candidature();
                $candidature->setUser($user);
                $candidature->setTitrePoste('Développeur ' . ['Frontend', 'Backend', 'Full Stack', 'Mobile'][rand(0, 3)]);
                $candidature->setEntreprise('Entreprise ' . chr(65 + rand(0, 25)));
                $candidature->setDateDepot(new \DateTime('-' . rand(1, 60) . ' days'));
                $candidature->setStatut($statuses[rand(0, 4)]);
                
                if ($candidature->getStatut() === 'interview') {
                    $interviewDate = new \DateTime('+' . rand(1, 14) . ' days');
                    $candidature->setDateEntretien($interviewDate);
                }
                
                if (rand(0, 1)) {
                    $candidature->setNotes('Notes sur cette candidature - ' . $candidature->getEntreprise());
                }
                
                $candidature->setDateCreation(new \DateTime());
                $manager->persist($candidature);
            }
        }
    }

    private function createNotifications(ObjectManager $manager, array $users): void
    {
        $notificationTypes = ['reminder', 'interview', 'info', 'warning', 'success'];
        
        for ($i = 1; $i < count($users); $i++) {
            $user = $users[$i];
            $numNotifications = rand(3, 8);
            
            for ($j = 0; $j < $numNotifications; $j++) {
                $notification = new Notification();
                $notification->setUser($user);
                $notification->setType($notificationTypes[rand(0, 4)]);
                $notification->setIsRead(rand(0, 1) === 1);
                
                switch ($notification->getType()) {
                    case 'reminder':
                        $notification->setTitle('Rappel: Relancer l\'entreprise');
                        $notification->setMessage('N\'oubliez pas de relancer Tech Solutions pour votre candidature.');
                        break;
                    case 'interview':
                        $notification->setTitle('Entretien prévu');
                        $notification->setMessage('Vous avez un entretien demain à 14h avec Data Corp.');
                        break;
                    case 'info':
                        $notification->setTitle('Nouvelle fonctionnalité');
                        $notification->setMessage('Découvrez notre nouveau système de recherche d\'emploi.');
                        break;
                    case 'warning':
                        $notification->setTitle('Candidature à mettre à jour');
                        $notification->setMessage('Votre candidature chez Web Agency n\'a pas été mise à jour depuis 15 jours.');
                        break;
                    case 'success':
                        $notification->setTitle('Candidature acceptée');
                        $notification->setMessage('Félicitations ! Votre candidature a été acceptée.');
                        break;
                }
                
                $manager->persist($notification);
            }
        }
    }

    private function createScheduleEvents(ObjectManager $manager, array $users): void
    {
        $eventTypes = ['interview', 'meeting', 'reminder', 'deadline', 'personal'];
        
        for ($i = 1; $i < count($users); $i++) {
            $user = $users[$i];
            $numEvents = rand(4, 10);
            
            for ($j = 0; $j < $numEvents; $j++) {
                $event = new ScheduleEvent();
                $event->setUser($user);
                $event->setType($eventTypes[rand(0, 4)]);
                
                $startDate = new \DateTime('+' . rand(-30, 30) . ' days');
                $endDate = clone $startDate;
                $endDate->add(new \DateInterval('PT' . rand(1, 4) . 'H'));
                
                $event->setStartDate($startDate);
                $event->setEndDate($endDate);
                
                switch ($event->getType()) {
                    case 'interview':
                        $event->setTitle('Entretien - ' . ['Tech Solutions', 'Data Corp', 'Web Agency'][rand(0, 2)]);
                        $event->setDescription('Entretien technique avec l\'équipe de développement.');
                        $event->setLocation('Visioconférence');
                        $event->setColor('#ef4444');
                        break;
                    case 'meeting':
                        $event->setTitle('Réunion équipe');
                        $event->setDescription('Réunion hebdomadaire avec l\'équipe projet.');
                        $event->setLocation('Salle de réunion A');
                        $event->setColor('#3b82f6');
                        break;
                    case 'reminder':
                        $event->setTitle('Relancer candidature');
                        $event->setDescription('Rappel pour relancer ma candidature.');
                        $event->setColor('#f59e0b');
                        break;
                    case 'deadline':
                        $event->setTitle('Date limite candidature');
                        $event->setDescription('Dernière date pour postuler à cette offre.');
                        $event->setColor('#dc2626');
                        break;
                    case 'personal':
                        $event->setTitle('Événement personnel');
                        $event->setDescription('Événement personnel ou formation.');
                        $event->setColor('#10b981');
                        break;
                }
                
                $manager->persist($event);
            }
        }
    }

    private function createPayments(ObjectManager $manager, array $users): void
    {
        // Créer des paiements pour les utilisateurs premium
        $premiumUsers = [$users[0], $users[1], $users[3]]; // Admin et 2 utilisateurs premium
        
        foreach ($premiumUsers as $user) {
            $numPayments = rand(1, 6);
            
            for ($i = 0; $i < $numPayments; $i++) {
                $payment = new Payment();
                $payment->setUser($user);
                $payment->setAmount(9.99);
                $payment->setCurrency('EUR');
                $payment->setStatus('completed');
                $payment->setDescription('Abonnement Premium');
                
                $paidDate = new \DateTime('-' . ($i + 1) . ' months');
                $payment->setPaidAt($paidDate);
                $payment->setCreatedAt($paidDate);
                $payment->setUpdatedAt($paidDate);
                
                $manager->persist($payment);
            }
        }
    }
}
