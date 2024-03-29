<?php

namespace Controller\API\ContentManager\SerieController;

use Core\Controller;
use Core\Logger;
use Core\QueryBuilder;
use Core\Request;
use Core\Response;
use Entity\Category;
use Entity\Content;
use Entity\Serie;
use Entity\Episode;
use Entity\Video;
use Services\Access\AccessContentsManager;
use Services\Access\AccessDashboard;
use Services\Back\VideoManagerService as VideoManager;
use Services\MustBeConnected;

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

class createSerie extends AccessContentsManager
{
    public function checkers(Request $request): array
    {
        return [
            ["type/string", $request->getBody()['description'], "description"],
            ["video/description", fn() => $this->floor->pickup("description"), "description"],
            ["type/string", $request->getBody()['image'], "image"],
            ["video/image", fn() => $this->floor->pickup("image"), "image"],
            ["type/string", $request->getBody()['title_serie'], "title_serie"],
            ["serie/title", fn() => $this->floor->pickup("title_serie"), "title_serie"],
            ["serie/notexist", [
                "title" => $request->getBody()['title_serie']
            ]],
            ["type/int", $request->getBody()['category_id'], "category_id"],
            ["category/exist", fn() => $this->floor->pickup("category_id"), "category"],
            ["type/string", $request->getBody()['release_date'], "release_date"]
        ];
    }

    public function handler(Request $request, Response $response): void
    {
        /** @var Category $category */
        $category = $this->floor->pickup("category");

        $serie = Serie::insertOne(
            fn (Serie $s) => $s
                ->setImage($this->floor->pickup("image"))
                ->setDescription($this->floor->pickup("description"))
                ->setTitle($this->floor->pickup("title_serie"))
                ->setReleaseDate($this->floor->pickup("release_date"))
        );

        Content::insertOne(
            fn(Content $content) => $content
                ->setValue($serie)
                ->setCategory($category)
        );

        $response->info("serie.created")->code(201)->send($serie);
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
class deleteSerie extends AccessContentsManager
{
    public function checkers(Request $request): array
    {
        return [
            ["type/int", $request->getParam("id"), "serie_id"],
            ["serie/exist", fn() => $this->floor->pickup("serie_id"), "serie"]
        ];
    }

    public function handler(Request $request, Response $response): void
    {
        /** @var Serie $serie */
        $serie = $this->floor->pickup("serie");
        $serie->delete();
        $response->info("serie.deleted")->code(204)->send();
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
            ["serie/exist", fn() => $this->floor->pickup("serie_id"), "serie"]
        ];
    }

    public function handler(Request $request, Response $response): void
    {
        /** @var Serie $serie */
        $serie = $this->floor->pickup("serie");

        Serie::groups("content", "vote", "category");
        $response->send($serie);
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
class getSeries extends AccessContentsManager
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

        $series = Serie::findMany(
            [
                "title" => [
                    "\$CTN" => $title
                ]
            ],
            ["ORDER_BY" => ["id"], "OFFSET" => $number * $page, "LIMIT" => $number]
        );

        Serie::groups("dateProps", "category", "content");

        $response->code(200)->info("series.get")->send($series);
    }
}

/**
 * @GET{/api/series/count}
 */
class getSeriesCount extends AccessDashboard
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
            ->send(["count" => Serie::count()]);
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

class updateSerie extends AccessContentsManager
{
    public function checkers(Request $request): array
    {
        return [
            ["type/string", $request->getBody()['title_serie'], "title_serie"],
            ["serie/title", fn() => $this->floor->pickup("title_serie"), "title_serie"],
            ["type/int", $request->getParam("id"), "serie_id"],
            ["serie/exist", fn() => $this->floor->pickup("serie_id"), "serie"],
            ["serie/notexist", [
                "title" => $request->getBody()['title_serie'],
                "serie_id" => $request->getParam("id")
            ]],
            ["type/string", $request->getBody()['image'], "image"],
            ["video/image", fn() => $this->floor->pickup("image"), "image"],
            ["type/string", $request->getBody()['description'], "description"],
            ["video/description", fn() => $this->floor->pickup("description"), "description"],
            ["type/int", $request->getBody()['category_id'], "category_id"],
            ["category/exist", fn() => $this->floor->pickup("category_id"), "category"],
            ["type/string", $request->getBody()['release_date'], "release_date"]
        ];
    }

