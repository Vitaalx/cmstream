<?php

namespace checker\user;

use Core\Floor;
use Core\Response;
use Entity\User;

function id(int $id, Floor $floor, Response $response): int
{
    return $id;
}

function name(string $name, Floor $floor, Response $response): string
{
    $name = trim($name);
    $name = strtolower($name);
    if (strlen($name) < 4 || strlen($name) > 20) {
        $response->info("user.name")->code(400)->send();
    }
    return $name;
}

function lastname(string $lastname, Floor $floor, Response $response): string
{
    $lastname = trim($lastname);
    $lastname = strtoupper($lastname);
    if (strlen($lastname) < 4 || strlen($lastname) > 20) {
        $response->info("user.lastname")->code(400)->send();
    }
    return $lastname;
}

function firstname(string $firstname, Floor $floor, Response $response): string
{
    $firstname = trim($firstname);
    $firstname = strtoupper($firstname);
    if (strlen($firstname) < 4 || strlen($firstname) > 20) {
        $response->info("user.firstname")->code(400)->send();
    }
    return $firstname;
}

function email(string $email, Floor $floor, Response $response): string
{
    $email = trim($email);
    $email = strtolower($email);
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $response->info("user.email")->code(400)->send();
    }
    return $email;
}

function emailIsFree(string $email, Floor $floor, Response $response): string
{
    $user = User::findFirst([
        "email" => $email
    ]);
    if ($user) {
        $response->info("user.email")->code(400)->send();
    }
    return $email;
}
