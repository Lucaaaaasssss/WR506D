<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

final class UserController extends AbstractController
{
    #[Route('/api/profile', name: 'app_user_profile', methods: ['GET'])]
    #[IsGranted('ROLE_USER')]
    public function profile(): JsonResponse
    {
        $user = $this->getUser();

        return new JsonResponse([
            'message' => 'Vous êtes authentifié !',
            'user' => [
                'email' => $user->getUserIdentifier(),
                'roles' => $user->getRoles(),
            ]
        ]);
    }

    #[Route('/api/admin', name: 'app_admin', methods: ['GET'])]
    #[IsGranted('ROLE_ADMIN')]
    public function admin(): JsonResponse
    {
        return new JsonResponse([
            'message' => 'Vous êtes admin !',
            'user' => [
                'email' => $this->getUser()->getUserIdentifier(),
            ]
        ]);
    }
}
