<?php

namespace Controller\API\ContentManager\Pages;

use Core\File;
use Core\Request;
use Core\Response;
use Entity\Page_history;
use Services\Access\AccessContentsManager;

/**
 * @PUT{/api/pages}
 */
class SetPages extends AccessContentsManager
{
    public function handler(Request $request, Response $response): void
    {
        $file = new File(__DIR__ . "/../../../public/cuteVue/pages.json");
        Page_history::insertOne(fn (Page_history $ph) => $ph->setValue($file->read()));
        $file->write($request->getBody());
        $response->code(204)->info("pages.set")->send();
    }
}

/**
 * @GET{/api/page-history}
 */
class GetPageHistory extends AccessContentsManager
{
    public function checkers(Request $request): array
    {
        return [
            ["type/int", $request->getQuery("page") ?? 0, "page"],
        ];
    }

    public function handler(Request $request, Response $response): void
    {
        $page = $this->floor->pickup("page");
        $response->code(200)->info("page-history.get")->send(Page_history::findMany(
            [],
            [
                "ORDER_BY" => ["timestamp DESC"],
                "OFFSET" => 10 * $page,
                "LIMIT" => 10,
            ]
        ));
    }
}

/**
 * @GET{/api/page-history/count}
 */
class CountPageHistory extends AccessContentsManager
{
    public function handler(Request $request, Response $response): void
    {
        $response->code(200)->info("page-history.count")->send(Page_history::count());
    }
}

/**
 * @PUT{/api/pages/page-history/{id}}
 */
class previousPage extends AccessContentsManager
{
    public function checkers(Request $request): array
    {
        return [
            ["type/int", $request->getParam("id") ?? 0, "id"],
            ["Page_history/exist", fn () => $this->floor->pickup("id"), "page_history"]
        ];
    }

    public function handler(Request $request, Response $response): void
    {
        /** @var Page_history $page_history */
        $page_history = $this->floor->pickup("page_history");

        $file = new File(__DIR__ . "/../../../public/cuteVue/pages.json");
        $file->write($page_history->getValue());
        $response->code(204)->info("pages.set")->send();
    }
}