    public function handler(Request $request, Response $response): void
    {
        /** @var Serie $serie */
        $serie = $this->floor->pickup("serie");
        /** @var Category $category */
        $category = $this->floor->pickup("category");
        $serie->setTitle($this->floor->pickup("title_serie"));
        $serie->setImage($this->floor->pickup("image"));
        $serie->setDescription($this->floor->pickup("description"));
        $serie->setReleaseDate($this->floor->pickup("release_date"));
        $serie->getContent()->setCategory($category)->save();
        $serie->save();
        $response->info("serie.updated")->code(200)->send();
    }
}

/**
 * @POST{/api/episode/{serie_id}}
 * @apiName AddEpisode
 * @apiGroup ContentManager/SerieController
 * @apiVersion 1.0.0
 * @Feature ContentManager
 * @Description Add an episode to a serie
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
 "title_video": "Video title",
 "description": "Video description",
 "serie_id": 1,
 "episode": 1,
 "season": 1,
}
*/

class addEpisodeBySerie extends AccessContentsManager
{
    public function checkers(Request $request): array
    {
        $episode = $request->getBody();
        return [
            ["type/string", $episode['title_video'], "title_video"],
            ["video/title", fn() => $this->floor->pickup("title_video"), "title_video"],
            ["type/string", $episode['description'], "description"],
            ["video/description", fn() => $this->floor->pickup("description"), "description"],
            ["type/int", $request->getParam('serie_id'), "serie_id"],
            ["serie/exist", fn() => $this->floor->pickup("serie_id"), "serie"],
            ["type/int", $episode['episode'], "episode"],
            ["serie/episode", fn() => $this->floor->pickup("episode"), "episode"],
            ["type/int", $episode['season'], "season"],
            ["serie/season", fn() => $this->floor->pickup("season"), "season"],
            [
                "episode/notexist", [
                    "episode" => $episode["episode"],
                    "season" => $episode["season"],
                    "serie_id" => $request->getParam("serie_id"),
            ]
            ]
        ];
    }

    public function handler(Request $request, Response $response): void
    {
        /** @var Serie $serie */
        $serie = $this->floor->pickup("serie");

        $video = VideoManager::createVideo();

        /** @var Episode $episode */
        $episode = Episode::insertOne([
            "episode" => $this->floor->pickup("episode"),
            "season" => $this->floor->pickup("season"),
            "video_id" => $video->getId(),
            "serie_id" => $serie->getId(),
            "title" => $this->floor->pickup("title_video"),
            "description" => $this->floor->pickup("description")
        ]);
        $response->info("episode.created")->code(200)->send(["episode" => $episode, "video" => $video]);
    }
}

/**
 * @GET{/api/serie/{id}/season/{season}/episode/{episode}}
 */
class GetEpisode extends Controller
{
    public function checkers(Request $request): array
    {
        return [
            ["type/int", $request->getParam("id"), "serieId"],
            ["serie/exist", fn() => $this->floor->pickup("serieId"), "serie"],
            ["type/int", $request->getParam("episode"), "episodeNumber"],
            ["type/int", $request->getParam("season"), "seasonNumber"],
        ];
    }

    public function handler(Request $request, Response $response): void
    {
        /** @var Serie $serie */
        $serie = $this->floor->pickup("serie");
        $episodeNumber = $this->floor->pickup("episodeNumber");
        $seasonNumber = $this->floor->pickup("seasonNumber");

        $episode = Episode::findFirst([
            "serie" => $serie,
            "episode" => $episodeNumber,
            "season" => $seasonNumber,
        ]);

        if ($episode === null) $response->info("episode.notfound")->code(404)->send();
        
        Episode::groups("video", "content", "vote", "category", "urls", "serie");
        $response->code(200)->info("episode.get")->send($episode);
    }
}

/**
 * @GET{/api/serie/{id}/episodes}
 */
class GetAllSerie extends Controller
{
    public function checkers(Request $request): array
    {
        return [
            ["type/int", $request->getParam("id"), "serieId"],
            ["serie/exist", fn() => $this->floor->pickup("serieId"), "serie"],
        ];
    }

