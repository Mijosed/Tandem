<?php

namespace App\EventListener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpFoundation\JsonResponse;

class JwtMiddlewareSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::REQUEST => 'onKernelRequest',
        ];
    }

    public function onKernelRequest(RequestEvent $event): void
    {
        $request = $event->getRequest();

        // Ne pas filtrer les routes d'authentification
        if (str_starts_with($request->getPathInfo(), '/api/auth')) {
            return;
        }

        $authHeader = $request->headers->get('Authorization');
        if (!$authHeader || !preg_match('/^Bearer\s.+$/', $authHeader)) {
            $event->setResponse(new JsonResponse(['error' => 'Token manquant ou invalide'], 401));
            return;
        }

        // Vérification du token JWT via le service
        $jwt = substr($authHeader, 7); // Retire 'Bearer '
        $jwtService = new \App\Service\JwtService();
        $payload = $jwtService->verify($jwt);
        if (!$payload) {
            $event->setResponse(new JsonResponse(['error' => 'Signature JWT invalide ou token expiré'], 401));
            return;
        }
    }
}
