<?php

namespace controller\API\ContentManager\SerieController;

use Core\Controller;
use Core\Request;
use Core\Response;
use Entity\Serie;
use Services\Back\VideoManagerService as VideoManager;

// Valid
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
                $request->getBody()['category']
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
// Valid
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
// Valid
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
// Valid
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
// Valid
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
// Valid
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

class deleteEpisodeWhereSerie extends Controller
{
    public function checkers(Request $request): array
    {
        return [
            ["serie/title", $request->getQuery('name')],
            ["serie/episode", $request->getQuery('episode')],
        ];
    }

    public function handler(Request $request, Response $response): void
    {
        try {
            $serie = Serie::findFirst([
                "title" => $this->floor->pickup("serie/title"),
                "episode" => $this->floor->pickup("serie/episode")
            ]);
            $serie->delete();
            $response->send(["message" => "Episode deleted"]);
        } catch (\Exception $e) {
            $response->send(["error" => $e->getMessage()]);
        }
    }
}

class updateEpisodeInfo extends Controller
{
    public function checkers(Request $request): array
    {
        return [
            ["serie/title", $request->getQuery('name')],
            ["serie/episode", $request->getQuery('episode')],
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

            $serie = Serie::findFirst([
                "title" => $this->floor->pickup("serie/title"),
                "episode" => $this->floor->pickup("serie/episode")
            ]);

            $videoManager->updateVideo(
                $serie->getVideo()->getId(),
                $this->floor->pickup("video/title"),
                $this->floor->pickup("video/description"),
                $request->getBody()['category']
            );

            $response->send(["message" => "Episode updated"]);
        } catch (\Exception $e) {
            $response->send(["error" => $e->getMessage()]);
        }
    }
}

class updateUrlWhereEpisode extends Controller
{
    public function checkers(Request $request): array
    {
        return [
            ["serie/title", $request->getQuery('name')],
            ["serie/episode", $request->getQuery('episode')],
            ["video/url", $request->getBody()['url']],
        ];
    }

    public function handler(Request $request, Response $response): void
    {
        try {
            $serie = Serie::findFirst([
                "title" => $this->floor->pickup("serie/title"),
                "episode" => $this->floor->pickup("serie/episode")
            ]);
            $urls = $serie->getVideo()->getUrls();
            foreach ($urls as $url) {
                $url->setUrl($this->floor->pickup("video/url"));
                $url->save();
            }

            $response->send(["message" => "Episode updated"]);
        } catch (\Exception $e) {
            $response->send(["error" => $e->getMessage()]);
        }
    }
}
