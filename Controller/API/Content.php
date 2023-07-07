<?php

namespace Controller\API\Content;

use Core\Controller;
use Core\Logger;
use Core\QueryBuilder;
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
        $number = ($number > 40 ? 40 : $number);
        $number = ($number < 1 ? 5 : $number);
        return [
            ["type/int", $number, "number", "content.get.badNumber"],
            ["type/string", $request->getQuery("type") ?? "", "type", "content.get.badType"],
            ["type/int", $request->getQuery("category_id") ?? -1, "category_id"],
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

/**
 * @GET{/api/content/top}
 */
class GetTopContent extends Controller
{
    public function checkers(Request $request): array
    {
        $number = $request->getQuery("number") ?? 5;
        $number = (($number > 40 || $number < 1) ? null : $number);
        return [
            ["type/int", $number, "number", "content.get.badNumber"],
            ["type/string", $request->getQuery("type") ?? "", "type", "content.get.badType"],
            ["type/int", $request->getQuery("category_id") ?? -1, "category_id"],
        ];
    }

    public function handler(Request $request, Response $response): void
    {  
        $dataBase = QueryBuilder::getDb();
        $number = $this->floor->pickup("number");
        $type = $this->floor->pickup("type");
        $category_id = $this->floor->pickup("category_id");

        $requestString = "SELECT SUM(_vote.value), _content.id from _content JOIN _vote ON _vote.content_id = _content.id";

        $where = [];
        if($type === "serie") $where[] = "_content.value_type = 'S'";
        else if($type === "movie") $where[] = "_content.value_type = 'M'";
        if($category_id !== -1) $where[] = "_content.category_id = ?";

        $requestString .= (count($where) !== 0 ? " WHERE " . implode(" AND ", $where) : "") . " GROUP BY _content.id ORDER BY SUM DESC limit ?";
        
        $request = $dataBase->prepare($requestString);
        $request->execute($category_id !== -1 ? [$category_id, $number] : [$number]);
        $result = $request->fetchAll(\PDO::FETCH_ASSOC);

        $contents = [];
        foreach ($result as $value) {
            $contents[] = Content::findFirst(["id" => $value["id"]]);
        }

        Content::groups("value", "vote", "category");
        $response->code(200)->info("content.get")->send($contents);
    }
}

/**
 * @GET{/api/content/last}
 */
class GetLastContent extends Controller
{
    public function checkers(Request $request): array
    {
        $number = $request->getQuery("number") ?? 5;
        $number = (($number > 40 || $number < 1) ? null : $number);
        return [
            ["type/int", $number, "number", "content.get.badNumber"],
            ["type/string", $request->getQuery("type") ?? "", "type", "content.get.badType"],
            ["type/int", $request->getQuery("category_id") ?? -1, "category_id"],
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

        Content::groups("value", "vote", "category", "dateProps");
        $response->code(200)->info("content.get")->send(Content::findMany($where, ["ORDER_BY" => ["created_at DESC"], "LIMIT" => $number]));
    }
}