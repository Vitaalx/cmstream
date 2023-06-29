<?php

namespace checker\url;

use Core\Floor;
use Core\Response;
use Entity\Url;

function exist(int $id, Floor $floor, Response $response): Url
{
    $url = Url::findFirst(["id" => $id]);
    if ($url === null) $response->info("url.notfound")->code(404)->send();
    return $url;
}

function notexist(string $url, Floor $floor, Response $response): void
{
    $url = Url::findFirst(["url" => $url]);
    if ($url !== null) $response->info("url.exist")->code(409)->send();
}

function url(string $url, Floor $floor, Response $response): string
{
    $url = trim($url);
    if (!filter_var($url, FILTER_VALIDATE_URL)) {
        $response->info("url.url")->code(400)->send();
    }
    return $url;
}
