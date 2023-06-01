<?php
# INIT ROUTE
require __DIR__ . "/../Core/Route.php";
use Core\Route;

Route::match([
    "method" => "POST",
    "path" => "/init-app",
    "controller" => "API/InitAppController/initApp",
]);


// OTHER

Route::match([
    "method" => "GET",
    "path" => "/public/.*",
    "controller" => "handlers/assets",
]);

Route::match([
    "method" => "GET",
    "path" => ".*",
    "controller" => "handlers/index",
]);

Route::match([
    "method" => "*",
    "path" => ".*",
    "controller" => "handlers/notfound",
]);
