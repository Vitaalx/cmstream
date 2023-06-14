<?php

namespace checker\page;

use Core\Floor;
use Core\Logger;
use Core\Response;
use Services\token\AccessToken;
use Services\token\ResetToken;

function onlyGuest(string $token, Floor $floor, Response $response): void
{
    $payload = AccessToken::verify();
    if ($payload === null) return;
    else $response->redirect("/");
}

function onlyConnected(string $token, Floor $floor, Response $response): void
{
    $payload = AccessToken::verify();
    if ($payload === null) $response->redirect("/");
}

function onlyResetPasswordToken(string $token, Floor $floor, Response $response): void
{
    $payload = ResetToken::verify($token);
    if ($payload === null) $response->redirect("/");
}