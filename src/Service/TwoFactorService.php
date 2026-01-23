<?php

namespace App\Service;

use App\Entity\User;
use Endroid\QrCode\Color\Color;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\ErrorCorrectionLevel;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\RoundBlockSizeMode;
use Endroid\QrCode\Writer\PngWriter;
use OTPHP\TOTP;

class TwoFactorService
{
    private string $appName;

    public function __construct(string $appName = 'WR506D App')
    {
        $this->appName = $appName;
    }

    public function generateSecret(): string
    {
        // Générer un secret compatible avec toutes les apps
        $totp = TOTP::generate();
        return $totp->getSecret();
    }

    public function getProvisioningUri(User $user): string
    {
        $totp = TOTP::createFromSecret($user->getTwoFactorSecret());
        $totp->setLabel($user->getEmail());
        $totp->setIssuer($this->appName);
        $totp->setPeriod(30);
        $totp->setDigits(6);
        $totp->setDigest('sha1');

        return $totp->getProvisioningUri();
    }

    public function getQrCode(User $user): string
    {
        $provisioningUri = $this->getProvisioningUri($user);

        $qrCode = new QrCode(
            data: $provisioningUri,
            encoding: new Encoding('UTF-8'),
            errorCorrectionLevel: ErrorCorrectionLevel::High,
            size: 300,
            margin: 10,
            roundBlockSizeMode: RoundBlockSizeMode::Margin,
            foregroundColor: new Color(0, 0, 0),
            backgroundColor: new Color(255, 255, 255)
        );

        $writer = new PngWriter();
        $result = $writer->write($qrCode);

        return $result->getDataUri();
    }

    public function verifyCode(User $user, string $code): bool
    {
        if (!$user->getTwoFactorSecret()) {
            return false;
        }

        $totp = TOTP::createFromSecret($user->getTwoFactorSecret());
        $totp->setPeriod(30);
        $totp->setDigits(6);
        $totp->setDigest('sha1');

        return $totp->verify($code);
    }

    /**
     * Génère 8 codes de secours aléatoires
     *
     * @return array ['plain' => [...codes en clair...], 'hashed' => [...codes hashés...]]
     */
    public function generateBackupCodes(): array
    {
        $plainCodes = [];
        $hashedCodes = [];

        for ($i = 0; $i < 8; $i++) {
            // Génère un code aléatoire de 8 caractères
            $code = strtoupper(bin2hex(random_bytes(4)));
            $plainCodes[] = $code;

            // Hash le code avec SHA-256
            $hashedCodes[] = hash('sha256', $code);
        }

        return [
            'plain' => $plainCodes,
            'hashed' => $hashedCodes,
        ];
    }

    /**
     * Vérifie si un code de secours est valide
     *
     * @param User $user
     * @param string $code Code de secours en clair
     * @return bool
     */
    public function verifyBackupCode(User $user, string $code): bool
    {
        $hashedCodes = $user->getTwoFactorBackupCodes();

        if (!$hashedCodes) {
            return false;
        }

        $hashedCode = hash('sha256', $code);

        return in_array($hashedCode, $hashedCodes, true);
    }

    /**
     * Supprime un code de secours après utilisation
     *
     * @param User $user
     * @param string $code Code de secours en clair
     * @return bool True si le code a été supprimé, false sinon
     */
    public function removeBackupCode(User $user, string $code): bool
    {
        $hashedCodes = $user->getTwoFactorBackupCodes();

        if (!$hashedCodes) {
            return false;
        }

        $hashedCode = hash('sha256', $code);
        $key = array_search($hashedCode, $hashedCodes, true);

        if ($key !== false) {
            unset($hashedCodes[$key]);
            $user->setTwoFactorBackupCodes(array_values($hashedCodes));
            return true;
        }

        return false;
    }
}
