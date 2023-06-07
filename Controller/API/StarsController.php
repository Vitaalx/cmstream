<?php

namespace Controller\API\StarsController;

use Core\Controller;
use Core\Request;
use Core\Response;

use Entity\Star;
use Entity\Video;
use Services\MustBeAdmin;
use Services\MustBeConnected;

/**
 * @POST{/api/star/{id}}
 * @apiName CreateStar
 * @apiGroup Star
 * @apiVersion 1.0.0
 * @Feature Stars
 * @Description Create a star
 * @param int note
 * @param int video
 * @param int user
 * @return Response
 */
/*
Entry:
{
    "note": 5,
}
*/
class createStar extends MustBeConnected
{
    public function checkers(Request $request): array
    {
        return [
            ["type/int", $request->getBody()["note"], "note"],
            ["type/int", $request->getParam("id"), "video_id"],
            ["video/exist", fn () => $this->floor->pickup("video_id"), "video"],
            [
                "star/notexist", [
                    "video_id" => fn () => $this->floor->pickup("video")->getId(),
                    "user_id" => fn () => $this->floor->pickup("user")->getId()
                ]
            ],
            ["star/note", $this->floor->pickup("note"), "note"]
        ];
    }

    public function handler(Request $request, Response $response): void
    {
        Star::insertOne([
            "note" => $this->floor->pickup("note"),
            "video_id" => $this->floor->pickup("video")->getId(),
            "user_id" => $this->floor->pickup("user")->getId()
        ]);

        $response->info("star.created")->code(201)->send();
    }
}

/**
 * @GET{/api/stars/average/{video_id}}
 * @apiName ReadStar
 * @apiGroup Star
 * @apiVersion 1.0.0
 * @Feature Stars
 * @Description Read a star
 * @param int video_id
 * @return Response
 */
class starAverage extends Controller
{
    public function checkers(Request $request): array
    {
        return [
            ["type/int", $request->getParam("video_id"), "video_id"],
            ["video/exist", fn () => $this->floor->pickup("video_id"), "video"]
        ];
    }

    public function handler(Request $request, Response $response): void
    {
        $avg = $this->floor->pickup("video")->AvgStars();
        $response->send(["average" => $avg]);
    }
}

/**
 * @DELETE{/api/star/{id}}
 * @apiName DeleteStar
 * @apiGroup Star
 * @apiVersion 1.0.0
 * @Feature Stars
 * @Description Delete a star
 * @param int video_id
 * @param int user_id
 * @return Response
 */
class deleteStarAdmin extends MustBeAdmin
{
    public function checkers(Request $request): array
    {
        return [
            ["type/int", $request->getParam("id"), "star_id"],
            ["star/existId", fn () => $this->floor->pickup("id"), "star"],
        ];
    }

    public function handler(Request $request, Response $response): void
    {
        $this->floor->pickup("star")->delete();
        $response->info("star.deleted")->code(200)->send();
    }
}

/**
 * @GET{/api/stars}
 * @apiName ReadAllStar
 * @apiGroup Star
 * @apiVersion 1.0.0
 * @Feature Stars
 * @Description Read all stars
 * @return Response
 */
class getAllAverageStarsWhereVideos extends Controller
{
    public function checkers(Request $request): array
    {
        return [];
    }

    public function handler(Request $request, Response $response): void
    {
        $videos = Video::findMany();
        $stars = [];
        foreach ($videos as $video) {
            $stars[] = [
                "video" => $video,
                "stars" => $video->AvgStars()
            ];
        }
        asort($stars);
        $response->send(["stars" => $stars]);
    }
}

/**
 * @GET{/api/star/{id}}
 * @apiName ReadStar
 * @apiGroup Star
 * @apiVersion 1.0.0
 * @Feature Stars
 * @Description Read a star
 * @param int video_id
 * @return Response
 */
class getYourstar extends MustBeConnected
{
    public function checkers(Request $request): array
    {
        return [
            ["type/int", $request->getParam("id"), "video_id"],
            ["video/exist", fn () => $this->floor->pickup("video_id"), "video"],
            ["star/exist", [
                "video_id" => $this->floor->pickup("video")->getId(),
                "user_id" => $this->floor->pickup("user")->getId()
            ], "star"]
        ];
    }

    public function handler(Request $request, Response $response): void
    {
        $response->send(["star" => $this->floor->pickup("star")]);
    }
}

/**
 * @PUT{/api/star/{id}}
 * @apiName UpdateStar
 * @apiGroup Star
 * @apiVersion 1.0.0
 * @Feature Stars
 * @Description Update a star
 * @param int note
 * @param int video_id
 * @param int user_id
 * @return Response
 */
/*
Entry:
{
    "note": 5,
}
*/
class updateStar extends MustBeConnected
{
    public function checkers(Request $request): array
    {
        return [
            ["type/int", $request->getBody()["note"], "note"],
            ["type/int", $request->getParam("id"), "video_id"],
            ["video/exist", fn () => $this->floor->pickup("video_id"), "video"],
            ["star/exist", [
                "video_id" => $this->floor->pickup("video")->getId(),
                "user_id" => $this->floor->pickup("user")->getId()
            ], "star"],
            ["star/note", fn () => $this->floor->pickup("note"), "note"]
        ];
    }

    public function handler(Request $request, Response $response): void
    {
        $this->floor->pickup("star")->setNote(
            $this->floor->pickup("note")
        )->save();
        $response->info("star.updated")->code(200)->send();
    }
}
