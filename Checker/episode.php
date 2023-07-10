<?php

namespace checker\episode;

use Core\Floor;
use Core\Response;
use Entity\Episode;

function notexist(array $content, Floor $floor, Response $response): void
{
    $episode = Episode::findFirst([
        "episode" => $content["episode"],
        "season" => $content["season"],
        "serie_id" => $content["serie_id"]
    ]);
    if ($episode !== null) $response->info("episode.exist")->code(400)->send();
}

function exist(int $id, Floor $floor, Response $response): Episode
{
    $episode = Episode::findFirst([
        "id" => $id
    ]);
    if ($episode === null) $response->info("episode.notexist")->code(400)->send();
    return $episode;
}
