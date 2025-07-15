<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

class ApiTestController extends AbstractController
{
    #[Route('/api', name: 'api_root', methods: ['GET'])]
    public function apiRoot(): JsonResponse
    {
        return $this->json([
            'message' => 'Bienvenue sur l\'API Tandem 🚀',
            'endpoints' => [
                '/api/test',
            ],
        ]);
    }

    #[Route('/api/test', name: 'api_test', methods: ['GET'])]
    public function test(): JsonResponse
    {
        return $this->json([
            'status' => 'success',
            'message' => 'API test route is working 🎉'
        ]);
    }
}
