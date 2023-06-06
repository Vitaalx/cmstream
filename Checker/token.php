<?php

namespace checker\token;

use Core\Floor;
use Core\Response;
use Core\Token;

/**
 * @throws \Exception
 */
function check(string $token, Floor $floor, Response $response): array
{
    $payload = Token::checkToken($token, CONFIG["SECRET_KEY"]);
    if ($payload === null) {
        $response->info("token.invalid")->code(401)->send();
    }
    return $payload;
}
