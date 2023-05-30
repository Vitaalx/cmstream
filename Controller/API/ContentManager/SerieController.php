<?php

namespace controller\API\ContentManager\SerieController;

use Core\Controller;
use Core\Request;
use Core\Response;
use Entity\Serie;
use Services\Back\VideoManagerService as VideoManager;

/**
 * @api {post} /api/content-manager/serie/create
 * @apiName CreateSerie
 * @apiGroup ContentManager/SerieController
 * @apiVersion 1.0.0
 * @Feature ContentManager
 * @Description Create a serie
 * @param array url
 * @param string title_video
 * @param string description
 * @param string image
 * @param string title_serie
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
 "title_serie": "Serie title",
 "category": 1
}
 */
class createSerie extends Controller
{
    public function checkers(Request $request): array
    {
        return [
            ["video/url", $request->getBody()['url']],
            ["video/title", $request->getBody()['title_video']],
            ["video/description", $request->getBody()['description']],
            ["video/image", $request->getBody()['image']],
            ["serie/title", $request->getBody()['title_serie']],
            ["category/id", $request->getBody()['category']]
        ];
    }

    public function handler(Request $request, Response $response): void
    {
        try {
            $videoManager = new VideoManager();

            if (Serie::findFirst(["title" => $this->floor->pickup("serie/title")])) {
                throw new \Exception("Serie already exist");
            }

            $video = $videoManager->createVideo(
                $this->floor->pickup("video/url"),
                $this->floor->pickup("video/title"),
                $this->floor->pickup("video/description"),
                $this->floor->pickup("category/id")
            );
            Serie::insertOne([
                "video_id" => $video->getId(),
                "episode" => 1,
                "season" => 1,
                "image" => $this->floor->pickup("video/image"),
                "title" => $this->floor->pickup("serie/title")
            ]);

            $response->send(["message" => "Serie created"]);
        } catch (\Exception $e) {
            $response->send(["error" => $e->getMessage()]);
        }
    }
}

/**
 * @api {delete} /api/content-manager/serie/delete
 * @apiName GetSerie
 * @apiGroup ContentManager/SerieController
 * @apiVersion 1.0.0
 * @Feature ContentManager
 * @Description Get a serie
 * @param string name
 * @return Response
 */
/*
Entry:
{
 "name": "Serie title"
}
*/
class deleteSerie extends Controller
{
    public function checkers(Request $request): array
    {
        return [
            ["serie/title", $request->getQuery('name')],
        ];
    }

    public function handler(Request $request, Response $response): void
    {
        try {
            $serie = Serie::findMany(["title" => $this->floor->pickup("serie/title")]);
            foreach ($serie as $serie) {
                $serie->delete();
            }
            $response->send(["message" => "Serie deleted"]);
        } catch (\Exception $e) {
            $response->send(["error" => $e->getMessage()]);
        }
    }
}

/**
 * @api {get} /api/content-manager/serie/get-all
 * @apiName GetSerie
 * @apiGroup ContentManager/SerieController
 * @apiVersion 1.0.0
 * @Feature ContentManager
 * @Description Get a serie
 * @param string name
 * @return Response
 */
/*
Entry:
{
 "name": "Serie title"
}
*/
class getTitleAndImageWhereAllSeries extends Controller
{
    public function checkers(Request $request): array
    {
        return [];
    }

    public function handler(Request $request, Response $response): void
    {
        try {
            $series = Serie::findMany();
            $seriesInfos = [];
            $tmp = "";
            foreach ($series as $serie) {
                if ($tmp != $serie->getTitle()) {
                    $seriesInfos[] = [
                        "title" => $serie->getTitle(),
                        "image" => $serie->getImage()
                    ];
                    $tmp = $serie->getTitle();
                }
            }

            $response->send(["series" => $seriesInfos]);
        } catch (\Exception $e) {
            $response->send(["error" => $e->getMessage()]);
        }
    }
}
/**
 * @api {put} /api/content-manager/serie/update
 * @apiName UpdateSerie
 * @apiGroup ContentManager/SerieController
 * @apiVersion 1.0.0
 * @Feature ContentManager
 * @Description Update a serie
 * @param string title_serie
 * @param string new_title_serie
 * @param string image
 * @return Response
 */
