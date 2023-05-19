<?php

namespace Services\Back;

use Core\Auth;
use Entity\User;

class AuthServiceImpl implements AuthService
{
    private Auth $auth;

    public function __construct()
    {
        $this->auth = new Auth();
    }

    public function register(array $data): string
    {
        $token = "";

        try {
            $user = User::insertOne([
                "firstname" => $data["firstname"],
                "lastname" => $data["lastname"],
                "country" => $data["country"],
                "email" => $data["email"],
                "password" => $this->auth->passwordHash($data["password"])
            ]);
            $user->save();

            $token = $this->auth->generateToken(
                $user->getId(),
                $user->getEmail(),
                $user->getFirstname()
            );
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }

        return $token;
    }

    public function login(string $email, string $password): string
    {
        $token = "";

        try {
            $user = User::findFirst([
                "email" => $email,
                "password" => $this->auth->passwordHash($password)
            ]);

            $token = $this->auth->generateToken(
                $user->getId(),
                $user->getEmail(),
                $user->getFirstname()
            );
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }

        return $token;
    }

    public function logout(): void
    {
    }
}
