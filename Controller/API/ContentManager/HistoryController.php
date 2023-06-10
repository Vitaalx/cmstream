<?php

namespace Controller\API\ContentManager;

use Core\Controller;
use Core\Request;
use Core\Response;

use Entity\History;

use Services\MustBeAdmin;
use Services\MustBeConnected;

/**
 * @POST{/api/history}
 * @Body Json Resquest
 * @Description Add a video to the history of the user
 * @param int video_id
 * @return Response
 */
/*
Entry:
{
    "video_id" : 1
}
*/
class addHistory extends MustBeConnected
{
    public function checkers(Request $request): array
    {
        return [
            ["type/int", $request->getBody()['video_id'], "video_id"],
            ["video/exist", fn () => $this->floor->pickup("video_id"), "video"]
        ];
    }

    public function handler(Request $request, Response $response): void
    {
        /** @var History $history */
        $history = History::insertOne(
            fn (History $history) => $history
                ->setVideo($this->floor->pickup("video"))
                ->setUser($this->floor->pickup("user"))
        );

        $response->code(201)->info("history.added")->send();
    }
}

/**
 * @GET{/api/history}
 * @Description Get the history of the user
 * @return Response
 */
class getHistory extends MustBeConnected
{
    public function handler(Request $request, Response $response): void
    {
        /** @var History $history */
        $history = History::findMany([
            "user_id" => $this->floor->pickup("user")->getId()
        ]);

        $response->code(200)->info("history.found")->send([
            "history" => $history
        ]);
    }
}

/**
 * @DELETE{/api/history/{id}}
 * @Description Delete a history of user
 * @param int id
 * @return Response
 */
class deleteHistory extends MustBeAdmin
{
    public function checkers(Request $request): array
    {
        return [
            ["type/int", $request->getParams()['id'], "user_id"],
            ["history/exist", fn () => $this->floor->pickup("user_id"), "history"]
        ];
    }

    public function handler(Request $request, Response $response): void
    {
        /** @var History $history */
        $history = $this->floor->pickup("history");
        $history->delete();

        $response->code(200)->info("history.deleted")->send();
    }
}

/**
 * @GET{/api/history/{id}}
 * @Description get history of user
 * @return Response
 */
class getHistoryById extends MustBeAdmin
{
    public function checkers(Request $request): array
    {
        return [
            ["type/int", $request->getParams()['id'], "user_id"],
            ["history/exist", fn () => $this->floor->pickup("user_id"), "history"]
        ];
    }

    public function handler(Request $request, Response $response): void
    {
        /** @var History $history */
        $history = $this->floor->pickup("history");

        $response->code(200)->info("history.found")->send([
            "history" => $history
        ]);
    }
}
