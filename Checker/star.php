<?php

namespace checker\star;

use Core\Floor;
use Core\Response;
use Entity\Star;

function exist(array $star, Floor $floor, Response $response): Star
{
    $star = Star::findFirst([
        "video_id" => $star["video_id"],
        "user_id" => $star["user_id"]
    ]);
    if ($star === null) $response->info("star.notfound")->code(404)->send();
    return $star;
}

function notexist(array $star, Floor $floor, Response $response): void
{
    $star = Star::findFirst([
        "video_id" => $star["video_id"],
        "user_id" => $star["user_id"]
    ]);
    if ($star !== null) $response->info("star.exist")->code(400)->send();
}

function isowner(array $star, Floor $floor, Response $reponse): Star
{
    $star = Star::findFirst([
        "id" => $star["id"],
    ]);
    if ($star === null) $reponse->info("star.notfound")->code(404)->send();
    if ($star->getUser()->getId() !== $floor->pickup("user")->getId()) $reponse->info("star.isowner")->code(403)->send();

    return $star;
}

function existId(int $id, Floor $floor, Response $response): Star
{
    $star = Star::findFirst([
        "id" => $id
    ]);
    if ($star === null) $response->info("star.notfound")->code(404)->send();
    return $star;
}

function note(int $note, Floor $floor, Response $response): int
{
    if ($note < 1 || $note > 5) {
        $response->info("star.note")->code(400)->send();
    }
    return $note;
}
