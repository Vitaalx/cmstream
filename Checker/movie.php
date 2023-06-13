<?php

namespace checker\movie;

use Core\Floor;
use Core\Response;
use Entity\Movie;

function exist(int $id, Floor $floor, Response $response): Movie
{
    $movie = Movie::findFirst(["id" => $id]);
    if ($movie === null) $response->info("movie.notfound")->code(404)->send();
    return $movie;
}
