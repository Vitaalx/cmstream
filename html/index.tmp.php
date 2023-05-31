<?php

use Core\Route;

require "../Core/Route.php";

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
    "method" => "*",
    "path" => ".*",
    "controller" => "handlers/notfound",
]);
