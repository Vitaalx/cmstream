<?php

namespace Controller\API\ContentManager\WatchlistController;

use Core\Controller;
use Core\Request;
use Core\Response;

use Entity\Content;
use Entity\User;
use Entity\Watchlist;
use Services\Access\AccessContentsManager;
use Services\MustBeConnected;

/**
 * @POST{/api/watchlist/wish}
 * @apiName AddWichWatchlist
 * @apiGroup ContentManager/WatchlistController
 * @apiVersion 1.0.0
 * @Feature WatchList
 * @Description Add wish to a watchlist.
 * @param int video_id
 * @return Response
 */
/*
Entry:
{
 "content_id": 1
}
*/
class addWishWatchlist extends MustBeConnected
{
    public function checkers(Request $request): array
    {
        return [
            ["type/int", $request->getBody()['content_id'], "content_id"],
            ["content/exist", fn () => $this->floor->pickup("content_id"), "content"],
            ["watchlist/notexist", [
                    "user_id" => $this->floor->pickup("user")->getId(),
                    "content_id" => $request->getBody()['content_id']
            ]]
        ];
    }

    public function handler(Request $request, Response $response): void
    {
        /** @var Content $content */
        $content = $this->floor->pickup("content");
        /** @var User $user */
        $user = $this->floor->pickup("user");

        /** @var Watchlist $wish */
        $wish = Watchlist::insertOne([
            "user_id" => $user->getId(),
            "content_id" => $content->getId(),
        ]);
        $response->info("wish.created")->code(201)->send($wish);
    }
}

/**
 * @GET{/api/watchlist}
 */
/*
Entry:
{
 "content_id": 1
}
*/
class getWatchlistByUserId extends MustBeConnected
{
    public function checkers(Request $request): array
    {
        return [
            ["watchlist/existByUser", fn () => $this->floor->pickup("user")->getId(), "watchlist"]
        ];
    }

    public function handler(Request $request, Response $response): void
    {
        /** @var Watchlist $watchlist */
        $watchlist = $this->floor->pickup("watchlist");

        Watchlist::groups("value", "watchlistContent");

        $response->info("wish.created")->code(201)->send($watchlist);
    }
}

/**
 * @DELETE{/api/watchlist/wish/{content_id}}
 * @apiName DeleteWatchlist
 * @apiGroup ContentManager/WatchlistController
 * @apiVersion 1.0.0
 * @Feature WatchList
 * @Description Delete a watchlist
 * @param int id
 * @return Response
 */
class deleteWishWatchlist extends MustBeConnected
{
    public function checkers(Request $request): array
    {
        return [
            ["type/int", $request->getParam("content_id"), "content_id"],
            ["watchlist/isOwner", [
                "user_id" => $this->floor->pickup("user")->getId(),
                "content_id" => $request->getParam("content_id")
            ], "watchlist"]
        ];
    }

    public function handler(Request $request, Response $response): void
    {
        /** @var Watchlist $watchlist */
        $watchlist = $this->floor->pickup("watchlist");
        $watchlist->delete();
        $response->info("watchlist.deleted")->code(204)->send();
    }
}
