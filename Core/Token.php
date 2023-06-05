<?php

namespace Core;

use Entity\User;
use Core\ConfigFile;

class Token
{
    static private int $duration;
    static private string $secretKey;

    public static function generateToken(array $payload): string
    {
        if (defined("CONFIG")) {
            self::$duration = CONFIG["TOKEN_DURATION"];
            self::$secretKey = CONFIG["SECRET_KEY"];
        } else {
            throw new \Exception("Config.uncreated");
        }

        $header = base64_encode(json_encode([
            'alg' => 'SHA256',
            'typ' => 'JWT',
            'expiresIn' => self::$duration
        ]));

        $payload = base64_encode(json_encode($payload + ["iat" => time()]));
        $signatureInput = $header . '.' . $payload;
        $signature = hash_hmac('sha256', $signatureInput, self::$secretKey, true);
        $encodedSignature = base64_encode($signature);
        $token = $header . '.' . $payload . '.' . $encodedSignature;

        self::setTokenInCookie($token);

        return $token;
    }

    public static function checkToken(string $token): array | bool
    {
        if (defined("CONFIG")) {
            self::$secretKey = CONFIG["SECRET_KEY"];
        } else {
            throw new \Exception("Config.uncreated");
        }

        list($headerEncoded, $payloadEncoded, $signatureEncoded) = explode('.', $token);

        $header = json_decode(base64_decode($headerEncoded), true);
        $payload = json_decode(base64_decode($payloadEncoded), true);

        $signatureInput = $headerEncoded . '.' . $payloadEncoded;

        $signature = base64_decode($signatureEncoded);

        $calculatedSignature = hash_hmac($header["alg"], $signatureInput, self::$secretKey, true);

        $signatureMatches = hash_equals($calculatedSignature, $signature);

        if (!$signatureMatches) {
            return false;
        }
        if (isset($payload['iat'])) {
            if ($header['expiresIn'] + $payload['iat'] < time()) {
                return false;
            }
        }

        return $payload;
    }

    static public function passwordHash(string $password): string
    {
        if (defined("CONFIG")) {
            self::$secretKey = CONFIG["SECRET_KEY"];
        } else {
            throw new \Exception("Config.uncreated");
        }
        return hash_hmac('sha256', $password, self::$secretKey);
    }

    static public function setTokenInCookie(string $token): void
    {
        setcookie("token", $token, time() + self::$duration, "/", "", false, true);
    }

    public static function generateTokenMail(array $payload): string
    {
        if (defined("CONFIG")) {
            self::$secretKey = CONFIG["SECRET_KEY"];
        } else {
            throw new \Exception("Config.uncreated");
        }

        $header = base64_encode(json_encode([
            'alg' => 'SHA256',
            'typ' => 'JWT',
        ]));

        $payload = base64_encode(json_encode($payload));
        $signatureInput = $header . '.' . $payload;
        $signature = hash_hmac('sha256', $signatureInput, self::$secretKey, true);
        $encodedSignature = base64_encode($signature);
        $token = $header . '.' . $payload . '.' . $encodedSignature;

        return $token;
    }
}
