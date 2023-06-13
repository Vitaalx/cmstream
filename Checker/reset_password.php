<?php

namespace checker\reset_password;

use Core\Floor;
use Core\Response;
use Entity\Reset_Password;
use Entity\User;

function exist(int $id, Floor $floor, Response $response): Reset_Password
{
    $resetPassword = Reset_Password::findFirst(["id" => $id]);

    if($resetPassword === null){
        $response->code(404)->info("Reset_Password.notfound")->send();
    }

    return $resetPassword;
}

function mustNotExist(User $user, Floor $floor, Response $response): void
{
    $resetPassword = Reset_Password::findFirst(["user_id" => $user->getId()]);

    if($resetPassword !== null){
        $response->code(409)->info("Reset_Password.already.use")->send();
    }
}