<?php

namespace Controller\API\ContentManager\HistoryController;

use Core\Request;
use Core\Response;

use Entity\History;

use Entity\User;
use Entity\Video;
use Services\Access\AccessContentsManager;
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
    "value_id" : 1,
    "value_type": "E"
}
*/

class addHistory extends MustBeConnected
{
    public function checkers(Request $request): array
    {
        return [
            ["type/int", $request->getBody()['value_id'], "value_id"],
            ["type/string", $request->getBody()['value_type'], "value_type"],
            ["history/valueType", $request->getBody()['value_type'], "value_type"],
            ["history/videoExist", [
                "id" => $request->getBody()['value_id'],
                "type" => $request->getBody()['value_type']
            ], "video"]
        ];
    }

    public function handler(Request $request, Response $response): void
    {
        /** @var Video $video */
        $video = $this->floor->pickup("video");

        $historyExist = History::findFirst([
            "user_id" => $this->floor->pickup("user")->getId(),
            "value_id" => $video->getId(),
            "value_type" => $this->floor->pickup("value_type")
        ]);

        if ($historyExist !== null) {
            $historyExist->setTimeStamp(time());
            $historyExist->save();
        } else {
            /** @var History $history */
            $history = History::insertOne(
                fn(History $history) => $history
                    ->setValue($video)
                    ->setUser($this->floor->pickup("user"))
            );
        }

        $response->code(201)->info("history.added")->send();
    }
}

/**
 * @GET{/api/history}
 * @Description Get the history of the user
 * @return Response
 */
class getHistoryByUserId extends MustBeConnected
{
    public function checkers(Request $request): array
    {
        return [
            ["type/int", $request->getQuery("page") ?? 0, "page", "content.page.not.number"],
        ];
    }
    public function handler(Request $request, Response $response): void
    {
        $page = $this->floor->pickup("page");
        /** @var User $user */
        $user = $this->floor->pickup("user");
        $history = History::findMany(
            [
                "user_id" => $user->getId()
            ],
            [
                "ORDER_BY" => ["timestamp DESC"],
                "OFFSET" => $page * 10,
                "LIMIT" => 10,
            ]
        );

        History::groups("HistoryValue", "contentValue", "serie");

        $response->code(200)->info("history.get")->send($history);
    }
}

/**
 * @DELETE{/api/histories}
 * @Description Delete a history of user
 * @param int id
 * @return Response
 */
class deleteAllHistory extends MustBeConnected
{
    public function checkers(Request $request): array
    {
        return [
            ["history/existByUser", fn() => $this->floor->pickup("user")->getId(), "histories"]
        ];
    }

    public function handler(Request $request, Response $response): void
    {
        /** @var History $history */
        $histories = $this->floor->pickup("histories");
        foreach ($histories as $history) {
            $history->delete();
        }
        $response->code(200)->info("histories.deleted")->send();
    }
}

/**
 * @DELETE{/api/history/{id}}
 * @Description Delete a history of user
 * @param int id
 * @return Response
 */
class deleteHistoryById extends MustBeConnected
{
    public function checkers(Request $request): array
    {
        return [
            ["type/int", $request->getParam('id'), "history_id"],
            ["history/exist", fn() => $this->floor->pickup("history_id"), "history"]
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