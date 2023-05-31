<?php

namespace controller\API\StarsController;

use Core\Controller;
use Core\Request;
use Core\Response;

use Entity\Star;
use Entity\Video;

/**
 * @api {post} /api/stars/create
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
    "video_id": 1,
    "user_id": 1
}
*/
class createStar extends Controller
{
    public function checkers(Request $request): array
    {
        return [
            ["type/int", $request->getBody()["note"], "note"],
            ["type/int", $request->getBody()["video_id"], "video_id"],
            ["type/int", $request->getBody()["user_id"], "user_id"],
            ["video/exist", fn () => $this->floor->pickup("video_id"), "video"],
            ["user/exist", fn () => $this->floor->pickup("user_id"), "user"]
        ];
    }

    public function handler(Request $request, Response $response): void
    {
        $star = Star::findFirst([
            "video_id" => $this->floor->pickup("video")->getId(),
            "user_id" => $this->floor->pickup("user")->getId()
        ]);
        if ($star !== null) {
            $response->info("star.exist")->code(400)->send();
        }
        Star::insertOne([
            "note" => $this->floor->pickup("note"),
            "video_id" => $this->floor->pickup("video")->getId(),
            "user_id" => $this->floor->pickup("user")->getId()
        ]);

        $response->info("star.created")->code(201)->send();
    }
}

/**
 * @api {get} /api/stars/average
 * @apiName ReadStar
 * @apiGroup Star
 * @apiVersion 1.0.0
 * @Feature Stars
 * @Description Read a star
 * @param int video_id
 * @return Response
 */
/*
Entry:
{
    "video_id": 1
}
*/
class starAverage extends Controller
{
    public function checkers(Request $request): array
    {
        return [
            ["type/int", $request->getBody()["video_id"], "video_id"],
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
 * @api {get} /api/stars/delete
 * @apiName DeleteStar
 * @apiGroup Star
 * @apiVersion 1.0.0
 * @Feature Stars
 * @Description Delete a star
 * @param int video_id
 * @param int user_id
 * @return Response
 */
/*
Entry:
{
    "video_id": 1,
    "user_id": 1
}
*/
class deleteStar extends Controller
{
    public function checkers(Request $request): array
    {
        return [
            ["type/int", $request->getBody()["video_id"], "video_id"],
            ["type/int", $request->getBody()["User_id"], "user_id"],
            ["video/exist", fn () => $this->floor->pickup("video_id"), "video"],
            ["user/exist", fn () => $this->floor->pickup("user_id"), "user"]
        ];
    }

    public function handler(Request $request, Response $response): void
    {
        $star = Star::findFirst([
            "video_id" => $this->floor->pickup("video")->getId(),
            "user_id" => $this->floor->pickup("user")->getId()
        ]);
        if ($star === null) {
            $response->info("star.notexist")->code(400)->send();
        }
        $star->delete();
        $response->info("star.deleted")->code(200)->send();
    }
}

/**
 * @api {get} /api/stars/get-all-average
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
 * @api {get} /api/stars/get
 * @apiName ReadStar
 * @apiGroup Star
 * @apiVersion 1.0.0
 * @Feature Stars
 * @Description Read a star
 * @param int video_id
 * @param int user_id
 * @return Response
 */
/*
Entry:
{
    "video_id": 1,
    "user_id": 1
}
*/
class getStar extends Controller
{
    public function checkers(Request $request): array
    {
        return [
            ["type/int", $request->getBody()["video_id"], "video_id"],
            ["type/int", $request->getBody()["user_id"], "user_id"],
            ["video/exist", fn () => $this->floor->pickup("video_id"), "video"],
            ["user/exist", fn () => $this->floor->pickup("user_id"), "user"]
        ];
    }

    public function handler(Request $request, Response $response): void
    {
        $star = Star::findFirst([
            "video_id" => $this->floor->pickup("video")->getId(),
            "user_id" => $this->floor->pickup("user")->getId()
        ]);
        if ($star === null) {
            $response->info("star.notexist")->code(400)->send();
        }
        $response->send(["star" => $star->getNote()]);
    }
}

/**
 * @api {put} /api/stars/update
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
    "video_id": 1,
    "user_id": 1
}
*/
class updateStar extends Controller
{
    public function checkers(Request $request): array
    {
        return [
            ["type/int", $request->getBody()["note"], "note"],
            ["type/int", $request->getBody()["video_id"], "video_id"],
            ["type/int", $request->getBody()["user_id"], "user_id"],
            ["video/exist", fn () => $this->floor->pickup("video_id"), "video"],
            ["user/exist", fn () => $this->floor->pickup("user_id"), "user"]
        ];
    }

    public function handler(Request $request, Response $response): void
    {
        $star = Star::findFirst([
            "video_id" => $this->floor->pickup("video")->getId(),
            "user_id" => $this->floor->pickup("user")->getId()
        ]);
        if ($star === null) {
            $response->info("star.notexist")->code(400)->send();
        }
        $star->setNote($this->floor->pickup("note"));
        $star->save();
        $response->info("star.updated")->code(200)->send();
    }
}
