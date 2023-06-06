<?php
# INIT ROUTE
require __DIR__ . "/../Core/Route.php";
use Core\Route;

Route::match([
    "method" => "GET",
    "path" => "/",
    "controller" => "API/InitApp/getInit",
]);

Route::match([
    "method" => "POST",
    "path" => "/init",
    "controller" => "API/InitApp/postInit",
]);

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
