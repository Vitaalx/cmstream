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
"category_id": 1
}
*/
class createFilm extends Controller
{
    public function checkers(Request $request): array
    {
        return [
            ["video/url", $request->getBody()['url']],
            ["type/string", $request->getBody()['title_video'], "title_video"],
            ["video/title", fn () => $this->floor->pickup("title_video"), "title_video"],
            ["type/string", $request->getBody()['description'], "description"],
            ["video/description", fn () => $this->floor->pickup("description"), "description"],
            ["type/string", $request->getBody()['image'], "image"],
            ["video/image", fn () => $this->floor->pickup("image"), "image"],
            ["type/int", $request->getBody()['category'], "category_id"],
            ["category/exist", fn () => $this->floor->pickup("category_id"), "category"]
        ];
    }

    public function handler(Request $request, Response $response): void
    {
        try {
            $videoManager = new VideoManager();
            $video = $videoManager->createVideo(
                $this->floor->pickup("video/url"),
                $this->floor->pickup("title_video"),
                $this->floor->pickup("description"),
                $this->floor->pickup("category")->getId()
            );
            if ($video === null) {
                $response->info("video.error")->code(500)->send();
            }

            Film::insertOne([
                "video_id" => $video->getId(),
                "image" => $this->floor->pickup("video/image")
            ]);

            $response->info("film.created")->code(201)->send();
        } catch (\Exception $e) {
            $response->info("film.error")->code(500)->send();
        }
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
"film_id": 1
}
*/
class deleteFilm extends Controller
{
    public function checkers(Request $request): array
    {
        return [
            ["type/int", $request->getBody()['film_id'], "film_id"],
            ["film/exist", fn () => $this->floor->pickup("film_id"), "film"]
        ];
    }

    public function handler(Request $request, Response $response): void
    {
        $film = $this->floor->pickup("film");
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
"film_id": 1
}
*/
class getFilm extends Controller
{
    public function checkers(Request $request): array
    {
        return [
            ["type/int", $request->getBody()['film_id'], "film_id"],
            ["film/exist", fn () => $this->floor->pickup("film_id"), "film"]
        ];
    }

    public function handler(Request $request, Response $response): void
    {
        $film = [];
        $film[] = $this->floor->pickup("film");
        $film[] = $this->floor->pickup("film")->getVideo();

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
        try {
            $films = Film::findMany();
            if ($films === null) {
                $response->info("film.notfound")->code(404)->send();
            }

            $films = array_map(function ($film) {
                return [
                    "id" => $film->getId(),
                    "video" => $film->getVideo(),
                    "image" => $film->getImage()
                ];
            }, $films);

            $response->send(["films" => $films]);
        } catch (\Exception $e) {
            $response->info("film.error")->code(500)->send();
        }
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
"film_id": 1,
"url": [
"https://www.youtube.com/watch?v=1",
"https://www.youtube.com/watch?v=2"
],
"title_video": "Video title",
"description": "Video description",
"image": "https://www.image.com/image.png",
"category_id": 1
}
*/
class updateFilm extends Controller
{
    public function checkers(Request $request): array
    {
        return [
            ["type/int", $request->getBody()['id'], "film_id"],
            ["film/exist", fn () => $this->floor->pickup("film_id"), "film"],
            ["video/url", $request->getBody()['url']],
            ["type/string", $request->getBody()['title_video'], "title_video"],
            ["video/title", fn () => $this->floor->pickup("title_video"), "title_video"],
            ["type/string", $request->getBody()['description'], "description"],
            ["video/description", fn () => $this->floor->pickup("description"), "description"],
            ["type/string", $request->getBody()['image'], "image"],
            ["video/image", fn () => $this->floor->pickup("image"), "image"],
            ["type/int", $request->getBody()['category'], "category_id"],
            ["category/exist", fn () => $this->floor->pickup("category_id"), "category"]
        ];
    }

    public function handler(Request $request, Response $response): void
    {
        try {
            $videoManager = new VideoManager();

            $videoManager->updateVideo(
                $this->floor->pickup("film")->getVideo(),
                $this->floor->pickup("video/url"),
                $this->floor->pickup("title_video"),
                $this->floor->pickup("description"),
                $this->floor->pickup("category")->getId()
            );

            $this->floor->pickup("film")->setImage($this->floor->pickup("image"));
            $this->floor->pickup("film")->setUpdatedAt(date("Y-m-d H:i:s"));
            $this->floor->pickup("film")->save();

            $response->info("film.updated")->code(200)->send();
        } catch (\Exception $e) {
            $response->info("film.error")->code(500)->send();
        }
    }
}
