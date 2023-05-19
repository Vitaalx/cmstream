<?php

namespace Core;

use Entity\User;

class Auth
{
    private int $duration;
    private string $secretKey;

    public function __construct(int $duration = 3600)
    {
        $this->duration = $duration;
        $this->secretKey = 'secretKey';
    }

    public function generateToken(int $id, string $email, string $userName): string
    {
        $clearToken = "http://"
            . $_SERVER["SERVER_NAME"]
            . $_SERVER["REQUEST_URI"]
            . $_SERVER['HTTP_USER_AGENT'];

        $informations = time() . $id . $email . $userName;
        $token = hash_hmac('sha256', $informations . $clearToken, $this->secretKey);

        return $this->setTokenInCookie($token, $informations);
    }

    public function passwordHash(string $password): string
    {
        return hash('sha256', $password);
    }

    public function setTokenInCookie(string $token, string $informations): string
    {
        setcookie('token', $token, time() + $this->duration);
        setcookie('informations', $informations, time() + $this->duration);

        return $token;
    }
}