/*
Entry:
{
 "title_serie": "Serie title",
 "new_title_serie": "New serie title",
 "image": "https://www.image.com/image.png"
}
*/
class updateSerieNameAndImage extends Controller
{
    public function checkers(Request $request): array
    {
        return [
            ["video/image", $request->getBody()['image']],
            ["serie/title", $request->getBody()['title_serie']],
            ["video/title", $request->getBody()['new_title_serie']],
        ];
    }

    public function handler(Request $request, Response $response): void
    {
        try {
            $serie = Serie::findMany(["title" => $this->floor->pickup("serie/title")]);
            foreach ($serie as $serie) {
                $serie->setTitle($this->floor->pickup("video/title"));
                $serie->setImage($this->floor->pickup("video/image"));
                $serie->save();
            }

            $response->send(["message" => "Serie updated"]);
        } catch (\Exception $e) {
            $response->send(["error" => $e->getMessage()]);
        }
    }
}

/**
 * @api {post} /api/content-manager/serie/add-episode
 * @apiName AddEpisode
 * @apiGroup ContentManager/SerieController
 * @apiVersion 1.0.0
 * @Feature ContentManager
 * @Description Add an episode to a serie
 * @param array url
 * @param string title_video
 * @param string description
 * @param string title_serie
 * @param int episode
 * @param int season
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
 "title_serie": "Serie title",
 "episode": 1,
 "season": 1,
 "category": 1
}
*/
class addEpisodeWhereSerie extends Controller
{
    public function checkers(Request $request): array
    {
        return [
            ["serie/title", $request->getBody()['title_serie']],
            ["video/url", $request->getBody()['url']],
            ["video/title", $request->getBody()['title_video']],
            ["video/description", $request->getBody()['description']],
            ["serie/episode", $request->getBody()['episode']],
            ["serie/season", $request->getBody()['season']],
        ];
    }

    public function handler(Request $request, Response $response): void
    {
        try {
            $videoManager = new VideoManager();

            if (!Serie::findFirst(["title" => $this->floor->pickup("serie/title")])) {
                throw new \Exception("Serie doesn't exist");
            }
            $serie = Serie::findFirst(["title" => $this->floor->pickup("serie/title")]);

            $video = $videoManager->createVideo(
                $this->floor->pickup("video/url"),
                $this->floor->pickup("video/title"),
                $this->floor->pickup("video/description"),
                $request->getBody()['category']
            );
            Serie::insertOne([
                "video_id" => $video->getId(),
                "episode" => $this->floor->pickup("serie/episode"),
                "season" => $this->floor->pickup("serie/season"),
                "image" => $serie->getImage(),
                "title" => $this->floor->pickup("serie/title")
            ]);

            $response->send(["message" => "Serie created"]);
        } catch (\Exception $e) {
            $response->send(["error" => $e->getMessage()]);
        }
    }
}

/**
 * @api {get} /api/content-manager/serie/get-episode
 * @apiName GetEpisode
 * @apiGroup ContentManager/SerieController
 * @apiVersion 1.0.0
 * @Feature ContentManager
 * @Description Get an episode
 * @param string name
 * @param int episode
 * @param int season
 * @return Response
 */
/*
Entry:
{
 "name": "Serie title",
 "episode": 1,
 "season": 1
}
*/
class getEpisodeWhereSerie extends Controller
{
    public function checkers(Request $request): array
    {
        return [
            ["serie/title", $request->getQuery('name')],
            ["serie/episode", $request->getQuery('episode')],
            ["serie/season", $request->getQuery('season')],
        ];
    }

    public function handler(Request $request, Response $response): void
    {
        try {
            $serie = Serie::findFirst([
                "title" => $this->floor->pickup("serie/title"),
                "episode" => $this->floor->pickup("serie/episode"),
                "season" => $this->floor->pickup("serie/season")
            ]);
            //die(var_dump($serie));
            $episode = [
                "episode" => $this->floor->pickup("serie/episode"),
                "season" => $this->floor->pickup("serie/season"),
                "image" => $serie->getImage(),
                "title" => $serie->getVideo()->getTitle(),
                "description" => $serie->getVideo()->getDescription(),
                "category" => $serie->getVideo()->getCategory()->getTitle(),
                "url" => $serie->getVideo()->getUrls(),
                "created_at" => $serie->getCreatedAt(),
                "updated_at" => $serie->getUpdatedAt()
            ];
            $response->send([$this->floor->pickup("serie/title") => $episode]);
        } catch (\Exception $e) {
            $response->send(["error" => $e->getMessage()]);
        }
    }
}

