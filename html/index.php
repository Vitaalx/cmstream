<?php
# ROUTE CMSTREAM
require __DIR__ . "/generate.methods.php";
require __DIR__ . "/../Core/Route.php";
use Core\Route;

Route::match([
    "method" => "GET",
    "path" => "/public/.*",
    "controller" => "handlers/assets",
]);

require __DIR__ . "/generate.index.php";

Route::match([
    "method" => "*",
    "path" => "/api/.*",
    "controller" => "handlers/notfound",
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
