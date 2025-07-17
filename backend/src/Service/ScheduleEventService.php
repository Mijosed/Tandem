<?php

namespace App\Service;

use App\Entity\Candidature;
use App\Entity\ScheduleEvent;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;

class ScheduleEventService
{
    public function __construct(
        private EntityManagerInterface $entityManager
    ) {}

    /**
     * Crée automatiquement un événement de planning pour un entretien
     */
    public function createInterviewEvent(Candidature $candidature): ?ScheduleEvent
    {
        // Vérifier qu'il y a une date d'entretien
        if (!$candidature->getDateEntretien()) {
            return null;
        }

        // Vérifier qu'il n'existe pas déjà un événement pour cette candidature
        $existingEvent = $this->entityManager->getRepository(ScheduleEvent::class)
            ->findOneBy(['Candidature' => $candidature, 'type' => 'interview']);

        if ($existingEvent) {
            // Mettre à jour l'événement existant
            return $this->updateInterviewEvent($existingEvent, $candidature);
        }

        // Créer un nouvel événement
        $event = new ScheduleEvent();
        $event->setTitle(sprintf('Entretien - %s chez %s', $candidature->getPoste(), $candidature->getEntreprise()));
        $event->setDescription(sprintf(
            'Entretien pour le poste de %s chez %s%s',
            $candidature->getPoste(),
            $candidature->getEntreprise(),
            $candidature->getNotes() ? "\n\nNotes: " . $candidature->getNotes() : ''
        ));
        $event->setStartDate($candidature->getDateEntretien());
        
        // Définir une durée par défaut d'1 heure pour l'entretien
        $endDate = clone $candidature->getDateEntretien();
        $endDate->add(new \DateInterval('PT1H'));
        $event->setEndDate($endDate);
        
        $event->setType('interview');
        $event->setUser($candidature->getUser());
        $event->setCandidature($candidature);
        $event->setColor('#10b981'); // Couleur verte pour les entretiens

        $this->entityManager->persist($event);
        $this->entityManager->flush();

        return $event;
    }

    /**
     * Met à jour un événement d'entretien existant
     */
    public function updateInterviewEvent(ScheduleEvent $event, Candidature $candidature): ScheduleEvent
    {
        $event->setTitle(sprintf('Entretien - %s chez %s', $candidature->getPoste(), $candidature->getEntreprise()));
        $event->setDescription(sprintf(
            'Entretien pour le poste de %s chez %s%s',
            $candidature->getPoste(),
            $candidature->getEntreprise(),
            $candidature->getNotes() ? "\n\nNotes: " . $candidature->getNotes() : ''
        ));
        $event->setStartDate($candidature->getDateEntretien());
        
        // Conserver la durée existante ou définir 1 heure par défaut
        if (!$event->getEndDate() || $event->getEndDate() <= $candidature->getDateEntretien()) {
            $endDate = clone $candidature->getDateEntretien();
            $endDate->add(new \DateInterval('PT1H'));
            $event->setEndDate($endDate);
        }

        $this->entityManager->flush();

        return $event;
    }

    /**
     * Supprime l'événement d'entretien associé à une candidature
     */
    public function removeInterviewEvent(Candidature $candidature): void
    {
        $event = $this->entityManager->getRepository(ScheduleEvent::class)
            ->findOneBy(['Candidature' => $candidature, 'type' => 'interview']);

        if ($event) {
            $this->entityManager->remove($event);
            $this->entityManager->flush();
        }
    }

    /**
     * Crée un événement de planning générique
     */
    public function createScheduleEvent(
        User $user,
        string $title,
        \DateTimeInterface $startDate,
        \DateTimeInterface $endDate,
        string $type = 'personal',
        ?string $description = null,
        ?string $location = null,
        bool $allDay = false,
        ?Candidature $candidature = null
    ): ScheduleEvent {
        $event = new ScheduleEvent();
        $event->setTitle($title);
        $event->setDescription($description);
        $event->setStartDate($startDate);
        $event->setEndDate($endDate);
        $event->setType($type);
        $event->setLocation($location);
        $event->setAllDay($allDay);
        $event->setUser($user);
        
        if ($candidature) {
            $event->setCandidature($candidature);
        }

        // Définir la couleur selon le type
        $colors = [
            'interview' => '#10b981',
            'meeting' => '#3b82f6',
            'reminder' => '#f59e0b',
            'deadline' => '#ef4444',
            'personal' => '#8b5cf6'
        ];
        $event->setColor($colors[$type] ?? $colors['personal']);

        $this->entityManager->persist($event);
        $this->entityManager->flush();

        return $event;
    }

    /**
     * Récupère tous les événements d'un utilisateur
     */
    public function getUserEvents(User $user): array
    {
        return $this->entityManager->getRepository(ScheduleEvent::class)
            ->findBy(['user' => $user], ['startDate' => 'ASC']);
    }

    /**
     * Récupère les événements d'aujourd'hui pour un utilisateur
     */
    public function getTodayEvents(User $user): array
    {
        $repository = $this->entityManager->getRepository(ScheduleEvent::class);
        return $repository->findTodayEvents($user->getId());
    }

    /**
     * Récupère les événements à venir pour un utilisateur
     */
    public function getUpcomingEvents(User $user, int $limit = 5): array
    {
        $repository = $this->entityManager->getRepository(ScheduleEvent::class);
        return $repository->findUpcomingEvents($user->getId(), $limit);
    }
}
