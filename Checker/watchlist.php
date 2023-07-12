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

function exist(int $id, Floor $floor, Response $response): Watchlist
{
    $watchlist = Watchlist::findFirst(["id" => $id]);
    if ($watchlist === null) $response->info("wish.notfound")->code(404)->send();
    return $watchlist;
}

function existByUser(int $user_id, Floor $floor, Response $response): array
{
    $watchlist = Watchlist::findMany(["user_id" => $user_id]);
    if ($watchlist === null) $response->info("watchlist.empty")->code(404)->send();
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
