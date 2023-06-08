<?php

namespace checker\page;

use Core\Floor;
use Core\Response;
use Services\token\AccessToken;

function onlyGuest(?string $token, Floor $floor, Response $response): void
{
    if($token === null) return;
    $payload = AccessToken::verify();
    if ($payload === null) return;
    else $response->redirect("/");
}

function onlyConnected(string $token, Floor $floor, Response $response): void
{
    $payload = AccessToken::verify();
    if ($payload === null) $response->redirect("/");
}