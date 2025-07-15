<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

class ApiTestController extends AbstractController
{
    #[Route('/api/test', name: 'api_test', methods: ['GET'])]
    public function index(): JsonResponse
    {
        return $this->json([
            'status' => 'success',
            'message' => 'API is working 🎉',
        ]);
    }
}
