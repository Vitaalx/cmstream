<?php

namespace Core;

use Entity\User;
use Core\ConfigFile;

class Auth
{
    static private int $duration;
    static private string $secretKey;

    public function __construct(int $duration = 3600)
    {
        if(defined("CONFIG")) {
            self::$duration = CONFIG["TOKEN_DURATION"];
            self::$secretKey = CONFIG["SECRET_KEY"];
        } else {
            self::$duration = $duration;
            self::$secretKey = "";
        }
    }

    static public function generateToken(string $email, string $userName): string
    {
        $clearToken = "http://"
            . $_SERVER["SERVER_NAME"]
            . $_SERVER["REQUEST_URI"]
            . $_SERVER['HTTP_USER_AGENT'];

        $informations = $email . " " . $userName;
        $token = self::passwordHash($clearToken);
        $token = base64_encode($token . "/" . $informations . "/" . time());

        return self::setTokenInCookie($token, $informations);
    }

    static public function passwordHash(string $password): string
    {
        return hash_hmac('sha256', $password, self::$secretKey);
    }

    static public function setTokenInCookie(string $token, string $informations): string
    {
        setcookie('token', $token, time() + self::$duration);
        setcookie('informations', $informations, time() + self::$duration);

        return $token;
    }

    /**
     * @throws \Exception
     */
    static public function checkToken(string $token): bool
    {
        try {
            $token = explode("/", base64_decode($token));
            if (
                $token[0] === self::passwordHash("http://"
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

            if ($token[2] . self::$duration < time()) {
                throw new \Exception("Token expired");
            }
            return true;
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }
}
