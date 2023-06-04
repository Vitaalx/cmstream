<?php

namespace checker\token;

use Core\Floor;
use Core\Response;
use Core\Auth;
use Entity\Waiting_validate;

/**
 * @throws \Exception
 */
function check(string $token, Floor $floor, Response $response): string
{
    if (!Auth::checkToken($token)) {
        $response->info("user.token")->code(401)->send();
    }
    return $token;
}

function mail(string $token, Floor $floor, Response $response): Waiting_validate
{
    $user = Waiting_validate::findFirst(["token" => $token]);
    if ($user === null) {
        $response->info("user.token")->code(401)->send();
    }
    return $user;
}
