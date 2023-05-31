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
    "path" => "/api/content-manager/film/create",
    "controller" => "API/ContentManager/FilmController/createFilm",
]);

Route::match([
    "method" => "DELETE",
    "path" => "/api/content-manager/film/delete",
    "controller" => "API/ContentManager/FilmController/deleteFilm",
]);

Route::match([
    "method" => "GET",
    "path" => "/api/content-manager/film/get",
    "controller" => "API/ContentManager/FilmController/getFilm",
]);

Route::match([
    "method" => "GET",
    "path" => "/api/content-manager/film/get-all",
    "controller" => "API/ContentManager/FilmController/getAllFilms",
]);

Route::match([
    "method" => "GET",
    "path" => "/api/content-manager/film/get-where-film",
    "controller" => "API/ContentManager/FilmController/getVideoWhereFilm",
]);

Route::match([
    "method" => "PUT",
    "path" => "/api/content-manager/film/update",
    "controller" => "API/ContentManager/FilmController/updateFilm",
]);

// CATEGORY
Route::match([
    "method" => "POST",
    "path" => "/api/content-manager/category/create",
    "controller" => "API/ContentManager/CategoryController/createCategory",
]);

Route::match([
    "method" => "DELETE",
    "path" => "/api/content-manager/category/delete",
    "controller" => "API/ContentManager/CategoryController/deleteCategory",
]);

Route::match([
    "method" => "GET",
    "path" => "/api/content-manager/category/get-all",
    "controller" => "API/ContentManager/CategoryController/getAllCategories",
]);

Route::match([
    "method" => "GET",
    "path" => "/api/content-manager/category/get-all-content",
    "controller" => "API/ContentManager/CategoryController/getAllContentWhereCategory",
]);

Route::match([
    "method" => "PUT",
    "path" => "/api/content-manager/category/update",
    "controller" => "API/ContentManager/CategoryController/updateCategory",
]);

// SERIE
Route::match([
    "method" => "POST",
    "path" => "/api/content-manager/serie/create",
    "controller" => "API/ContentManager/SerieController/createSerie",
]);

Route::match([
    "method" => "DELETE",
    "path" => "/api/content-manager/serie/delete",
    "controller" => "API/ContentManager/SerieController/deleteSerie",
]);

Route::match([
    "method" => "GET",
    "path" => "/api/content-manager/serie/get-all",
    "controller" => "API/ContentManager/SerieController/getTitleAndImageWhereAllSeries",
]);

Route::match([
    "method" => "PUT",
    "path" => "/api/content-manager/serie/update",
    "controller" => "API/ContentManager/SerieController/updateSerieNameAndImage",
]);

Route::match([
    "method" => "POST",
    "path" => "/api/content-manager/serie/add-episode",
    "controller" => "API/ContentManager/SerieController/addEpisodeWhereSerie",
]);

Route::match([
    "method" => "GET",
    "path" => "/api/content-manager/serie/get-episode",
    "controller" => "API/ContentManager/SerieController/getEpisodeWhereSerie",
]);

Route::match([
    "method" => "GET",
    "path" => "/api/content-manager/serie/get-all-episodes",
    "controller" => "API/ContentManager/SerieController/getAllEpisodesWhereSerie",
]);

Route::match([
    "method" => "DELETE",
    "path" => "/api/content-manager/serie/delete-episode",
    "controller" => "API/ContentManager/SerieController/deleteEpisodeWhereSerie",
]);

Route::match([
    "method" => "PUT",
    "path" => "/api/content-manager/serie/update-episode",
    "controller" => "API/ContentManager/SerieController/updateEpisodeInfo",
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

// STAR

Route::match([
    "method" => "POST",
    "path" => "/api/stars/create",
    "controller" => "API/StarsController/createStar",
]);

Route::match([
    "method" => "GET",
    "path" => "/api/stars/average",
    "controller" => "API/StarsController/starAverage",
]);

Route::match([
    "method" => "GET",
    "path" => "/api/stars/get",
    "controller" => "API/StarsController/getStar",
]);

Route::match([
    "method" => "GET",
    "path" => "/api/stars/get-all-average",
    "controller" => "API/StarsController/getAllAverageStarsWhereVideos",
]);

Route::match([
    "method" => "PUT",
    "path" => "/api/stars/update",
    "controller" => "API/StarsController/updateStar",
]);

Route::match([
    "method" => "DELETE",
    "path" => "/api/stars/delete",
    "controller" => "API/StarsController/deleteStar",
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