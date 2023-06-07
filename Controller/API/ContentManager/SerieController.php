<?php

namespace Controller\API\ContentManager\SerieController;

use Core\Controller;
use Core\Request;
use Core\Response;
use Entity\Serie;
use Entity\Episode;
use Services\Back\VideoManagerService as VideoManager;
use Services\MustBeAdmin;

/**
 * @POST{/api/serie}
 * @apiName CreateSerie
 * @apiGroup ContentManager/SerieController
 * @apiVersion 1.0.0
 * @Feature ContentManager
 * @Description Create a serie
 * @param string description
 * @param string image
 * @param string title_serie
 * @param int category_id
 * @return Response
 */
/*
Entry:
{
 "description": "Video description",
 "image": "https://www.image.com/image.png",
 "title_serie": "Serie title",
 "category_id": 1
}
 */
class createSerie extends MustBeAdmin
{
    public function checkers(Request $request): array
    {
        return [
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

            Serie::insertOne([
                "description" => $this->floor->pickup("description"),
                "image" => $this->floor->pickup("image"),
                "title" => $this->floor->pickup("title_serie"),
                "category_id" => $this->floor->pickup("category")->getId()
            ]);

            $response->info("serie.created")->code(201)->send();
        } catch (\Exception $e) {
            $response->info("serie.error")->code(500)->send();
        }
    }
}

/**
 * @DELETE{/api/serie/{id}}
 * @apiName DeleteSerie
 * @apiGroup ContentManager/SerieController
 * @apiVersion 1.0.0
 * @Feature ContentManager
 * @Description Get a serie
 * @param int id
 * @return Response
 */
class deleteSerie extends MustBeAdmin
{
    public function checkers(Request $request): array
    {
        return [
            ["type/int", $request->getParam("id"), "serie_id"],
            ["serie/exist", fn () => $this->floor->pickup("serie_id"), "serie"]
        ];
    }

    public function handler(Request $request, Response $response): void
    {
        $this->floor->pickup("serie")->delete();
        $response->info("serie.deleted")->code(200)->send();
    }
}

/**
 * @GET{/api/serie/{id}}
 * @apiName GetSerie
 * @apiGroup ContentManager/SerieController
 * @apiVersion 1.0.0
 * @Feature ContentManager
 * @Description Get a serie
 * @param int id
 * @return Response
 */
class getSerie extends Controller
{
    public function checkers(Request $request): array
    {
        return [
            ["type/int", $request->getParam('id'), "serie_id"],
            ["serie/exist", fn () => $this->floor->pickup("serie_id"), "serie"]
        ];
    }

    public function handler(Request $request, Response $response): void
    {
        try {
            $serie = [
                "id" => $this->floor->pickup("serie")->getId(),
                "title" => $this->floor->pickup("serie")->getTitle(),
                "description" => $this->floor->pickup("serie")->getDescription(),
                "image" => $this->floor->pickup("serie")->getImage(),
                "category" => $this->floor->pickup("serie")->getCategory()->getTitle(),
                "created_at" => $this->floor->pickup("serie")->getCreatedAt(),
                "updated_at" => $this->floor->pickup("serie")->getUpdatedAt()
            ];
            $response->send([$this->floor->pickup("title_serie") => $serie]);
        } catch (\Exception $e) {
            $response->info("serie.error")->code(500)->send();
        }
    }
}

/**
 * @GET{/api/series}
 * @apiName GetSeries
 * @apiGroup ContentManager/SerieController
 * @apiVersion 1.0.0
 * @Feature ContentManager
 * @Description Get all serie
 * @return Response
 */
class getSeries extends Controller
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
            $response->send(["series" => $series]);
        } catch (\Exception $e) {
            $response->info("serie.error")->code(500)->send();
        }
    }
}
/**
 * @PUT{/api/serie/{id}}
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
 "title_serie": "Serie title"
 "image": "https://www.image.com/image.png",
 "description": "serie description"
}
*/
class updateSerie extends MustBeAdmin
{
    public function checkers(Request $request): array
    {
        return [
            ["type/string", $request->getBody()['title_serie'], "title_serie"],
            ["serie/title", fn () => $this->floor->pickup("title_serie"), "title_serie"],
            ["type/int", $request->getParam("id"), "serie_id"],
            ["serie/exist", fn () => $this->floor->pickup("serie_id"), "serie"],
            ["serie/notexist", fn () => $this->floor->pickup("title_serie")],
            ["type/string", $request->getBody()['image'], "image"],
            ["video/image", fn () => $this->floor->pickup("image"), "image"],
            ["type/string", $request->getBody()['description'], "description"],
            ["video/description", fn () => $this->floor->pickup("description"), "description"]
        ];
    }

