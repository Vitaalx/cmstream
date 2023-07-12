<?php

namespace checker\history;

use Core\Floor;
use Core\Response;
use Entity\Episode;
use Entity\History;
use Entity\Movie;

function existByUser(int $user_id, Floor $floor, Response $response): array
{
    $history = History::findMany(["user_id" => $user_id]);
    if ($history === null) $response->info("history.notfound")->code(404)->send();
    return $history;
}

function exist(int $id, Floor $floor, Response $response): History
{
    $history = History::findFirst(["id" => $id]);
    if ($history === null) $response->info("history.notfound")->code(404)->send();
    return $history;
}
function videoExist(array $array, Floor $floor, Response $response): Episode | Movie
{
    $video = null;
    if($array["type"] === "M") $video = Movie::findFirst(["id" => $array["id"]]);
    if($array["type"] === "E") $video = Episode::findFirst(["id" => $array["id"]]);
    if ($video === null) $response->info("history.video.notfound")->code(404)->send();
    return $video;
}

function valueType(string $value_type, Floor $floor, Response $response): string
{
    if(!in_array($value_type, ["E", "M"])) $response->code(400)->info("wrong.value.type")->send();
    return $value_type;
}
