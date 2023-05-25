<?php

namespace checker\serie;

use Core\Floor;
use Core\Response;

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
