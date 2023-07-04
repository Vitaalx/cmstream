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
        $number = $request->getQuery("number") ?? 0;
        $number = (($number > 40 || $number < 1) ? null : $number);
        return [
            ["type/int", $number, "number", "content.get.badNumber"],
            ["type/string", $request->getQuery("type") ?? "", "type", "content.get.badType"],
        ];
    }

    public function handler(Request $request, Response $response): void
    {  
        Content::groups("value", "votes");
        $number = $this->floor->pickup("number");
        $type = $this->floor->pickup("type");
        if($type === "serie"){
            $response->code(200)->info("content.get")->send(Content::findMany(["value_type" => "S"], ["ORDER_BY" => ["RANDOM()"], "LIMIT" => $number]));
        }
        else if($type === "movie"){
            $response->code(200)->info("content.get")->send(Content::findMany(["value_type" => "M"], ["ORDER_BY" => ["RANDOM()"], "LIMIT" => $number]));
        }
        else {
            $response->code(200)->info("content.get")->send(Content::findMany([], ["ORDER_BY" => ["RANDOM()"], "LIMIT" => $number]));
        }
    }
}