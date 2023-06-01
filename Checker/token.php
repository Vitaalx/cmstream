<?php

namespace checker\token;

use Core\Floor;
use Core\Response;
use Core\Auth;

/**
 * @throws \Exception
 */
function check(string $token, Floor $floor, Response $response): string
{
    $auth = new Auth();
    if (!$auth::checkToken($token)) {
        $response->info("user.token")->code(401)->send();
    }
    return $token;
}
