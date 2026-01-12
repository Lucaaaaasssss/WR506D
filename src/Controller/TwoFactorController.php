<?php

namespace App\Controller;

use App\Entity\User;
use App\Service\TwoFactorService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/api/2fa')]
final class TwoFactorController extends AbstractController
{
    public function __construct(
        private readonly TwoFactorService $twoFactorService,
        private readonly EntityManagerInterface $entityManager
    ) {
    }

    #[Route('/setup', name: 'app_2fa_setup', methods: ['POST'])]
    #[IsGranted('ROLE_USER')]
    public function setup(): JsonResponse
    {
        $user = $this->getUser();

        if (!$user instanceof User) {
            return $this->json(['error' => 'User not found'], 401);
        }

        $secret = $this->twoFactorService->generateSecret();
        $user->setTwoFactorSecret($secret);
        // ON FORCE A FALSE !
        $user->setTwoFactorEnabled(false);

        $this->entityManager->flush();

        // QR code
        $qrCodeDataUri = $this->twoFactorService->getQrCode($user);
        // URL de provision générée par le module OTP
        $provisioningUri = $this->twoFactorService->getProvisioningUri($user);

        return $this->json([
            'secret' => $secret,
            'qr_code' => $qrCodeDataUri,
            'provisioning_uri' => $provisioningUri,
            'message' => 'Scannez ce QR code avec Google Authenticator pour activer le 2FA',
        ]);
    }

    #[Route('/enable', name: 'app_2fa_enable', methods: ['POST'])]
    #[IsGranted('ROLE_USER')]
    public function enable(Request $request): JsonResponse
    {
        $user = $this->getUser();

        if (!$user instanceof User) {
            return $this->json(['error' => 'User not found'], Response::HTTP_UNAUTHORIZED);
        }

        // Vérifier que l'utilisateur a bien un secret (a fait le setup)
        if (!$user->getTwoFactorSecret()) {
            return $this->json([
                'error' => 'Vous devez d\'abord configurer le 2FA via /api/2fa/setup'
            ], Response::HTTP_BAD_REQUEST);
        }

        // Récupérer le code depuis le body
        $data = json_decode($request->getContent(), true);
        $code = $data['code'] ?? '';

        // Validation du code fourni
        if (empty($code)) {
            return $this->json([
                'error' => 'Le code est requis'
            ], Response::HTTP_BAD_REQUEST);
        }

        // Vérifier que le code est au bon format (6 chiffres)
        if (!preg_match('/^\d{6}$/', $code)) {
            return $this->json([
                'error' => 'Le code doit être composé de 6 chiffres'
            ], Response::HTTP_BAD_REQUEST);
        }

        // Vérification du code TOTP
        if (!$this->twoFactorService->verifyCode($user, $code)) {
            return $this->json([
                'error' => 'Code invalide. Veuillez vérifier le code dans votre application d\'authentification.'
            ], Response::HTTP_UNAUTHORIZED);
        }

        // Génération des codes de secours
        $backupCodes = $this->twoFactorService->generateBackupCodes();

        // Activer le 2FA
        $user->setTwoFactorEnabled(true);

        // Stocker les codes de secours hashés
        $user->setTwoFactorBackupCodes($backupCodes['hashed']);

        $this->entityManager->flush();

        return $this->json([
            'message' => '2FA activé avec succès !',
            'backup_codes' => $backupCodes['plain'],
            'warning' => 'Sauvegardez ces codes de secours dans un endroit sûr. Ils ne seront plus affichés.',
        ]);
    }

    #[Route('/disable', name: 'app_2fa_disable', methods: ['POST'])]
    #[IsGranted('ROLE_USER')]
    public function disable(Request $request): JsonResponse
    {
        $user = $this->getUser();

        if (!$user instanceof User) {
            return $this->json(['error' => 'User not found'], Response::HTTP_UNAUTHORIZED);
        }

        // Vérifier que le 2FA est activé
        if (!$user->isTwoFactorEnabled()) {
            return $this->json([
                'error' => 'Le 2FA n\'est pas activé'
            ], Response::HTTP_BAD_REQUEST);
        }

        // Récupérer le code depuis le body pour confirmer la désactivation
        $data = json_decode($request->getContent(), true);
        $code = $data['code'] ?? '';

        if (empty($code)) {
            return $this->json([
                'error' => 'Un code de vérification est requis pour désactiver le 2FA'
            ], Response::HTTP_BAD_REQUEST);
        }

        // Vérifier le code TOTP ou code de secours
        $isValidTotp = $this->twoFactorService->verifyCode($user, $code);
        $isValidBackup = $this->twoFactorService->verifyBackupCode($user, $code);

        if (!$isValidTotp && !$isValidBackup) {
            return $this->json([
                'error' => 'Code invalide'
            ], Response::HTTP_UNAUTHORIZED);
        }

        // Désactiver le 2FA
        $user->setTwoFactorEnabled(false);
        $user->setTwoFactorSecret(null);
        $user->setTwoFactorBackupCodes(null);

        $this->entityManager->flush();

        return $this->json([
            'message' => '2FA désactivé avec succès'
        ]);
    }

    #[Route('/verify', name: 'app_2fa_verify', methods: ['POST'])]
    public function verify(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $email = $data['email'] ?? '';
        $code = $data['code'] ?? '';

        if (empty($email) || empty($code)) {
            return $this->json([
                'error' => 'Email et code requis'
            ], Response::HTTP_BAD_REQUEST);
        }

        // Récupérer l'utilisateur
        $user = $this->entityManager->getRepository(User::class)->findOneBy(['email' => $email]);

        if (!$user || !$user->isTwoFactorEnabled()) {
            return $this->json([
                'error' => 'Utilisateur non trouvé ou 2FA non activé'
            ], Response::HTTP_UNAUTHORIZED);
        }

        // Vérifier le code TOTP
        $isValidTotp = $this->twoFactorService->verifyCode($user, $code);

        // Vérifier le code de secours
        $isValidBackup = false;
        if (!$isValidTotp) {
            $isValidBackup = $this->twoFactorService->verifyBackupCode($user, $code);

            // Si c'est un code de secours valide, le supprimer
            if ($isValidBackup) {
                $this->twoFactorService->removeBackupCode($user, $code);
                $this->entityManager->flush();
            }
        }

        if (!$isValidTotp && !$isValidBackup) {
            return $this->json([
                'error' => 'Code invalide'
            ], Response::HTTP_UNAUTHORIZED);
        }

        return $this->json([
            'message' => 'Code valide',
            'is_backup_code' => $isValidBackup,
            'remaining_backup_codes' => $isValidBackup ? count($user->getTwoFactorBackupCodes() ?? []) : null,
        ]);
    }

    #[Route('/status', name: 'app_2fa_status', methods: ['GET'])]
    #[IsGranted('ROLE_USER')]
    public function status(): JsonResponse
    {
        $user = $this->getUser();

        if (!$user instanceof User) {
            return $this->json(['error' => 'User not found'], Response::HTTP_UNAUTHORIZED);
        }

        return $this->json([
            'enabled' => $user->isTwoFactorEnabled(),
            'backup_codes_count' => $user->isTwoFactorEnabled() ? count($user->getTwoFactorBackupCodes() ?? []) : 0,
        ]);
    }
}
