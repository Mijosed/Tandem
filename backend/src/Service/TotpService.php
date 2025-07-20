<?php

namespace App\Service;

class TotpService
{
    // Table de correspondance Base32
    private const BASE32_CHARS = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';
    
    // Période TOTP (30 secondes)
    private const TIME_PERIOD = 30;
    
    // Longueur du code TOTP
    private const CODE_LENGTH = 6;
    
    // Tolérance pour les codes (fenêtres de temps)
    private const TOLERANCE = 1;

    /**
     * Génère une clé secrète aléatoire en Base32
     */
    public function generateSecret(): string
    {
        $secret = '';
        for ($i = 0; $i < 32; $i++) {
            $secret .= self::BASE32_CHARS[random_int(0, 31)];
        }
        return $secret;
    }

    /**
     * Calcule le code TOTP pour un timestamp donné
     */
    public function calculateCode(string $secret, ?int $timestamp = null): string
    {
        if ($timestamp === null) {
            $timestamp = time();
        }

        // Calculer le compteur de temps (fenêtre de 30 secondes)
        $timeCounter = intval($timestamp / self::TIME_PERIOD);

        // Décoder le secret Base32
        $binarySecret = $this->base32Decode($secret);

        // Convertir le compteur en bytes (big-endian)
        $timeBytes = pack('N*', 0, $timeCounter);

        // Calculer HMAC-SHA1
        $hash = hash_hmac('sha1', $timeBytes, $binarySecret, true);

        // Extract dynamic truncation
        $offset = ord($hash[19]) & 0xf;
        $code = (
            ((ord($hash[$offset]) & 0x7f) << 24) |
            ((ord($hash[$offset + 1]) & 0xff) << 16) |
            ((ord($hash[$offset + 2]) & 0xff) << 8) |
            (ord($hash[$offset + 3]) & 0xff)
        ) % (10 ** self::CODE_LENGTH);

        return str_pad((string)$code, self::CODE_LENGTH, '0', STR_PAD_LEFT);
    }

    /**
     * Vérifie si un code TOTP est valide
     */
    public function verifyCode(string $secret, string $code): bool
    {
        $currentTime = time();
        
        // Vérifier le code actuel et les codes dans la fenêtre de tolérance
        for ($i = -self::TOLERANCE; $i <= self::TOLERANCE; $i++) {
            $timestamp = $currentTime + ($i * self::TIME_PERIOD);
            $expectedCode = $this->calculateCode($secret, $timestamp);
            
            if (hash_equals($expectedCode, $code)) {
                return true;
            }
        }
        
        return false;
    }

    /**
     * Génère l'URL pour Google Authenticator
     */
    public function getQrCodeUrl(string $secret, string $issuer, string $accountName): string
    {
        $label = urlencode($issuer . ':' . $accountName);
        $issuer = urlencode($issuer);
        
        return "otpauth://totp/{$label}?secret={$secret}&issuer={$issuer}&algorithm=SHA1&digits=6&period=30";
    }

    /**
     * Décodage Base32 personnalisé
     */
    private function base32Decode(string $encoded): string
    {
        $encoded = strtoupper($encoded);
        $encoded = rtrim($encoded, '='); // Supprimer le padding
        
        $binaryString = '';
        
        foreach (str_split($encoded) as $char) {
            $value = strpos(self::BASE32_CHARS, $char);
            if ($value === false) {
                throw new \InvalidArgumentException('Invalid Base32 character: ' . $char);
            }
            $binaryString .= str_pad(decbin($value), 5, '0', STR_PAD_LEFT);
        }
        
        // Convertir la chaîne binaire en bytes
        $result = '';
        $chunks = str_split($binaryString, 8);
        
        foreach ($chunks as $chunk) {
            if (strlen($chunk) === 8) {
                $result .= chr(bindec($chunk));
            }
        }
        
        return $result;
    }

    /**
     * Génère un code TOTP pour un moment spécifique (pour les tests)
     */
    public function generateCodeForTime(string $secret, int $timestamp): string
    {
        return $this->calculateCode($secret, $timestamp);
    }

    /**
     * Obtient le temps restant avant le prochain code
     */
    public function getTimeRemaining(): int
    {
        return self::TIME_PERIOD - (time() % self::TIME_PERIOD);
    }
} 