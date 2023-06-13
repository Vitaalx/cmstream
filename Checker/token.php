<?php

namespace checker\token;

use Core\Floor;
use Core\Logger;
use Core\Response;
use Services\token\AccessToken;
use Services\token\EmailToken;
use Services\token\ResetToken;

function checkAccessToken(string $token, Floor $floor, Response $response): array
{
    $payload = AccessToken::verify();
    if ($payload === null) {
        $response->info("token.invalid")->code(401)->send();
    }
    return $payload;
}

function checkEmailToken(string $token, Floor $floor, Response $response): array
{
    $payload = EmailToken::verify($token);
    if ($payload === null) {
        $response->info("token.invalid")->code(401)->send();
    }
    return $payload;
}

function checkResetToken(string $token, Floor $floor, Response $response): array
{
    $payload = ResetToken::verify();
    if ($payload === null) {
        $response->info("token.invalid")->code(401)->send();
    }
    return $payload;
}