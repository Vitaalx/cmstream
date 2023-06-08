<?php

namespace checker\watchlist;

use Core\Floor;
use Core\Response;

use Entity\Watchlist;

function notexist(array $content, Floor $floor, Response $response): void
{
    $watchlist = Watchlist::findFirst([
        "film_id" => $content['film'],
        "serie_id" => $content['serie'],
        "user_id" => $content['user']
    ]);
    if ($watchlist !== null) $response->info("watchlist.exist")->code(400)->send();
}

function exist(int $id, Floor $floor, Response $response): Watchlist
{
    $watchlist = Watchlist::findFirst(["id" => $id]);
    if ($watchlist === null) $response->info("wish.notfound")->code(404)->send();
    return $watchlist;
}

function isowner(array $list, Floor $floor, Response $response): Watchlist
{
    $watchlist = Watchlist::findFirst([
        "id" => $list['watchlist_id'],
        "user_id" => $list['user_id'],
    ]);
    if ($watchlist === null) $response->info("watchlist.notfound")->code(404)->send();
    return $watchlist;
}
