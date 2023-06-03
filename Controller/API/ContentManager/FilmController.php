<?php

namespace Controller\API\ContentManager\FilmController;

use Core\Controller;
use Core\Request;
use Core\Response;

use Entity\Film;
use Services\Back\VideoManagerService as VideoManager;

/**
 * @POST{/api/film}
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
            /** @var Video $video */
            $video = VideoManager::createVideo(
                $this->floor->pickup("video/url"),
                $this->floor->pickup("title_video"),
                $this->floor->pickup("description")
            );
            if ($video === null) {
                $response->info("video.error")->code(500)->send();
            }

            Film::insertOne([
                "video_id" => $video->getId(),
                "image" => $this->floor->pickup("video/image"),
                "category_id" => $this->floor->pickup("category")->getId()
            ]);

            $response->info("film.created")->code(201)->send();
        } catch (\Exception $e) {
            $response->info("film.error")->code(500)->send();
        }
    }
}

/**
 * @DELETE{/api/film/{id}}
 * @apiName DeleteFilm
 * @apiGroup ContentManager/FilmController
 * @apiVersion 1.0.0
 * @Feature ContentManager
 * @Description Delete a film
 * @param int id
 * @return Response
 */
class deleteFilm extends Controller
{
    public function checkers(Request $request): array
    {
        return [
            ["type/int", $request->getParam('id'), "film_id"],
            ["film/exist", fn () => $this->floor->pickup("film_id"), "film"]
        ];
    }

    public function handler(Request $request, Response $response): void
    {
        try {
            $this->floor->pickup("film")->delete();
            $response->info("film.deleted")->code(204)->send();
        } catch (\Exception $e) {
            $response->info("film.error")->code(500)->send();
        }
    }
}

/**
 * @GET{/api/film/{id}}
 * @apiName GetFilm
 * @apiGroup ContentManager/FilmController
 * @apiVersion 1.0.0
 * @Feature ContentManager
 * @Description Get a film
 * @param int id
 * @return Response
 */
class getFilm extends Controller
{
    public function checkers(Request $request): array
    {
        return [
            ["type/int", $request->getParam('id'), "film_id"],
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
 * @GET{/api/films/}
 * @apiName GetFilms
 * @apiGroup ContentManager/FilmController
 * @apiVersion 1.0.0
 * @Feature ContentManager
 * @Description Get films
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
            if ($films === null) $response->info("film.notfound")->code(404)->send();

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
 * @PUT{/api/film/{id}}
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
            ["type/int", $request->getParam('id'), "film_id"],
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
            VideoManager::updateVideo(
                $this->floor->pickup("film")->getVideo()->getId(),
                $this->floor->pickup("title_video"),
                $this->floor->pickup("description")
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
