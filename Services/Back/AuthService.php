<?php

namespace Services\Back;

use Core\Auth;
use Entity\User;

interface AuthService
{
    public function login(string $email, string $password): string;

    public function logout(): void;

    public function register(array $data): string;
}
