<?php

namespace Controller\API\ContentManager\PlaylistController;

use Core\Controller;
use Core\Request;
use Core\Response;

use Entity\Watchlist;
use Services\MustBeAdmin;
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
 "movie_id": 1
 "serie_id": null
}
*/
class addWishWatchlist extends MustBeConnected
{
    public function checkers(Request $request): array
    {
        return [
            ["type/int", $request->getBody()['movie_id'], "movie_id"],
            ["type/int", $request->getBody()['series_id'], "series_id"],
            ["movie/exist", fn () => $this->floor->pickup("movie_id"), "movie"],
            ["series/exist", fn () => $this->floor->pickup("serie_id"), "serie"],
            [
                "watchlist/notexist", [
                    "user" => fn () => $this->floor->pickup("user")->getId(),
                    "movie" => fn () => $this->floor->pickup("movie")->getId(),
                    "serie" => fn () => $this->floor->pickup("serie")->getId(),
                ]
            ]
        ];
    }

    public function handler(Request $request, Response $response): void
    {
        /** @var Watchlist $wish */
        $wish = Watchlist::insertOne([
            "user_id" => $this->floor->pickup("user")->getId(),
            "movie_id" => $this->floor->pickup("movie")->getId(),
            "series_id" => $this->floor->pickup("serie")->getId()
        ]);
        $response->info("wish.created")->code(201)->send(["wish" => $wish]);
    }
}

/**
 * @DELETE{/api/watchlist/{user_id}}
 * @apiName DeleteWatchlist
 * @apiGroup ContentManager/WatchlistController
 * @apiVersion 1.0.0
 * @Feature WatchList
 * @Description Delete a watchlist
 * @param int user_id
 * @return Response
 */
class deleteWatchlistAdmin extends MustBeAdmin
{
    public function checkers(Request $request): array
    {
        return [
            ["type/int", $request->getParam("user_id"), "user_id"],
            ["user/exist", fn () => $this->floor->pickup("user_id"), "user"],
        ];
    }

    public function handler(Request $request, Response $response): void
    {
        /** @var Watchlist $watchlist */
        $watchlist = Watchlist::findMany(["user_id" => $this->floor->pickup("user")->getId()]);
        if (empty($watchlist)) $response->info("watchlist.notfound")->code(404)->send();
        foreach ($watchlist as $wish) {
            $wish->delete();
        }
        $response->info("watchlist.deleted")->code(200)->send();
    }
}

/**
 * @DELETE{/api/watchlist}
 */

class deleteWatchlist extends MustBeConnected
{
    public function handler(Request $request, Response $response): void
    {
        /** @var Watchlist $watchlist */
        $watchlist = Watchlist::findMany(["user_id" => $this->floor->pickup("user")->getId()]);
        if (empty($watchlist)) $response->info("watchlist.notfound")->code(404)->send();
        foreach ($watchlist as $wish) {
            $wish->delete();
        }
        $response->info("watchlist.deleted")->code(200)->send();
    }
}

/**
 * @DELETE{/api/watchlist/wish/{id}}
 * @apiName DeleteWatchlist
 * @apiGroup ContentManager/WatchlistController
 * @apiVersion 1.0.0
 * @Feature WatchList
 * @Description Delete a watchlist
 * @param int id
 * @return Response
 */
class deleteWishWatchlistAdmin extends MustBeAdmin
{
    public function checkers(Request $request): array
    {
        return [
            ["type/int", $request->getParam("id"), "watch_id"],
            ["watchlist/exist", fn () => $this->floor->pickup("watch_id"), "watchlist"],
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

/**
 * @DELETE{/api/watchlist/yourwish/{id}}
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
            ["type/int", $request->getParam("id"), "watch_id"],
            ["watchlist/isowner", [
                "user_id" => fn () => $this->floor->pickup("user")->getId(),
                "watchlist_id" => fn () => $this->floor->pickup("watch_id")
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
