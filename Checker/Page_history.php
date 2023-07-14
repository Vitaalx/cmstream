<?php
namespace checker\Page_history;

use Core\Floor;
use Core\Response;
use Entity\Page_history;

function exist(int $id, Floor $floor, Response $response): Page_history
{
    $page_history = Page_history::findFirst(["id" => $id]);
    if($page_history === null) $response->info("page_history.notfound")->code(404)->send();
    return $page_history;
}