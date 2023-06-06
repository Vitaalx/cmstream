<?php

namespace Core;

class Token
{
    public static function generateToken(array $payload, string $secretKey, ?int $duration = null): string
    {
        $header = base64_encode(json_encode([
            'alg' => 'SHA256',
            'typ' => 'JWT',
            'expiresIn' => $duration
        ]));

        $payload = base64_encode(json_encode($payload + ["iat" => time()]));
        $signatureInput = $header . '.' . $payload;
        $signature = hash_hmac('sha256', $signatureInput, $secretKey, true);
        $encodedSignature = base64_encode($signature);
        $token = $header . '.' . $payload . '.' . $encodedSignature;

        return $token;
    }

    public static function checkToken(string $token, string $secretKey): array | bool
    {
        list($headerEncoded, $payloadEncoded, $signatureEncoded) = explode('.', $token);

        $header = json_decode(base64_decode($headerEncoded), true);
        $payload = json_decode(base64_decode($payloadEncoded), true);

        $signatureInput = $headerEncoded . '.' . $payloadEncoded;

        $signature = base64_decode($signatureEncoded);

        $calculatedSignature = hash_hmac($header["alg"], $signatureInput, $secretKey, true);

        $signatureMatches = hash_equals($calculatedSignature, $signature);

        if (!$signatureMatches) {
            return false;
        }
        if (isset($payload['iat']) && $header['expiresIn'] !== null) {
            if ($header['expiresIn'] + $payload['iat'] < time()) {
                return false;
            }
        }

        return $payload;
    }

    // static public function passwordHash(string $password): string
    // {
    //     return hash_hmac('sha256', $password, self::$secretKey);
    // }
}