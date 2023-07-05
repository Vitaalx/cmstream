<?php

namespace Controller\API\Content;

use Core\Controller;
use Core\Logger;
use Core\Request;
use Core\Response;
use Entity\Content;

/**
 * @GET{/api/content/discover}
 */
class GetDiscoverContent extends Controller
{
    public function checkers(Request $request): array
    {
        $number = $request->getQuery("number") ?? 5;
        $number = (($number > 40 || $number < 1) ? null : $number);
        return [
            ["type/int", $number, "number", "content.get.badNumber"],
            ["type/string", $request->getQuery("type") ?? "", "type", "content.get.badType"],
            ["type/int", $request->getQuery("type") ?? -1, "category_id"],
        ];
    }

    public function handler(Request $request, Response $response): void
    {  
        $number = $this->floor->pickup("number");
        $type = $this->floor->pickup("type");
        $category_id = $this->floor->pickup("category_id");

        if($type === "serie") $where = ["value_type" => "S"];
        else if($type === "movie") $where = ["value_type" => "M"];
        else $where = [];
        if($category_id !== -1) $where["category_id"] = $category_id;

        Content::groups("value", "vote", "category");
        $response->code(200)->info("content.get")->send(Content::findMany($where, ["ORDER_BY" => ["RANDOM()"], "LIMIT" => $number]));
    }
}