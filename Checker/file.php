<?php
namespace checker\file;

use Core\Floor;
use Core\Response;

function checkPath(string $path, Floor $floor, Response $response){
    if(str_contains("..", $path)){
        $response->code(400)->info("invalidePath")->send();
    }
    
    $floor->droped("path", __DIR__ . "/.." . $path);
}

function exist(string $path, Floor $floor, Response $response){
    if(file_exists($path) === false){
        $response->code(404)->info("fileNotFound")->send();
    }
}