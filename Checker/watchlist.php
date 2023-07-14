<?php

namespace checker\watchlist;

use Core\Floor;
use Core\Response;

use Entity\Watchlist;

function notexist(array $array, Floor $floor, Response $response): void
{
    $watchlist = Watchlist::findFirst([
        "content_id" => $array['content_id'],
        "user_id" => $array['user_id']
    ]);
    if ($watchlist !== null) $response->info("watchlist.already.exist")->code(400)->send();
}

function state(array $array, Floor $floor, Response $response): int
{
    $watchlist = Watchlist::findFirst([
        "content_id" => $array['content_id'],
        "user_id" => $array['user_id']
    ]);
    if($watchlist === null) return 1;
    else return 0;
}

function exist(int $id, Floor $floor, Response $response): Watchlist
{
    $watchlist = Watchlist::findFirst(["id" => $id]);
    if ($watchlist === null) $response->info("wish.notfound")->code(404)->send();
    return $watchlist;
}

function isOwner(array $list, Floor $floor, Response $response): Watchlist
{
    $watchlist = Watchlist::findFirst([
        "content_id" => $list['content_id'],
        "user_id" => $list['user_id'],
    ]);
    if ($watchlist === null) $response->info("watchlist.notfound")->code(404)->send();
    return $watchlist;
}
