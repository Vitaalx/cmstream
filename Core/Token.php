<?php

namespace Core;

abstract class Token
{
    abstract protected static function duration(): ?int;
    abstract protected static function secretKey(): string;

    protected static function name(): string
    {
        $name = explode("\\", static::class);
        $name = array_pop($name);
        $name = strtolower(preg_replace(["/([a-z\d])([A-Z])/", "/([^_])([A-Z][a-z])/"], "$1_$2", $name));
        
        return $name;
    }

    protected static function path(): string
    {
        return "/";
    }

    protected static function domain(): string
    {
        return "";
    }

    protected static function secure(): bool
    {
        return false;
    }

    protected static function httponly(): bool
    {
        return false;
    }

    public static function generate(array $payload, bool $generateOnly = false): string
    {
        $header = base64_encode(json_encode([
            'alg' => 'SHA256',
            'typ' => 'JWT',
            'expiresIn' => static::duration(),
            'name' => self::name()
        ]));

        $payload = base64_encode(json_encode($payload + ["iat" => time()]));
        $signatureInput = $header . '.' . $payload;
        $signature = hash_hmac('sha256', $signatureInput, static::secretKey(), true);
        $encodedSignature = base64_encode($signature);
        $token = $header . '.' . $payload . '.' . $encodedSignature;
        
        if($generateOnly === false){
            Response::getCurrentResponse()->setCookie(
                self::name(), 
                $token, 
                0,
                static::path(),
                static::domain(),
                static::secure(),
                static::httponly()
            );
        }

        return $token;
    }

    public static function verify(string $token = null): ?array
    {
        $token = $token ?? Request::getCurrentRequest()->getCookie(self::name());
        
        list($headerEncoded, $payloadEncoded, $signatureEncoded) = explode('.', $token);

        $header = json_decode(base64_decode($headerEncoded), true);
        $payload = json_decode(base64_decode($payloadEncoded), true);

        $signatureInput = $headerEncoded . '.' . $payloadEncoded;

        $signature = base64_decode($signatureEncoded);

        if(isset($header["alg"]) === false || $header["name"] !== self::name()) return null;

        $calculatedSignature = hash_hmac($header["alg"], $signatureInput, static::secretKey(), true);

        $signatureMatches = hash_equals($calculatedSignature, $signature);

        if (!$signatureMatches) {
            return null;
        }
        if (isset($payload['iat']) && $header['expiresIn'] !== null) {
            if ($header['expiresIn'] + $payload['iat'] < time()) {
                return null;
            }
        }

        return $payload;
    }

    public static function delete(): bool
    {
        if(Request::getCurrentRequest()->getCookie(self::name()) === null) return false;
        else {
            Response::getCurrentResponse()->setCookie(
                self::name(), 
                null, 
                -1,
                static::path(),
                static::domain()
            );
            return true;
        }
    }
}