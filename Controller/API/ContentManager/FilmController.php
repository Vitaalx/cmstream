<?php

namespace controller\API\ContentManager\FilmController;

use Core\Controller;
use Core\Request;
use Core\Response;

use Entity\Film;
use Services\Back\VideoManagerService as VideoManager;

/**
 * @api {post} /api/content-manager/film/create
 * @apiName CreateFilm
 * @apiGroup ContentManager/FilmController
 * @apiVersion 1.0.0
 * @Feature ContentManager
 * @Description Create a film
 * @param array url
 * @param string title_video
 * @param string description
 * @param string image
 * @param int category
 * @return Response
 */
/*
Entry:
{
"url": [
"https://www.youtube.com/watch?v=1",
"https://www.youtube.com/watch?v=2"
],
"title_video": "Video title",
"description": "Video description",
"image": "https://www.image.com/image.png",
"category": 1
}
*/
class createFilm extends Controller
{
    public function checkers(Request $request): array
    {
        return [
            ["video/url", $request->getBody()['url']],
            ["video/title", $request->getBody()['title_video']],
            ["video/description", $request->getBody()['description']],
            ["video/image", $request->getBody()['image']],
            ["category/id", $request->getBody()['category']]
        ];
    }

    public function handler(Request $request, Response $response): void
    {
        $videoManager = new VideoManager();
        $video = $videoManager->createVideo(
            $this->floor->pickup("video/url"),
            $this->floor->pickup("video/title"),
            $this->floor->pickup("video/description"),
            $this->floor->pickup("category/id")
        );

        Film::insertOne([
            "video_id" => $video->getId(),
            "image" => $this->floor->pickup("video/image")
        ]);

        $response->send(["message" => "Film created"]);
    }
}

/**
 * @api {delete} /api/content-manager/film/delete
 * @apiName DeleteFilm
 * @apiGroup ContentManager/FilmController
 * @apiVersion 1.0.0
 * @Feature ContentManager
 * @Description Delete a film
 * @param int id
 * @return Response
 */
/*
Entry:
{
"id": 1
}
*/
class deleteFilm extends Controller
{
    public function checkers(Request $request): array
    {
        return [
            ["film/id", $request->getBody()['id']]
        ];
    }

    public function handler(Request $request, Response $response): void
    {
        $film = Film::findFirst([
            "id" => $this->floor->pickup("film/id")
        ]);
        $film->delete();

        $response->send(["message" => "Film deleted"]);
    }
}

/**
 * @api {get} /api/content-manager/film/get
 * @apiName GetFilm
 * @apiGroup ContentManager/FilmController
 * @apiVersion 1.0.0
 * @Feature ContentManager
 * @Description Get a film
 * @param int id
 * @return Response
 */
/*
Entry:
{
"id": 1
}
*/
class getFilm extends Controller
{
    public function checkers(Request $request): array
    {
        return [
            ["film/id", $request->getBody()['id']]
        ];
    }

    public function handler(Request $request, Response $response): void
    {
        $film = Film::findFirst([
            "id" => $this->floor->pickup("film/id")
        ]);

        $response->send(["film" => $film]);
    }
}

/**
 * @api {get} /api/content-manager/film/get-all
 * @apiName GetAllFilms
 * @apiGroup ContentManager/FilmController
 * @apiVersion 1.0.0
 * @Feature ContentManager
 * @Description Get all films
 * @return Response
 */
class getAllFilms extends Controller
{
    public function checkers(Request $request): array
    {
        return [];
    }

    public function handler(Request $request, Response $response): void
    {
        $films = Film::findMany();

        $response->send(["films" => $films]);
    }
}

/**
 * @api {get} /api/content-manager/film/get-where-film
 * @apiName GetWhereFilm
 * @apiGroup ContentManager/FilmController
 * @apiVersion 1.0.0
 * @Feature ContentManager
 * @Description Get a film
 * @param int id
 * @return Response
 */
/*
Entry:
{
"id": 1
}
*/
class getVideoWhereFilm extends Controller
{
    public function checkers(Request $request): array
    {
        return [
            ["film/id", $request->getBody()['id']]
        ];
    }

    public function handler(Request $request, Response $response): void
    {
        $film = Film::findFirst([
            "id" => $this->floor->pickup("film/id")
        ]);

        $response->send(["video" => $film->getVideo()]);
    }
}

/**
 * @api {put} /api/content-manager/film/update
 * @apiName UpdateFilm
 * @apiGroup ContentManager/FilmController
 * @apiVersion 1.0.0
 * @Feature ContentManager
 * @Description Update a film
 * @param int id
 * @param array url
 * @param string title_video
 * @param string description
 * @param string image
 * @param int category
 * @return Response
 */
/*
Entry:
{
"id": 1,
"url": [
"https://www.youtube.com/watch?v=1",
"https://www.youtube.com/watch?v=2"
],
"title_video": "Video title",
"description": "Video description",
"image": "https://www.image.com/image.png",
"category": 1
}
*/
class updateFilm extends Controller
{
    public function checkers(Request $request): array
    {
        return [
            ["film/id", $request->getBody()['id']],
            ["video/url", $request->getBody()['url']],
            ["video/title", $request->getBody()['title_video']],
            ["video/description", $request->getBody()['description']],
            ["video/image", $request->getBody()['image']],
            ["category/id", $request->getBody()['category']]
        ];
    }

    public function handler(Request $request, Response $response): void
    {
        $videoManager = new VideoManager();

        $film = Film::findFirst([
            "id" => $this->floor->pickup("film/id")
        ]);

        $videoManager->updateVideo(
            $film->getVideo()->getId(),
            $this->floor->pickup("video/url"),
            $this->floor->pickup("video/title"),
            $this->floor->pickup("video/description"),
            $this->floor->pickup("category/id")
        );

        $film->setImage($this->floor->pickup("video/image"));
        $film->save();

        $response->send(["message" => "Film updated"]);
    }
}
