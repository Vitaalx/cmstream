<?php

use Core\Route;

require "../Core/Route.php";


// USER
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
    "method" => "POST",
    "path" => "/checkToken",
    "controller" => "API/TestTokenController/checkToken",
]);


// FILM
Route::match([
    "method" => "POST",
    "path" => "/createFilm",
    "controller" => "API/ContentManager/FilmController/createFilm",
]);

// CATEGORY
Route::match([
    "method" => "GET",
    "path" => "/createCategory",
    "controller" => "API/ContentManager/CategoryController/createCategory",
]);

Route::match([
    "method" => "GET",
    "path" => "/getCategories",
    "controller" => "API/ContentManager/CategoryController/getCategories",
]);
Route::match([
    "method" => "GET",
    "path" => "/getAllContentWhereCategory",
    "controller" => "API/ContentManager/CategoryController/getAllContentWhereCategory",
]);

// SERIE
Route::match([
    "method" => "POST",
    "path" => "/createSerie",
    "controller" => "API/ContentManager/SerieController/createSerie",
]);

Route::match([
    "method" => "POST",
    "path" => "/addEpisodeWhereSerie",
    "controller" => "API/ContentManager/SerieController/addEpisodeWhereSerie",
]);

Route::match([
    "method" => "GET",
    "path" => "/getEpisodeWhereSerie",
    "controller" => "API/ContentManager/SerieController/getEpisodeWhereSerie",
]);

Route::match([
    "method" => "GET",
    "path" => "/getAllEpisodesWhereSerie",
    "controller" => "API/ContentManager/SerieController/getAllEpisodesWhereSerie",
]);

Route::match([
    "method" => "GET",
    "path" => "/getSeriesInfos",
    "controller" => "API/ContentManager/SerieController/getTitleAndImageWhereAllSeries",
]);

Route::match([
    "method" => "GET",
    "path" => "/deleteSerie",
    "controller" => "API/ContentManager/SerieController/deleteSerie",
]);

Route::match([
    "method" => "GET",
    "path" => "/deleteEpisodeWhereSerie",
    "controller" => "API/ContentManager/SerieController/deleteEpisodeWhereSerie",
]);

Route::match([
    "method" => "POST",
    "path" => "/updateSerie",
    "controller" => "API/ContentManager/SerieController/updateSerieNameAndImage",
]);

// COMMENT

Route::match([
    "method" => "POST",
    "path" => "/addComment",
    "controller" => "API/CommentController/addComment",
]);

Route::match([
    "method" => "GET",
    "path" => "/getComments",
    "controller" => "API/CommentController/getComments",
]);

Route::match([
    "method" => "DELETE",
    "path" => "/deleteComment",
    "controller" => "API/CommentController/deleteComment",
]);

Route::match([
    "method" => "PUT",
    "path" => "/modifyComment",
    "controller" => "API/CommentController/modifyComment",
]);

Route::match([
    "method" => "*",
    "path" => "/public/.*",
    "controller" => "handlers/assets",
]);
