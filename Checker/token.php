<?php

namespace checker\token;

use Core\Floor;
use Core\Response;
use Core\Token;
use Entity\Waiting_validate;

/**
 * @throws \Exception
 */
function check(string $token, Floor $floor, Response $response): array
{
    $payload = Token::checkToken($token);
    if ($payload === null || $payload === false) {
        $response->info("token.invalid")->code(401)->send();
    }
    return $payload;
}

function mail(array $payload, Floor $floor, Response $response): Waiting_validate
{
    $user = Waiting_validate::findFirst(["email" => $payload[0], "firstname" => $payload[1]]);
    if ($user === null) {
        $response->info("user.token")->code(401)->send();
    }
    return $user;
}
