<?php
use Core\Route;

require "./Core/Route.php";

Route::match([
    "method" => "GET",
    "path" => "/user/{id}",
    "controller" => "API/user/get",
]);

Route::match([
    "method" => "GET",
    "path" => "/",
    "controller" => "SPA/front/index",
]);

Route::match([
    "method" => "*",
    "path" => ".*",
    "controller" => "handlers/notfound",
]);