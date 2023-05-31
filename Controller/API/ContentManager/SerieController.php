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
 "category_id": 1
}
 */
class createSerie extends Controller
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
            ["type/string", $request->getBody()['title_serie'], "title_serie"],
            ["serie/title", fn () => $this->floor->pickup("title_serie"), "title_serie"],
            ["serie/notexist", fn () => $this->floor->pickup("title_serie")],
            ["type/int", $request->getBody()['category_id'], "category_id"],
            ["category/exist", fn () => $this->floor->pickup("category_id"), "category"]
        ];
    }

    public function handler(Request $request, Response $response): void
    {
        try {
            $videoManager = new VideoManager();

            $video = $videoManager->createVideo(
                $this->floor->pickup("video/url"),
                $this->floor->pickup("title_serie"),
                $this->floor->pickup("description"),
                $this->floor->pickup("category")->getId()
            );
            if ($video === null) $response->info("video.error")->code(500)->send();

            Serie::insertOne([
                "video_id" => $video->getId(),
                "episode" => 1,
                "season" => 1,
                "image" => $this->floor->pickup("image"),
                "title" => $this->floor->pickup("title_serie"),
            ]);

            $response->info("serie.created")->code(201)->send();
        } catch (\Exception $e) {
            $response->info("serie.error")->code(500)->send();
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
 "title_serie": "Serie title"
}
*/
class deleteSerie extends Controller
{
    public function checkers(Request $request): array
    {
        return [
            ["type/string", $request->getBody()['title_serie'], "title_serie"],
            ["serie/exist", fn () => $this->floor->pickup("title_serie")]
        ];
    }

    public function handler(Request $request, Response $response): void
    {
        try {
            $serie = Serie::findMany(["title" => $this->floor->pickup("title_serie")]);
            foreach ($serie as $serie) {
                $serie->delete();
            }
            $response->info("serie.deleted")->code(200)->send();
        } catch (\Exception $e) {
            $response->info("serie.error")->code(500)->send();
        }
    }
}

/**
 * @api {get} /api/content-manager/serie/get-all
 * @apiName GetSerie
 * @apiGroup ContentManager/SerieController
 * @apiVersion 1.0.0
 * @Feature ContentManager
 * @Description Get all serie
 * @return Response
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
            if (empty($series)) $response->info("serie.notfound")->code(404)->send();
            $seriesInfos = [];
            $tmp = "";
            foreach ($series as $serie) {
                if ($tmp != $serie->getTitle()) {
                    $seriesInfos[] = [
                        "title" => $serie->getTitle(),
                        "image" => $serie->getImage(),
                        "created_at" => $serie->getCreatedAt(),
                        "updated_at" => $serie->getUpdatedAt()
                    ];
                    $tmp = $serie->getTitle();
                }
            }

            $response->send(["series" => $seriesInfos]);
        } catch (\Exception $e) {
            $response->info("serie.error")->code(500)->send();
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
            ["type/string", $request->getBody()['image'], "image"],
            ["video/image", $request->getBody()['image']],
            ["type/string", $request->getBody()['title_serie'], "title_serie"],
            ["serie/title", fn () => $this->floor->pickup("title_serie"), "title_serie"],
            ["serie/exist", fn () => $this->floor->pickup("title_serie")],
            ["type/string", $request->getBody()['new_title_serie'], "new_title_serie"],
            ["serie/title", fn () => $this->floor->pickup("new_title_serie"), "new_title_serie"],
            ["serie/notexist", fn () => $this->floor->pickup("new_title_serie")],
        ];
    }

    public function handler(Request $request, Response $response): void
    {
        try {
            $series = Serie::findMany(["title" => $this->floor->pickup("title_serie")]);
            if (empty($series)) $response->info("serie.notfound")->code(404)->send();
            foreach ($series as $serie) {
                $serie->setTitle($this->floor->pickup("new_title_serie"));
                $serie->setImage($this->floor->pickup("image"));
                $serie->setUpdatedAt(date("Y-m-d H:i:s"));
                $serie->save();
            }

            $response->info("serie.updated")->code(200)->send();
        } catch (\Exception $e) {
            $response->info("serie.error")->code(500)->send();
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
 "category_id": 1
}
*/
class addEpisodeWhereSerie extends Controller
{
    public function checkers(Request $request): array
    {
        return [
            ["type/string", $request->getBody()['title_serie'], "title_serie"],
            ["serie/title", fn () => $this->floor->pickup('title_serie'), "title_serie"],
            ["serie/exist", fn () => $this->floor->pickup("title_serie"), "serie"],
            ["video/url", $request->getBody()['url']],
            ["type/string", $request->getBody()['title_video'], "title_video"],
            ["video/title", fn () => $this->floor->pickup('title_video')],
            ["type/string", $request->getBody()['description'], "description"],
            ["video/description", fn () => $this->floor->pickup('title_video'), "description"],
            ["type/int", $request->getBody()['episode'], "episode"],
            ["serie/episode", fn () => $this->floor->pickup('episode'), "episode"],
            ["type/int", $request->getBody()['season'], "season"],
            ["serie/season", fn () => $this->floor->pickup('season'), "season"],
            ["type/int", $request->getBody()['category_id'], "category_id"],
            ["category/exist", fn () => $this->floor->pickup("category_id"), "category"]
        ];
    }

    public function handler(Request $request, Response $response): void
    {
        try {
            if (Serie::findFirst([
                "title" => $this->floor->pickup("title_serie"),
                "episode" => $this->floor->pickup("episode"),
                "season" => $this->floor->pickup("season")
            ]) !== null) {
                $response->info("episode.exist")->code(400)->send();
            }

            $videoManager = new VideoManager();

            $video = $videoManager->createVideo(
                $this->floor->pickup("video/url"),
                $this->floor->pickup("title_video"),
                $this->floor->pickup("description"),
                $this->floor->pickup("category")->getId()
            );

            if ($video === null) $response->info("video.error")->code(500)->send();

            Serie::insertOne([
                "video_id" => $video->getId(),
                "episode" => $this->floor->pickup("episode"),
                "season" => $this->floor->pickup("season"),
                "image" => $this->floor->pickup("serie")->getImage(),
                "title" => $this->floor->pickup("serie")->getTitle(),
            ]);

            $response->info("episode.created")->code(201)->send();
        } catch (\Exception $e) {
            $response->info("episode.error")->code(500)->send();
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
 "title_serie": "Serie title",
 "episode": 1,
 "season": 1
}
*/
class getEpisodeWhereSerie extends Controller
{
    public function checkers(Request $request): array
    {
        return [
            ["type/string", $request->getBody()['title_serie'], "title_serie"],
            ["serie/title", fn () => $this->floor->pickup("title_serie"), "title_serie"],
            ["serie/exist", fn () => $this->floor->pickup("title_serie")],
            ["type/int", $request->getBody()['episode'], "episode"],
            ["serie/episode", fn () => $this->floor->pickup("episode"), "episode"],
            ["type/int", $request->getBody()['season'], "season"],
            ["serie/season", fn () => $this->floor->pickup("season"), "season"]
        ];
    }

    public function handler(Request $request, Response $response): void
    {
        try {
            $serie = Serie::findFirst([
                "title" => $this->floor->pickup("title_serie"),
                "episode" => $this->floor->pickup("episode"),
                "season" => $this->floor->pickup("season")
            ]);
            if ($serie === null) $response->info("episode.notfound")->code(404)->send();
            $episode = [
                "episode" => $this->floor->pickup("episode"),
                "season" => $this->floor->pickup("season"),
                "image" => $serie->getImage(),
                "title" => $serie->getVideo()->getTitle(),
                "description" => $serie->getVideo()->getDescription(),
                "category" => $serie->getVideo()->getCategory()->getTitle(),
                "url" => $serie->getVideo()->getUrls(),
                "created_at" => $serie->getCreatedAt(),
                "updated_at" => $serie->getUpdatedAt()
            ];
            $response->send([$this->floor->pickup("title_serie") => $episode]);
        } catch (\Exception $e) {
            $response->info("episode.error")->code(500)->send();
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
 "title_serie": "Serie title"
}
*/
class getAllEpisodesWhereSerie extends Controller
{
    public function checkers(Request $request): array
    {
        return [
            ["type/string", $request->getBody()['title_serie'], "title_serie"],
            ["serie/title", fn () => $this->floor->pickup("title_serie"), "title_serie"],
            ["serie/exist", fn () => $this->floor->pickup("title_serie")],
        ];
    }

    public function handler(Request $request, Response $response): void
    {
        try {
            $serie = Serie::findMany(["title" => $this->floor->pickup("title_serie")]);
            if (empty($serie)) $response->info("serie.notfound")->code(404)->send();
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
            $response->send([$this->floor->pickup("title_serie") => $episodes]);
        } catch (\Exception $e) {
            $response->info("serie.error")->code(500)->send();
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
 "title_serie": "Serie title",
 "episode": 1,
 "season": 1
}
 */
class deleteEpisodeWhereSerie extends Controller
{
    public function checkers(Request $request): array
    {
        return [
            ["type/string", $request->getBody()['title_serie'], "title_serie"],
            ["serie/title", fn () => $this->floor->pickup("title_serie"), "title_serie"],
            ["serie/exist", fn () => $this->floor->pickup("title_serie")],
            ["type/int", $request->getBody()['episode'], "episode"],
            ["serie/episode", fn () => $this->floor->pickup("episode"), "episode"],
            ["type/int", $request->getBody()['season'], "season"],
            ["serie/season", fn () => $this->floor->pickup("season"), "season"]
        ];
    }

    public function handler(Request $request, Response $response): void
    {
        try {
            $serie = Serie::findFirst([
                "title" => $this->floor->pickup("title_serie"),
                "episode" => $this->floor->pickup("episode"),
                "season" => $this->floor->pickup("season")
            ]);
            if ($serie === null) $response->info("episode.notfound")->code(404)->send();
            $serie->delete();
            $response->info("episode.deleted")->code(200)->send();
        } catch (\Exception $e) {
            $response->info("episode.error")->code(500)->send();
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
 "category_id": 1
}
*/
class updateEpisodeInfo extends Controller
{
    public function checkers(Request $request): array
    {
        return [
            ["type/string", $request->getBody()['title_serie'], "title_serie"],
            ["serie/title", fn () => $this->floor->pickup("title_serie"), "title_serie"],
            ["serie/exist", fn () => $this->floor->pickup("title_serie")],
            ["type/string", $request->getBody()['title_video'], "title_video"],
            ["video/title", fn () => $this->floor->pickup("title_video"), "title_video"],
            ["type/string", $request->getBody()['description'], "description"],
            ["video/description", fn () => $this->floor->pickup("description"), "description"],
            ["type/int", $request->getBody()['episode'], "episode"],
            ["serie/episode", fn () => $this->floor->pickup("episode"), "episode"],
            ["type/int", $request->getBody()['season'], "season"],
            ["serie/season", fn () => $this->floor->pickup("season"), "season"],
            ["type/int", $request->getBody()['category_id'], "category_id"],
            ["category/exist", fn () => $this->floor->pickup("category_id"), "category"]
        ];
    }

    public function handler(Request $request, Response $response): void
    {
        try {
            $videoManager = new VideoManager();

            $serie = Serie::findFirst([
                "title" => $this->floor->pickup("title_serie"),
                "episode" => $this->floor->pickup("episode"),
                "season" => $this->floor->pickup("season")
            ]);
            if ($serie === null) $response->info("episode.notfound")->code(404)->send();

            $videoManager->updateVideo(
                $serie->getVideo()->getId(),
                $this->floor->pickup("title_video"),
                $this->floor->pickup("description"),
                $this->floor->pickup("category")->getId()
            );

            $response->info("episode.updated")->code(200)->send();
        } catch (\Exception $e) {
            $response->info("episode.error")->code(500)->send();
        }
    }
}