/**
 * @api {get} /api/content-manager/serie/get-all-episodes
 * @apiName GetAllEpisodes
 * @apiGroup ContentManager/SerieController
 * @apiVersion 1.0.0
 * @Feature ContentManager
 * @Description Get all episodes of a serie
 * @param string name
 * @return Response
 */
/*
Entry:
{
 "name": "Serie title"
}
*/
class getAllEpisodesWhereSerie extends Controller
{
    public function checkers(Request $request): array
    {
        return [
            ["serie/title", $request->getQuery('name')],
        ];
    }

    public function handler(Request $request, Response $response): void
    {
        try {
            $serie = Serie::findMany(["title" => $this->floor->pickup("serie/title")]);
            $episodes = [];
            foreach ($serie as $serie) {
                $episodes[] = [
                    "episode" => $serie->getEpisode(),
                    "season" => $serie->getSeason(),
                    "image" => $serie->getImage(),
                    "title" => $serie->getVideo()->getTitle(),
                    "description" => $serie->getVideo()->getDescription(),
                    "category" => $serie->getVideo()->getCategory()->getTitle(),
                    "url" => $serie->getVideo()->getUrls(),
                    "created_at" => $serie->getCreatedAt(),
                    "updated_at" => $serie->getUpdatedAt()
                ];
            }
            $response->send([$this->floor->pickup("serie/title") => $episodes]);
        } catch (\Exception $e) {
            $response->send(["error" => $e->getMessage()]);
        }
    }
}

/**
 * @api {delete} /api/content-manager/serie/delete-episode
 * @apiName DeleteEpisode
 * @apiGroup ContentManager/SerieController
 * @apiVersion 1.0.0
 * @Feature ContentManager
 * @Description Delete an episode
 * @param string name
 * @param int episode
 * @return Response
 /
/*
Entry:
{
 "name": "Serie title",
 "episode": 1,
 "season": 1
}
 */
class deleteEpisodeWhereSerie extends Controller
{
    public function checkers(Request $request): array
    {
        return [
            ["serie/title", $request->getQuery('name')],
            ["serie/episode", $request->getQuery('episode')],
            ["serie/season", $request->getQuery('season')],
        ];
    }

    public function handler(Request $request, Response $response): void
    {
        try {
            $serie = Serie::findFirst([
                "title" => $this->floor->pickup("serie/title"),
                "episode" => $this->floor->pickup("serie/episode"),
                "season" => $this->floor->pickup("serie/season")
            ]);
            $serie->delete();
            $response->send(["message" => "Episode deleted"]);
        } catch (\Exception $e) {
            $response->send(["error" => $e->getMessage()]);
        }
    }
}

/**
 * @api {put} /api/content-manager/serie/update-episode
 * @apiName UpdateEpisode
 * @apiGroup ContentManager/SerieController
 * @apiVersion 1.0.0
 * @Feature ContentManager
 * @Description Update an episode
 * @param string title_serie
 * @param string title_video
 * @param string description
 * @param int episode
 * @param int season
 * @param int category
 * @return Response
 */
/*
Entry:
{
 "title_serie": "Serie title",
 "title_video": "Video title",
 "description": "Video description",
 "episode": 1,
 "season": 1,
 "category": 1
}
*/
class updateEpisodeInfo extends Controller
{
    public function checkers(Request $request): array
    {
        return [
            ["serie/title", $request->getBody()['title_serie']],
            ["serie/episode", $request->getBody()['episode']],
            ["video/title", $request->getBody()['title_video']],
            ["video/description", $request->getBody()['description']],
            ["serie/episode", $request->getBody()['episode']],
            ["serie/season", $request->getBody()['season']],
            ["category/id", $request->getBody()['category']]
        ];
    }

    public function handler(Request $request, Response $response): void
    {
        try {
            $videoManager = new VideoManager();

            $serie = Serie::findFirst([
                "title" => $this->floor->pickup("serie/title"),
                "episode" => $this->floor->pickup("serie/episode"),
                "season" => $this->floor->pickup("serie/season")
            ]);

            $videoManager->updateVideo(
                $serie->getVideo()->getId(),
                $this->floor->pickup("video/title"),
                $this->floor->pickup("video/description"),
                $this->floor->pickup("category/id")
            );

            $response->send(["message" => "Episode updated"]);
        } catch (\Exception $e) {
            $response->send(["error" => $e->getMessage()]);
        }
    }
}
