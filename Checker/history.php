<?php

namespace checker\history;

use Core\Floor;
use Core\Response;
use Entity\History;

function exist(int $user_id, Floor $floor, Response $response): array
{
    $history = History::findMany(["user_id" => $user_id]);
    if ($history === null) $response->info("history.notfound")->code(404)->send();
    return $history;
}
