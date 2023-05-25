<?php

namespace checker\category;

use Core\Floor;
use Core\Response;

function name(string $name, Floor $floor, Response $response): string
{
    $name = trim($name);
    if (strlen($name) < 4 || strlen($name) > 20) {
        $response->info("category.name")->code(400)->send();
    }
    return $name;
}
