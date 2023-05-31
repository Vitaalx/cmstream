<?php

namespace checker\category;

use Core\Floor;
use Core\Response;
use Entity\Category;

function name(string $name, Floor $floor, Response $response): string
{
    $name = trim($name);
    if (strlen($name) < 4 || strlen($name) > 20) {
        $response->info("category.name")->code(400)->send();
    }
    return $name;
}

function exist(int $id, Floor $floor, Response $response): Category
{
    $category = Category::findFirst(["id" => $id]);
    if ($category === null) $response->info("category.notfound")->code(404)->send();
    return $category;
}

function notexist(string $name, Floor $floor, Response $response): null
{
    $category = Category::findFirst(["title" => $name]);
    if ($category !== null) $response->info("category.exist")->code(400)->send();
    return $category;
}