    public function handler(Request $request, Response $response): void
    {
        try {
            $this->floor->pickup("serie")->setTitle($this->floor->pickup("title_serie"));
            $this->floor->pickup("serie")->setImage($this->floor->pickup("image"));
            $this->floor->pickup("serie")->setDescription($this->floor->pickup("description"));
            $this->floor->pickup("serie")->save();

            $response->info("serie.updated")->code(200)->send();
        } catch (\Exception $e) {
            $response->info("serie.error")->code(500)->send();
        }
    }
}

/**
 * @POST{/api/episode}
 * @apiName AddEpisode
 * @apiGroup ContentManager/SerieController
 * @apiVersion 1.0.0
 * @Feature ContentManager
 * @Description Add an episode to a serie
 * @param array url
 * @param string title_video
 * @param string description
 * @param int serie_id
 * @param int episode
 * @param int season
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
 "serie_id": 1,
 "episode": 1,
 "season": 1,
}
*/
class addEpisodeWhereSerie extends MustBeAdmin
{
    public function checkers(Request $request): array
    {
        $episode = $request->getBody();
        return [
            ["video/url", $episode['url'], "url"],
            ["type/string", $episode['title_video'], "title_video"],
            ["video/title", fn () => $this->floor->pickup("title_video"), "title_video"],
            ["type/string", $episode['description'], "description"],
            ["video/description", fn () => $this->floor->pickup("description"), "description"],
            ["type/int", $episode['serie_id'], "serie_id"],
            ["serie/exist", fn () => $this->floor->pickup("serie_id"), "serie"],
            ["type/int", $episode['episode'], "episode"],
            ["serie/episode", fn () => $this->floor->pickup("episode"), "episode"],
            ["type/int", $episode['season'], "season"],
            ["serie/season", fn () => $this->floor->pickup("season"), "season"],
            [
                "episode/notexist", [
                    fn () => $this->floor->pickup("serie_id"),
                    fn () => $this->floor->pickup("episode"),
                    fn () => $this->floor->pickup("season")
                ]
            ]
        ];
    }

    public function handler(Request $request, Response $response): void
    {
        try {
            /** @var Video $video */
            $video = VideoManager::createVideo(
                $this->floor->pickup("video/url"),
                $this->floor->pickup("title_video"),
                $this->floor->pickup("description"),
            );

            if ($video === null) $response->info("video.error")->code(500)->send();

            Episode::insertOne([
                "episode" => $this->floor->pickup("episode"),
                "season" => $this->floor->pickup("season"),
                "video_id" => $video->getId(),
                "serie_id" => $this->floor->pickup("serie")->getId(),
            ]);
        } catch (\Exception $e) {
            $response->info("episode.error")->code(500)->send();
        }
    }
}

/**
 * @GET{/api/episode/{serie_id}/{season}/{episode}}
 * @apiName GetEpisode
 * @apiGroup ContentManager/SerieController
 * @apiVersion 1.0.0
 * @Feature ContentManager
 * @Description Get an episode
 * @param int serie_id
 * @param int episode
 * @param int season
 * @return Response
 */
class getEpisodeWhereSerie extends Controller
{
    public function checkers(Request $request): array
    {
        return [
            ["type/int", $request->getParam('serie_id'), "serie_id"],
            ["serie/exist", fn () => $this->floor->pickup("serie_id"), "serie"],
            ["type/int", $request->getParam('episode'), "episode"],
            ["serie/episode", fn () => $this->floor->pickup("episode"), "episode"],
            ["type/int", $request->getParam('season'), "season"],
            ["serie/season", fn () => $this->floor->pickup("season"), "season"]
        ];
    }

    public function handler(Request $request, Response $response): void
    {
        try {
            $episode = Episode::findFirst([
                "serie_id" => $this->floor->pickup("serie")->getId(),
                "episode" => $this->floor->pickup("episode"),
                "season" => $this->floor->pickup("season")
            ]);
            if ($episode === null) $response->info("episode.notfound")->code(404)->send();
            $episode = [
                "id" => $episode->getId(),
                "episode" => $episode->getEpisode(),
                "season" => $episode->getSeason(),
                "image" => $episode->getSerie()->getImage(),
                "title" => $episode->getVideo()->getTitle(),
                "description" => $episode->getVideo()->getDescription(),
                "category" => $episode->getSerie()->getCategory()->getTitle(),
                "url" => $episode->getVideo()->getUrls(),
                "created_at" => $episode->getCreatedAt(),
                "updated_at" => $episode->getUpdatedAt()
            ];
            $response->send([$this->floor->pickup("title_serie") => $episode]);
        } catch (\Exception $e) {
            $response->info("episode.error")->code(500)->send();
        }
    }
}

/**
 * @GET{/api/episodes/{serie_id}}
 * @apiName GetEpisodes
 * @apiGroup ContentManager/SerieController
 * @apiVersion 1.0.0
 * @Feature ContentManager
 * @Description Get episodes of a serie
 * @param int serie_id
 * @return Response
 */
class getEpisodesWhereSerie extends Controller
{
    public function checkers(Request $request): array
    {
        return [
            ["type/int", $request->getParam('serie_id'), "serie_id"],
            ["serie/exist", fn () => $this->floor->pickup("serie_id"), "serie"]
        ];
    }

