<?php

namespace controller\API\ContentManagerController;

use Core\Controller;
use Core\Request;
use Core\Response;

use Entity\Video;
use Entity\Category;

use Services\Back\VideoManagerService as VideoManager;
use Services\Back\SerieManagerService as SerieManager;
use Services\Back\MovieManagerService as MovieManager;
use Services\Back\CategoryManagerService as CategoryManager;

class createSerie extends Controller
{
    public function checkers(Request $request): array
    {
        return [
            ["video/url", $request->getBody()['url']],
            ["video/title", $request->getBody()['title']],
            ["video/description", $request->getBody()['description']],
            ["video/image", $request->getBody()['image']],
            ["serie/episode", $request->getBody()['episode']],
            ["serie/season", $request->getBody()['season']],
        ];
    }

    public function handler(Request $request, Response $response): void
    {
        try {
            $videoManager = new VideoManager();
            $serieManager = new SerieManager();

            $video = $videoManager->createVideo(
                $this->floor->pickup("video/url"),
                $this->floor->pickup("video/title"),
                $this->floor->pickup("video/description"),
                $request->getBody()['category']
            );
            $serieManager->createSerie(
                $video->getId(),
                $this->floor->pickup("serie/episode"),
                $this->floor->pickup("serie/season"),
                $this->floor->pickup("video/image")
            );

            $response->send(["message" => "Serie created"]);
        } catch (\Exception $e) {
            $response->send(["error" => $e->getMessage()]);
        }
    }
}

class createMovie extends Controller
{
    public function checkers(Request $request): array
    {
        return [
            ["video/url", $request->getBody()['url']],
            ["video/title", $request->getBody()['title']],
            ["video/description", $request->getBody()['description']],
            ["video/image", $request->getBody()['image']],
        ];
    }

    public function handler(Request $request, Response $response): void
    {
        try {
            $videoManager = new VideoManager();
            $movieManager = new MovieManager();

            $video = $videoManager->createVideo(
                $this->floor->pickup("video/url"),
                $this->floor->pickup("video/title"),
                $this->floor->pickup("video/description"),
                $request->getBody()['category']
            );
            $movieManager->createMovie(
                $video->getId(),
                $this->floor->pickup("video/image")
            );

            $response->send(["message" => "Movie created"]);
        } catch (\Exception $e) {
            $response->send(["error" => $e->getMessage()]);
        }
    }
}

class createCategory extends Controller
{
    public function checkers(Request $request): array
    {
        return [
            ["category/name", $request->getBody()['name']],
        ];
    }

    public function handler(Request $request, Response $response): void
    {
        try {
            $categoryManager = new CategoryManager();

            $categoryManager->createCategory(
                $this->floor->pickup("category/name")
            );

            $response->send(["message" => "Category created"]);
        } catch (\Exception $e) {
            $response->send(["error" => $e->getMessage()]);
        }
    }
}