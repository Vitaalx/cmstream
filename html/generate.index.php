<?php
use Core\Route;

Route::match([
    "method" => "POST",
    "path" => "/comment",
    "controller" => "API/CommentController/addComment",
]);


Route::match([
    "method" => "GET",
    "path" => "/comments/{id}",
    "controller" => "API/CommentController/getComments",
]);


Route::match([
    "method" => "DELETE",
    "path" => "/comment/{id}",
    "controller" => "API/CommentController/deleteComment",
]);


Route::match([
    "method" => "POST",
    "path" => "/register",
    "controller" => "API/UserController/register",
]);


Route::match([
    "method" => "POST",
    "path" => "/login",
    "controller" => "API/UserController/login",
]);


Route::match([
    "method" => "DELETE",
    "path" => "/user/{id}",
    "controller" => "API/UserController/deleteUser",
]);


Route::match([
    "method" => "PUT",
    "path" => "/user/{id}",
    "controller" => "API/UserController/modifyUser",
]);
