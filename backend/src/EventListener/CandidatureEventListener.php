<?php

namespace App\EventListener;

use App\Entity\Candidature;
use App\Entity\ScheduleEvent;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsEntityListener;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Events;
use Doctrine\Persistence\Event\LifecycleEventArgs;

#[AsEntityListener(event: Events::postPersist, method: 'postPersist', entity: Candidature::class)]
#[AsEntityListener(event: Events::postUpdate, method: 'postUpdate', entity: Candidature::class)]
#[AsEntityListener(event: Events::preRemove, method: 'preRemove', entity: Candidature::class)]
class CandidatureEventListener
{
    public function __construct(
        private EntityManagerInterface $entityManager
    ) {}

    /**
     * Après la création d'une candidature
     */
    public function postPersist(Candidature $candidature, LifecycleEventArgs $event): void
    {
        $this->handleInterviewScheduling($candidature);
    }

    /**
     * Après la mise à jour d'une candidature
     */
    public function postUpdate(Candidature $candidature, LifecycleEventArgs $event): void
    {
        $this->handleInterviewScheduling($candidature);
    }

    /**
     * Avant la suppression d'une candidature
     */
    public function preRemove(Candidature $candidature, LifecycleEventArgs $event): void
    {
        // Supprimer l'événement d'entretien associé s'il existe
        $this->removeInterviewEvent($candidature);
    }

    /**
     * Gère la création/mise à jour automatique des événements d'entretien
     */
    private function handleInterviewScheduling(Candidature $candidature): void
    {
        if ($candidature->getDateEntretien()) {
            // Il y a une date d'entretien, créer ou mettre à jour l'événement
            $this->createOrUpdateInterviewEvent($candidature);
        } else {
            // Plus de date d'entretien, supprimer l'événement s'il existe
            $this->removeInterviewEvent($candidature);
        }
    }

    /**
     * Crée ou met à jour un événement d'entretien
     */
    private function createOrUpdateInterviewEvent(Candidature $candidature): void
    {
        // Vérifier qu'il n'existe pas déjà un événement pour cette candidature
        $existingEvent = $this->entityManager->getRepository(ScheduleEvent::class)
            ->findOneBy(['Candidature' => $candidature, 'type' => 'interview']);

        if ($existingEvent) {
            // Mettre à jour l'événement existant
            $this->updateInterviewEvent($existingEvent, $candidature);
        } else {
            // Créer un nouvel événement
            $this->createInterviewEvent($candidature);
        }
    }

    /**
     * Crée un nouvel événement d'entretien
     */
    private function createInterviewEvent(Candidature $candidature): void
    {
        $event = new ScheduleEvent();
        $event->setTitle(sprintf('Entretien - %s chez %s', $candidature->getTitrePoste(), $candidature->getEntreprise()));
        $event->setDescription(sprintf(
            'Entretien pour le poste de %s chez %s%s',
            $candidature->getTitrePoste(),
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
        
        // Utiliser un flush séparé pour éviter les conflits
        try {
            $this->entityManager->flush();
        } catch (\Exception $e) {
            // Log l'erreur mais ne pas faire planter l'application
            error_log('Erreur lors de la création de l\'événement d\'entretien: ' . $e->getMessage());
        }
    }

    /**
     * Met à jour un événement d'entretien existant
     */
    private function updateInterviewEvent(ScheduleEvent $event, Candidature $candidature): void
    {
        $event->setTitle(sprintf('Entretien - %s chez %s', $candidature->getTitrePoste(), $candidature->getEntreprise()));
        $event->setDescription(sprintf(
            'Entretien pour le poste de %s chez %s%s',
            $candidature->getTitrePoste(),
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

        try {
            $this->entityManager->flush();
        } catch (\Exception $e) {
            error_log('Erreur lors de la mise à jour de l\'événement d\'entretien: ' . $e->getMessage());
        }
    }

    /**
     * Supprime l'événement d'entretien associé à une candidature
     */
    private function removeInterviewEvent(Candidature $candidature): void
    {
        $event = $this->entityManager->getRepository(ScheduleEvent::class)
            ->findOneBy(['Candidature' => $candidature, 'type' => 'interview']);

        if ($event) {
            $this->entityManager->remove($event);
            try {
                $this->entityManager->flush();
            } catch (\Exception $e) {
                error_log('Erreur lors de la suppression de l\'événement d\'entretien: ' . $e->getMessage());
            }
        }
    }
}
