<?php

namespace checker\video;

use Core\Floor;
use Core\Response;


function url(array $url, Floor $floor, Response $response): array
{
    $url = array_map(function ($url) {
        $url = trim($url);
        if (!filter_var($url, FILTER_VALIDATE_URL)) {
            $this->response->info("video.url")->code(400)->send();
        }
        return $url;
    }, $url);
    return $url;
}

function title(string $title, Floor $floor, Response $response): string
{
    $title = trim($title);
    if (strlen($title) < 4 || strlen($title) > 20) {
        $response->info("video.title")->code(400)->send();
    }
    return $title;
}

function description(string $description, Floor $floor, Response $response): string
{
    $description = trim($description);
    if (strlen($description) < 4 || strlen($description) > 20) {
        $response->info("video.description")->code(400)->send();
    }
    return $description;
}

function image(string $image, Floor $floor, Response $response): string
{
    $image = trim($image);
    if (!filter_var($image, FILTER_VALIDATE_URL)) {
        $response->info("video.image")->code(400)->send();
    }
    return $image;
}
