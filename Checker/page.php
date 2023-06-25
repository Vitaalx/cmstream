<?php

namespace checker\page;

use Core\Floor;
use Core\Logger;
use Core\Response;
use Entity\User;
use Services\Permissions;
use Services\token\AccessToken;
use Services\token\ResetToken;

function onlyGuest(string $token, Floor $floor, Response $response): void
{
    $payload = AccessToken::verify();
    if ($payload === null) return;
    else $response->redirect("/");
}

function onlyConnected(string $token, Floor $floor, Response $response): User
{
    $payload = AccessToken::verify();
    if ($payload === null) $response->redirect("/");
    $user = User::findFirst(["id" => $payload["id"]]);
    if($user === null) $response->redirect("/");


    return $user;
}

function onlyResetPasswordToken(string $token, Floor $floor, Response $response): void
{
    $payload = ResetToken::verify($token);
    if ($payload === null) $response->redirect("/");
}

function mustHavePermission(Permissions $permissions, Floor $floor, Response $response){
    /** @var User $user */
    $user = $floor->pickup("user");
    if($user->hasPermission($permissions) === false) $response->redirect("/");
}