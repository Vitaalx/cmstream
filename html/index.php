<?php
# ROUTE CMSTREAM
require __DIR__ . "/../Core/Route.php";
require __DIR__ . "/generate.index.php";
use Core\Route;

Route::match([
    "method" => "*",
    "path" => "/api/.*",
    "controller" => "handlers/notfound",
]);

Route::match([
    "method" => "GET",
    "path" => "/public/.*",
    "controller" => "handlers/assets",
]);

Route::match([
    "method" => "GET",
    "path" => ".*",
    "controller" => "handlers/notfoundIndex",
]);

Route::match([
    "method" => "*",
    "path" => ".*",
    "controller" => "handlers/notfound",
]);
