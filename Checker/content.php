<?php

namespace checker\content;

use Core\Floor;
use Core\Response;
use Entity\Content;

function exist(int $id, Floor $floor, Response $response): Content
{
    $content = Content::findFirst(["id" => $id]);
    if($content === null) $response->info("content.notfound")->code(404)->send();
    return $content;
}