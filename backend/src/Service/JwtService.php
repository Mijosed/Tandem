<?php

namespace App\Service;

class JwtService
{
    private $secret;

    public function __construct()
    {
        $this->secret = $_ENV['JWT_SECRET'] ?? 'ma_cle_secrete_super_longue_change_la_en_prod';
    }

    // Génère un JWT HMAC
    public function generate(array $payload, int $expireSeconds = 3600): string
    {
        $header = [
            'alg' => 'HS256',
            'typ' => 'JWT'
        ];
        $payload['exp'] = time() + $expireSeconds;
        $headerEncoded = rtrim(strtr(base64_encode(json_encode($header)), '+/', '-_'), '=');
        $payloadEncoded = rtrim(strtr(base64_encode(json_encode($payload)), '+/', '-_'), '=');
        $signature = hash_hmac('sha256', "$headerEncoded.$payloadEncoded", $this->secret, true);
        $signatureEncoded = rtrim(strtr(base64_encode($signature), '+/', '-_'), '=');
        return "$headerEncoded.$payloadEncoded.$signatureEncoded";
    }

    // Vérifie et décode un JWT HMAC
    public function verify(string $jwt): ?array
    {
        $parts = explode('.', $jwt);
        if (count($parts) !== 3) return null;
        list($header, $payload, $signature) = $parts;
        $expectedSignature = rtrim(strtr(base64_encode(hash_hmac('sha256', "$header.$payload", $this->secret, true)), '+/', '-_'), '=');
        if (!hash_equals($signature, $expectedSignature)) return null;
        $payloadDecoded = json_decode(base64_decode(strtr($payload, '-_', '+/')), true);
        if (isset($payloadDecoded['exp']) && $payloadDecoded['exp'] < time()) return null;
        return $payloadDecoded;
    }
}