    public function handler(Request $request, Response $response): void
    {
        /** @var Serie $serie */
        $serie = $this->floor->pickup("serie");

        $response->code(200)->info("episode.get.all")->send(Episode::findMany(["serie" => $serie]));
    }
}

/**
 * @GET{/api/episode/{episode_id}}
 */
class getEpisodeById extends AccessContentsManager {

    public function checkers(Request $request): array
    {
        return [
            ["type/int", $request->getParam('episode_id'), "episode_id"],
            ["episode/exist", fn () => $this->floor->pickup('episode_id'), "episode"]
        ];
    }
    public function handler(Request $request, Response $response): void
    {
        /** @var Episode $episode */
        $episode = $this->floor->pickup("episode");

        Episode::groups("video", "content", "urls");

        $response->code(200)->info("episode.get")->send($episode);
    }
}

/**
 * @PUT{/api/episode/{episode_id}}
 */
class updateEpisodeById extends AccessContentsManager {

    public function checkers(Request $request): array
    {
        return [
            ["type/int", $request->getParam('episode_id'), "episode_id"],
            ["episode/exist", fn () => $this->floor->pickup('episode_id'), "episode"],
            ["type/int", $request->getBody()["episodeNumber"], "episodeNumber"],
            ["serie/episode", fn () => $this->floor->pickup("episodeNumber"), "episodeNumber"],
            ["type/int", $request->getBody()["seasonNumber"], "seasonNumber"],
            ["serie/season", fn () => $this->floor->pickup("seasonNumber"), "seasonNumber"],
            ["type/string", $request->getBody()["title"], "title"],
            ["type/string", $request->getBody()["description"], "description"]
        ];
    }
    public function handler(Request $request, Response $response): void
    {
        /** @var Episode $episode */
        $episode = $this->floor->pickup("episode");

        $episode->setTitle($this->floor->pickup("title"));
        $episode->setDescription($this->floor->pickup("description"));
        $episode->setEpisode($this->floor->pickup("episodeNumber"));
        $episode->setSeason($this->floor->pickup("seasonNumber"));

        $episode->save();

        $response->code(200)->info("episode.updated")->send($episode);
    }
}

/**
 * @GET{/api/serie/{serie_id}/season/{seasonNumber}/episodes}
 * @apiName GetEpisodes
 * @apiGroup ContentManager/SerieController
 * @apiVersion 1.0.0
 * @Feature ContentManager
 * @Description Get episodes of a serie
 * @param int serie_id
 * @return Response
 */
class getEpisodesSeriesBySeason extends Controller
{
    public function checkers(Request $request): array
    {
        return [
            ["type/int", $request->getParam('serie_id'), "serie_id"],
            ["serie/exist", fn() => $this->floor->pickup("serie_id"), "serie"],
            ["type/int", $request->getParam('seasonNumber'), "seasonNumber"]
        ];
    }

    public function handler(Request $request, Response $response): void
    {
        $episodes = Episode::findMany([
            "serie_id" => $this->floor->pickup("serie")->getId(),
            "season" => $this->floor->pickup("seasonNumber")
        ],
            ["ORDER_BY" => ["episode"]]);

        if (empty($episodes)) $response->code(400)->info("season.notexist")->send();

        $response->code(200)->info("episodes.get")->send($episodes);
    }
}

/**
 * @GET{/api/serie/{serie_id}/season/count}
 * @Description Get max season number of series
 * @param int serie_id
 * @return int
 */
class getSeasonCountBySeries extends Controller
{

    public function checkers(Request $request): array
    {
        return [
            ["type/int", $request->getParam('serie_id'), "serie_id"],
            ["serie/exist", fn() => $this->floor->pickup("serie_id"), "serie"]
        ];
    }

