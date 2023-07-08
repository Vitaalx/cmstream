<?php

namespace checker\serie;

use Core\Floor;
use Core\Response;
use Entity\Serie;

function episode(int $episode, Floor $floor, Response $response): int
{
    if ($episode < 1) {
        $response->info("serie.episode")->code(400)->send();
    }
    return $episode;
}

function season(int $season, Floor $floor, Response $response): int
{
    if ($season < 1) {
        $response->info("serie.season")->code(400)->send();
    }
    return $season;
}

function title(string $title, Floor $floor, Response $response): string
{
    $title = trim($title);
    if (strlen($title) < 4 || strlen($title) > 20) {
        $response->info("video.title")->code(400)->send();
    }
    return $title;
}

function exist(int $id, Floor $floor, Response $response): Serie
{
    $serie = Serie::findFirst(["id" => $id]);
    if ($serie === null) $response->info("serie.notfound")->code(404)->send();
    return $serie;
}

function notexist(array $array, Floor $floor, Response $response): void
{
    $serie = Serie::findFirst(["title" => $array["title"]]);
    if ($serie !== null && $serie->getId() !== intval($array["serie_id"])) $response->info("serie.exist")->code(400)->send();
}
