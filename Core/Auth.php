<?php

namespace Core;

use Entity\User;
use Core\ConfigFile;

class Auth
{
    private int $duration;
    private string $secretKey;

    public function __construct(int $duration = 3600)
    {
        $config = new ConfigFile();
        $this->duration = intval($config->getEnv('TOKEN_DURATION'));
        $this->secretKey = $config->getEnv('SECRET_KEY');
        if ($this->secretKey === "") {
            throw new \Exception("Secret key not found");
        }
    }

    public function generateToken(int $id, string $email, string $userName): string
    {
        $clearToken = "http://"
            . $_SERVER["SERVER_NAME"]
            . $_SERVER["REQUEST_URI"]
            . $_SERVER['HTTP_USER_AGENT'];

        $informations = $id . " " . $email . " " . $userName;
        $token = $this->passwordHash($clearToken);
        $token = base64_encode($token . "/" . $informations . "/" . time());

        return $this->setTokenInCookie($token, $informations);
    }

    public function passwordHash(string $password): string
    {
        return hash_hmac('sha256', $password, $this->secretKey);
    }

    public function setTokenInCookie(string $token, string $informations): string
    {
        setcookie('token', $token, time() + $this->duration);
        setcookie('informations', $informations, time() + $this->duration);

        return $token;
    }

    public function checkToken(string $token): bool
    {
        try {
            $token = explode("/", base64_decode($token));
            if (
                $token[0] === $this->passwordHash("http://"
                    . $_SERVER["SERVER_NAME"]
                    . $_SERVER["REQUEST_URI"]
                    . $_SERVER['HTTP_USER_AGENT'])
            ) {
                throw new \Exception("Wrong token");
            }
            $informations = explode(" ", $token[1]);
            $user = User::findFirst([
                "id" => $informations[0],
                "email" => $informations[1],
                "firstname" => $informations[2],
            ]);
            if (!$user) {
                throw new \Exception("User not found");
            }

            if ($token[2] + $this->duration < time()) {
                throw new \Exception("Token expired");
            }
            return true;
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }
}
