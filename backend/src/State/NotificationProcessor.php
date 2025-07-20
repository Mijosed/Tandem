<?php

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Entity\Notification;
use App\Entity\User;
use App\Entity\Candidature;
use Doctrine\ORM\EntityManagerInterface;

/**
 * Processor pour gérer les opérations sur les notifications
 */
class NotificationProcessor implements ProcessorInterface
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private ProcessorInterface $persistProcessor
    ) {}

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): mixed
    {
        if (!$data instanceof Notification) {
            return $this->persistProcessor->process($data, $operation, $uriVariables, $context);
        }

        // Seulement traiter les opérations POST et PUT/PATCH (pas GET)
        $operationName = $operation->getName();
        if (!in_array($operationName, ['_api_/notifications_post', '_api_/notifications/{id}_put', '_api_/notifications/{id}_patch'])) {
            return $this->persistProcessor->process($data, $operation, $uriVariables, $context);
        }

        // Résoudre la relation User si elle est fournie comme IRI
        if (isset($context['request_data']['user']) && is_string($context['request_data']['user'])) {
            $userIri = $context['request_data']['user'];
            if (preg_match('/\/api\/users\/(\d+)/', $userIri, $matches)) {
                $userId = (int) $matches[1];
                $user = $this->entityManager->getRepository(User::class)->find($userId);
                if ($user) {
                    $data->setUser($user);
                }
            }
        }

        // Résoudre la relation Candidature si elle est fournie comme IRI
        if (isset($context['request_data']['candidature']) && is_string($context['request_data']['candidature'])) {
            $candidatureIri = $context['request_data']['candidature'];
            if (preg_match('/\/api\/candidatures\/(\d+)/', $candidatureIri, $matches)) {
                $candidatureId = (int) $matches[1];
                $candidature = $this->entityManager->getRepository(Candidature::class)->find($candidatureId);
                if ($candidature) {
                    $data->setCandidature($candidature);
                }
            }
        }

        // Gérer les dates
        if (isset($context['request_data']['scheduledFor']) && is_string($context['request_data']['scheduledFor'])) {
            try {
                $scheduledFor = new \DateTime($context['request_data']['scheduledFor']);
                $data->setScheduledFor($scheduledFor);
            } catch (\Exception $e) {
                // Ignorer les dates invalides
            }
        }

        if (isset($context['request_data']['interviewTime']) && is_string($context['request_data']['interviewTime'])) {
            try {
                $interviewTime = new \DateTime($context['request_data']['interviewTime']);
                $data->setInterviewTime($interviewTime);
            } catch (\Exception $e) {
                // Ignorer les heures invalides
            }
        }

        return $this->persistProcessor->process($data, $operation, $uriVariables, $context);
    }
}
