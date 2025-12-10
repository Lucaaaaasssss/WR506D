<?php

namespace App\Controller;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/api/admin')]
#[IsGranted('ROLE_ADMIN')]
class AdminController extends AbstractController
{
    #[Route('/users', name: 'admin_list_users', methods: ['GET'])]
    public function listUsers(EntityManagerInterface $entityManager): JsonResponse
    {
        $users = $entityManager->getRepository(User::class)->findAll();
        
        $usersData = array_map(function(User $user) {
            return [
                'id' => $user->getId(),
                'email' => $user->getEmail(),
                'firstname' => $user->getFirstname(),
                'lastname' => $user->getLastname(),
                'roles' => $user->getRoles(),
            ];
        }, $users);

        return new JsonResponse($usersData);
    }

    #[Route('/users/{id}/roles', name: 'admin_update_user_roles', methods: ['PUT'])]
    public function updateUserRoles(
        int $id,
        Request $request,
        EntityManagerInterface $entityManager
    ): JsonResponse
    {
        $user = $entityManager->getRepository(User::class)->find($id);
        
        if (!$user) {
            return new JsonResponse([
                'error' => 'User not found'
            ], Response::HTTP_NOT_FOUND);
        }

        $data = json_decode($request->getContent(), true);
        
        if (!isset($data['roles']) || !is_array($data['roles'])) {
            return new JsonResponse([
                'error' => 'Invalid roles data'
            ], Response::HTTP_BAD_REQUEST);
        }

        // Valider les rôles
        $validRoles = ['ROLE_USER', 'ROLE_EDITOR', 'ROLE_MANAGER', 'ROLE_ADMIN', 'ROLE_SUPER_ADMIN'];
        foreach ($data['roles'] as $role) {
            if (!in_array($role, $validRoles)) {
                return new JsonResponse([
                    'error' => "Invalid role: $role"
                ], Response::HTTP_BAD_REQUEST);
            }
        }

        $user->setRoles($data['roles']);
        $entityManager->flush();

        return new JsonResponse([
            'message' => 'User roles updated successfully',
            'user' => [
                'id' => $user->getId(),
                'email' => $user->getEmail(),
                'roles' => $user->getRoles(),
            ]
        ]);
    }
}
