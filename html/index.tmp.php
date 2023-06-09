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
    "path" => "/api/init",
    "controller" => "API/InitApp/postInit",
]);

// TRY

Route::match([
    "method" => "POST",
    "path" => "/api/try/db",
    "controller" => "API/InitApp/tryDB",
]);

Route::match([
    "method" => "POST",
    "path" => "/api/try/app",
    "controller" => "API/InitApp/tryAppConf",
]);

Route::match([
    "method" => "POST",
    "path" => "/api/try/email",
    "controller" => "API/InitApp/tryEmail",
]);

Route::match([
    "method" => "POST",
    "path" => "/api/try/account",
    "controller" => "API/InitApp/tryFirstAccount",
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
