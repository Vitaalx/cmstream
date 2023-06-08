<?php

namespace checker\Waiting_validate;

use Core\Floor;
use Core\Response;
use Entity\Waiting_validate;

function existById(int $id, Floor $floor, Response $response): Waiting_validate
{
    $waitingUser = Waiting_validate::findFirst(["id" => $id]);

    if($waitingUser === null){
        $response->code(404)->info("waiting_validate.notfound")->send();
    }

    return $waitingUser;
}