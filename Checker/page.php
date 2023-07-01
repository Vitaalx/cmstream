<?php

namespace checker\page;

use Core\Floor;
use Core\Logger;
use Core\Request;
use Core\Response;
use Entity\User;
use Services\Permissions;
use Services\token\AccessToken;
use Services\token\ResetToken;

//is not checker
function goodRedirect(){
    if(Request::getCurrentRequest()->getHeader("Page-Access") === null){
        Response::getCurrentResponse()->code(401)->info("token.invalid")->redirect("/signin");
    }
    else{
        Response::getCurrentResponse()->code(401)->info("token.invalid")->send();
    }
}

function onlyGuest(string $token, Floor $floor, Response $response): void
{
    $payload = AccessToken::verify();
    if ($payload === null) return;
    else $response->code(401)->redirect("/");
}

function onlyConnected(string $token, Floor $floor, Response $response): User
{
    $payload = AccessToken::verify();
    if ($payload === null) goodRedirect();
    $user = User::findFirst(["id" => $payload["id"]]);
    if($user === null) goodRedirect();


    return $user;
}

function onlyResetPasswordToken(string $token, Floor $floor, Response $response): void
{
    $payload = ResetToken::verify($token);
    if ($payload === null) $response->code(401)->redirect("/");
}

function mustHavePermission(Permissions $permissions, Floor $floor, Response $response){
    /** @var User $user */
    $user = $floor->pickup("user");
    if($user->hasPermission($permissions) === false) $response->code(401)->redirect("/");
}