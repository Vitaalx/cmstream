<?php

namespace checker\page;

use Core\Floor;
use Core\Response;
use Core\Token;

function onlyGuest(?string $token, Floor $floor, Response $response): void
{
    if($token === null) return;
    $payload = Token::checkToken($token, CONFIG["SECRET_KEY"]);
    if ($payload === null) return;
    else $response->redirect("/");
}

function onlyConnected(string $token, Floor $floor, Response $response): void
{
    $payload = Token::checkToken($token, CONFIG["SECRET_KEY"]);
    if ($payload === null) $response->redirect("/");
}