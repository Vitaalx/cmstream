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

    /**
     * @param array $payload
     * @param boolean $generateOnly
     * @return string
     * 
     * Generate a token and set it in the response.
     * If $generateOnly is true, the token will not be set in the response.
     * Paylod is an array of data that will be encoded with header and signature.
     */
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

    /**
     * @param string|null $token
     * @return array|null
     * 
     * Verify a token and return the payload if it is valid.
     * For verify token, this function will check the signature, the algorithm, the name, and the expiration time.
     */
    public static function verify(string $token = null): ?array
    {
        $token = $token ?? Request::getCurrentRequest()->getCookie(self::name());

        if($token === null) return null;
        
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

    /**
     * @return boolean
     * 
     * Delete a token from the response.
     * If the token is not set in the response, this function will return false.
     * If the token is set in the response, this function will crush the last token.
     */
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