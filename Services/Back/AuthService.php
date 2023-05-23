<?php

namespace Services\Back;

use Core\Auth;
use Entity\User;

class AuthService
{
    private Auth $auth;

    public function __construct()
    {
        $this->auth = new Auth();
    }

    /**
     * this method is use for register user
     * @param string $firstname
     * @param string $lastname
     * @param string $email
     * @param string $password hash method sha256
     * 
     * @return string token
     */
    public function register(string $firstname, string $lastname, string $email, string $password): string
    {
        try {
            $userInserted = User::insertOne([
                "firstname" => $firstname,
                "lastname" => $lastname,
                "email" => $email,
                "password" => $this->auth->passwordHash($password),
                "role" => "client",
                "created_at" => date("Y-m-d H:i:s"),
                "updated_at" => date("Y-m-d H:i:s")
            ]);
            $userInserted->save();

            return $this->auth->generateToken(
                $userInserted->getId(),
                $userInserted->getEmail(),
                $userInserted->getFirstname()
            );
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }

    public function login(string $email, string $password): string
    {
        try {
            $user = User::findFirst([
                "email" => $email,
            ]);

            if (!$user) {
                throw new \Exception("User not found");
            }
            if ($user->getPassword() !== $this->auth->passwordHash($password)) {
                throw new \Exception("Wrong password");
            }

            return $this->auth->generateToken(
                $user->getId(),
                $user->getEmail(),
                $user->getFirstname()
            );
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }

    // tmp for test feature token
    // checker "token" is fonctionnal, it's ready to use (checker path: [token/check])
    public function checkToken(string $token): bool
    {
        try {
            if (!$token) {
                throw new \Exception("No token");
            }
            if (!$this->auth->checkToken($token)) {
                throw new \Exception("Wrong token");
            }
            return true;
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }
}
