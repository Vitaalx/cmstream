<?php

namespace Controller\API\ContentManager\MovieController;

use Core\Controller;
use Core\Request;
use Core\Response;

use Entity\Category;
use Entity\Content;
use Entity\Movie;
use Entity\Video;
use Services\Access\AccessContentsManager;
use Services\Access\AccessDashboard;
use Services\Back\VideoManagerService as VideoManager;

/**
 * @POST{/api/movie}
 * @apiName CreateMovie
 * @apiGroup ContentManager/MovieController
 * @apiVersion 1.0.0
 * @Feature ContentManager
 * @Description Create a movie
 * @param array url
 * @param string title_video
 * @param string description
 * @param string image
 * @param int category
 * @return Response
 * 
 * This controller is used to create a movie.
 */
/*
Entry:
{
"title_video": "Video title",
"description": "Video description",
"image": "https://www.image.com/image.png",
"category_id": 1
}
*/
class createMovie extends AccessContentsManager
{
    public function checkers(Request $request): array
    {
        return [
            ["type/string", $request->getBody()['title_video'], "title_video"],
            ["video/title", fn () => $this->floor->pickup("title_video"), "title_video"],
            ["type/string", $request->getBody()['description'], "description"],
            ["video/description", fn () => $this->floor->pickup("description"), "description"],
            ["type/string", $request->getBody()['image'], "image"],
            ["video/image", fn () => $this->floor->pickup("image"), "image"],
            ["type/int", $request->getBody()['category_id'], "category_id"],
            ["category/exist", fn () => $this->floor->pickup("category_id"), "category"],
            ["type/string", $request->getBody()['release_date'], "release_date"]
        ];
    }

    public function handler(Request $request, Response $response): void
    {
        /** @var Category $category*/
        $category = $this->floor->pickup("category");

        $video = Video::insertOne([]);
        $movie = Movie::insertOne(
            fn (Movie $movie) => $movie
                ->setTitle($this->floor->pickup("title_video"))
                ->setDescription($this->floor->pickup("description"))
                ->setVideo($video)
                ->setImage($this->floor->pickup("image"))
                ->setReleaseDate($this->floor->pickup("release_date"))
        );
        Content::insertOne(
            fn (Content $content) => $content
                ->setValue($movie)
                ->setCategory($category)
        );

        $response->code(201)->info("movie.created")->send($movie);
    }
}

/**
 * @DELETE{/api/movie/{id}}
 * @apiName DeleteMovie
 * @apiGroup ContentManager/MovieController
 * @apiVersion 1.0.0
 * @Feature ContentManager
 * @Description Delete a movie
 * @param int id
 * @return Response
 * 
 * This controller is used to delete a movie.
 */
class deleteMovie extends AccessContentsManager
{
    public function checkers(Request $request): array
    {
        return [
            ["type/int", $request->getParam('id'), "movie_id"],
            ["movie/exist", fn () => $this->floor->pickup("movie_id"), "movie"]
        ];
    }

    public function handler(Request $request, Response $response): void
    {
        $this->floor->pickup("movie")->delete();
        $response->info("movie.deleted")->code(204)->send();
    }
}

/**
 * @GET{/api/movie/{id}}
 * @apiName GetMovie
 * @apiGroup ContentManager/MovieController
 * @apiVersion 1.0.0
 * @Feature ContentManager
 * @Description Get a movie
 * @param int id
 * @return Response
 * 
 * This controller is used to get a movie.
 */
class getMovie extends Controller
{
    public function checkers(Request $request): array
    {
        return [
            ["type/int", $request->getParam('id'), "movie_id"],
            ["movie/exist", fn () => $this->floor->pickup("movie_id"), "movie"]
        ];
    }

    public function handler(Request $request, Response $response): void
    {
        /** @var Movie $movie */
        $movie = $this->floor->pickup("movie");

        Movie::groups("video", "content", "vote", "category", "urls");
        $response->code(200)->info("movie.get")->send($movie);
    }
}

/**
 * @GET{/api/movies}
 * @apiName GetMovies
 * @apiGroup ContentManager/MovieController
 * @apiVersion 1.0.0
 * @Feature ContentManager
 * @Description Get movies
 * @return Response
 * 
 * This controller is used to get movies.
 */
class getMovies extends AccessContentsManager
{
    public function checkers(Request $request): array
    {
        return [
            ["type/int", $request->getQuery("page") ?? 0, "page"],
            ["type/string", $request->getQuery("title") ?? "", "title"]
        ];
    }

    public function handler(Request $request, Response $response): void
    {
        $page = $this->floor->pickup("page");
        $title = $this->floor->pickup("title");
        $number = 5;
        
        $movies = Movie::findMany(
            [
                "title" => [
                    "\$CTN" => $title
                ]
            ],
            ["ORDER_BY" => ["id"], "OFFSET" => $number * $page, "LIMIT" => $number]
        );
        
        Movie::groups("dateProps", "category", "content");

        $response->code(200)->info("movies.get")->send($movies);
    }
}

/**
 * @GET{/api/movies/count}
 * @apiName GetMoviesCount
 * @apiGroup ContentManager/MovieController
 * @apiVersion 1.0.0
 * @Feature ContentManager
 * @return Response
 * 
 * This controller is used to get movies count.
 */
class getMoviesCount extends AccessDashboard
{
    public function checkers(Request $request): array
    {
        return [];
    }

    public function handler(Request $request, Response $response): void
    {
        $response
            ->code(200)
            ->info("count")
            ->send(["count" => Movie::count()]);
    }
}

/**
 * @PUT{/api/movie/{id}}
 * @apiName UpdateMovie
 * @apiGroup ContentManager/MovieController
 * @apiVersion 1.0.0
 * @Feature ContentManager
 * @Description Update a movie
 * @param int id
 * @param array url
 * @param string title_video
 * @param string description
 * @param string image
 * @param int category
 * @return Response
 * 
 * This controller is used to update a movie.
 */
/*
Entry:
{
"title_video": "Video title",
"description": "Video description",
"image": "https://www.image.com/image.png",
"category_id": 1
}
*/
class updateMovie extends AccessContentsManager
{
    public function checkers(Request $request): array
    {
        return [
            ["type/int", $request->getParam('id'), "movie_id"],
            ["movie/exist", fn () => $this->floor->pickup("movie_id"), "movie"],
            ["type/string", $request->getBody()['title_video'], "title_video"],
            ["video/title", fn () => $this->floor->pickup("title_video"), "title_video"],
            ["type/string", $request->getBody()['description'], "description"],
            ["video/description", fn () => $this->floor->pickup("description"), "description"],
            ["type/string", $request->getBody()['image'], "image"],
            ["video/image", fn () => $this->floor->pickup("image"), "image"],
            ["type/int", $request->getBody()['category'], "category_id"],
            ["category/exist", fn () => $this->floor->pickup("category_id"), "category"],
            ["type/string", $request->getBody()['release_date'], "release_date"]
        ];
    }

    public function handler(Request $request, Response $response): void
    {
        /** @var \Entity\Category $category */
        $category = $this->floor->pickup("category");

        /** @var Movie $movie */
        $movie = $this->floor->pickup("movie");

        $movie->setImage($this->floor->pickup("image"));
        $movie->setTitle($this->floor->pickup("title_video"));
        $movie->getContent()->setCategory($category)->save();
        $movie->setDescription($this->floor->pickup("description"));
        $movie->setReleaseDate($this->floor->pickup("release_date"));
        $movie->setUpdatedAt(date("Y-m-d H:i:s"));
        $movie->save();

        $response->info("movie.updated")->code(200)->send();
    }
}