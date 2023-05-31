<?php

namespace checker\film;

use Core\Floor;
use Core\Response;
use Entity\Film;

function exist(int $id, Floor $floor, Response $response): Film
{
    $film = Film::findFirst(["id" => $id]);
    if ($film === null) $response->info("film.notfound")->code(404)->send();
    return $film;
}