    public function handler(Request $request, Response $response): void
    {
        $sql = QueryBuilder::createSelectRequest("_episode", ["season"], ["serie_id" => $this->floor->pickup("serie")->getId()], ["GROUP_BY" => ["season"], "ORDER_BY" => ["season"]]);
        Logger::allowRequestDB();
        $seasons = $sql->fetchAll(\PDO::FETCH_ASSOC);

        $response->code(200)->info("season.count")->send($seasons);
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
class deleteEpisode extends AccessContentsManager
{
    public function checkers(Request $request): array
    {
        return [
            ["type/int", $request->getParam('id'), "episode_id"],
            ["episode/exist", fn() => $this->floor->pickup("episode_id"), "episode"]
        ];
    }

    public function handler(Request $request, Response $response): void
    {
        /** @var Episode $episode */
        $episode = $this->floor->pickup("episode");
        $episode->delete();
        $response->info("episode.deleted")->code(204)->send();
    }
}

/**
 * @PUT{/api/episode/{serie_id}}
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

class updateEpisodeInfo extends AccessContentsManager
{
    public function checkers(Request $request): array
    {
        return [
            ["type/int", $request->getParam('serie_id'), "serie_id"],
            ["serie/exist", fn() => $this->floor->pickup("serie_id"), "serie"],
            ["type/string", $request->getBody()['title_video'], "title_video"],
            ["video/title", fn() => $this->floor->pickup("title_video"), "title_video"],
            ["type/string", $request->getBody()['description'], "description"],
            ["video/description", fn() => $this->floor->pickup("description"), "description"],
            ["type/int", $request->getBody()['episode'], "episode"],
            ["serie/episode", fn() => $this->floor->pickup("episode"), "episode"],
            ["episode/exist", fn() => $this->floor->pickup("episode"), "episode"],
            ["type/int", $request->getBody()['season'], "season"],
            ["serie/season", fn() => $this->floor->pickup("season"), "season"]
        ];
    }

    public function handler(Request $request, Response $response): void
    {
        /** @var Episode $episode */
        $episode = $this->floor->pickup("episode");

        $episode->setTitle($this->floor->pickup("title_video"));
        $episode->setDescription($this->floor->pickup("description"));
        $episode->setSeason($this->floor->pickup("episode"));
        $episode->setEpisode($this->floor->pickup("season"));

        $response->info("episode.updated")->code(200)->send();
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

class updateEpisode extends AccessContentsManager
{
    public function checkers(Request $request): array
    {
        return [
            ["type/int", $request->getParam('id'), "episode_id"],
            ["episode/exist", fn() => $this->floor->pickup("episode_id"), "episode"],
            ["type/int", $request->getBody()['season'], "season"],
            ["type/int", $request->getBody()['episode_order'], "episode_nb"],
            ["serie/episode", fn() => $this->floor->pickup("episode_nb"), "episode_nb"],
            ["serie/season", fn() => $this->floor->pickup("season"), "season"],
            [
                "episode/notexist", [
                fn() => $this->floor->pickup("episode")->getSerie()->getId(),
                fn() => $this->floor->pickup("episode_nb"),
                fn() => $this->floor->pickup("season")
            ]
            ]
        ];
    }

    public function handler(Request $request, Response $response): void
    {
        /** @var Episode $episode */
        $episode = $this->floor->pickup("episode");
        $episode->setSeason($this->floor->pickup("season"));
        $episode->setEpisode($this->floor->pickup("episode_nb"));
        $episode->save();
        $response->info("episode.updated")->code(200)->send();
    }
}

class getSerieByName extends Controller
{
    public function checkers(Request $request): array
    {
        return [
            ["type/string", $request->getParam('serie_title'), "serie_title"],
        ];
    }

    public function handler(Request $request, Response $response): void
    {
        /** @var Serie $serie */
        $serie = Serie::findFirst([
            "title" => $this->floor->pickup("serie_title")
        ]);
        if (empty($serie)) $response->info("serie.notfound")->code(404)->send();
        $response->code(200)->info("serie.get")->send(["serie" => $serie]);
    }
}