    public function handler(Request $request, Response $response): void
    {
        try {
            $episodes = Episode::findMany([
                "serie_id" => $this->floor->pickup("serie")->getId()
            ]);
            $response->send([$this->floor->pickup("title_serie") => $episodes]);
        } catch (\Exception $e) {
            $response->info("serie.error")->code(500)->send();
        }
    }
}

/**
 * @DELETE{/api/episode/{id}}
 * @apiName DeleteEpisode
 * @apiGroup ContentManager/SerieController
 * @apiVersion 1.0.0
 * @Feature ContentManager
 * @Description Delete an episode
 * @param int id
 * @return Response
 */
class deleteEpisode extends MustBeAdmin
{
    public function checkers(Request $request): array
    {
        return [
            ["type/int", $request->getParam('id'), "episode_id"],
            ["episode/exist", fn () => $this->floor->pickup("episode_id"), "episode"]
        ];
    }

    public function handler(Request $request, Response $response): void
    {
        try {
            $this->floor->pickup("episode")->delete();
            $response->info("episode.deleted")->code(200)->send();
        } catch (\Exception $e) {
            $response->info("episode.error")->code(500)->send();
        }
    }
}

/**
 * @PUT{/api/episode/data/{serie_id}}
 * @apiName UpdateEpisodeInfo
 * @apiGroup ContentManager/SerieController
 * @apiVersion 1.0.0
 * @Feature ContentManager
 * @Description Update an episode
 * @param string title_serie
 * @param string title_video
 * @param string description
 * @param int serie_id
 * @param int season
 * @param int category
 * @return Response
 */
/*
Entry:
{
 "title_video": "Video title",
 "description": "Video description",
 "episode": 1,
 "season": 1,
 "category_id": 1
}
*/
class updateEpisodeInfo extends MustBeAdmin
{
    public function checkers(Request $request): array
    {
        return [
            ["type/int", $request->getParam('serie_id'), "serie_id"],
            ["serie/exist", fn () => $this->floor->pickup("serie_id"), "serie"],
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

            $episode = Episode::findFirst([
                "serie_id" => $this->floor->pickup("serie")->getId(),
                "episode" => $this->floor->pickup("episode"),
                "season" => $this->floor->pickup("season")
            ]);

            VideoManager::updateVideo(
                $episode->getVideo()->getId(),
                $this->floor->pickup("title_video"),
                $this->floor->pickup("description"),
            );

            $response->info("episode.updated")->code(200)->send();
        } catch (\Exception $e) {
            $response->info("episode.error")->code(500)->send();
        }
    }
}

/**
 * @PUT{/api/episode/order/{id}}
 * @apiName UpdateEpisode
 * @apiGroup ContentManager/SerieController
 * @apiVersion 1.0.0
 * @Feature ContentManager
 * @Description Update an episode
 * @param int id
 * @param int episode_order
 * @param int season
 * @return Response
 */
/*
Entry:
{
 "episode_order": 1,
 "season": 1
}
*/
class updateEpisode extends MustBeAdmin
{
    public function checkers(Request $request): array
    {
        return [
            ["type/int", $request->getParam('id'), "episode_id"],
            ["episode/exist", fn () => $this->floor->pickup("episode_id"), "episode"],
            ["type/int", $request->getBody()['season'], "season"],
            ["type/int", $request->getBody()['episode_order'], "episode_nb"],
            ["serie/episode", fn () => $this->floor->pickup("episode_nb"), "episode_nb"],
            ["serie/season", fn () => $this->floor->pickup("season"), "season"],
            [
                "episode/notexist", [
                    fn () => $this->floor->pickup("episode")->getSerie()->getId(),
                    fn () => $this->floor->pickup("episode_nb"),
                    fn () => $this->floor->pickup("season")
                ]
            ]
        ];
    }

    public function handler(Request $request, Response $response): void
    {
        try {
            $this->floor->pickup("episode")->setSeason($this->floor->pickup("season"));
            $this->floor->pickup("episode")->setEpisode($this->floor->pickup("episode_nb"));
            $this->floor->pickup("episode")->save();
            $response->info("episode.updated")->code(200)->send();
        } catch (\Exception $e) {
            $response->info("episode.error")->code(500)->send();
        }
    }
}

// TODO
class getSerieWhereName extends Controller
{
    public function checkers(Request $request): array
    {
        return [
            ["type/string", $request->getParam('serie_title'), "serie_title"],
        ];
    }

    public function handler(Request $request, Response $response): void
    {
        try {
            $serie = Serie::findFirst([
                "title" => $this->floor->pickup("serie_title")
            ]);
            if ($serie === null) $response->info("serie.notfound")->code(404)->send();
            $response->send([$this->floor->pickup("name") => $serie]);
        } catch (\Exception $e) {
            $response->info("serie.error")->code(500)->send();
        }
    }
}
