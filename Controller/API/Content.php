<?php

namespace Controller\API\Content;

use Core\Controller;
use Core\Logger;
use Core\QueryBuilder;
use Core\Request;
use Core\Response;
use Entity\Content;
use Entity\User;
use Entity\Vote;
use Services\MustBeConnected;

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

        $where = [];
        if($type === "serie") $where["c.value_type"] = "S";
        else if($type === "movie") $where["c.value_type"] = "M";
        if($category_id !== -1) $where["c.category_id"] = $category_id;

        $request = QueryBuilder::createSelectRequest(
            "_content as c", 
            ["SUM(v.value)", "c.id"],
            $where,
            [
                "GROUP_BY" => ["c.id"],
                "ORDER_BY" => ["SUM DESC"],
                "LIMIT" => $number,
            ],
            [
                "JOIN" => [
                    [
                        "TABLE" => "_vote as v",
                        "WHERE" => ["v.content_id" => ["c.id"]],
                    ]
                ]
            ]
        );

        $contents = [];
        foreach ($request->fetchAll(\PDO::FETCH_ASSOC) as $value) {
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

/**
 * @GET{/api/contents}
 */
class GetContents extends Controller
{
    public function checkers(Request $request): array
    {
        return [
            ["type/int", $request->getQuery("page") ?? 0, "page", "content.page.not.number"],
            ["type/string", $request->getQuery("type") ?? "", "type", "content.get.badType"],
            ["type/int", $request->getQuery("category_id") ?? -1, "category_id", "content.category_id.not.number"],
            ["type/string", $request->getQuery("title") ?? "", "title", "content.title.not.title"],
        ];
    }

    public function handler(Request $request, Response $response): void
    {  
        $page = $this->floor->pickup("page");
        $type = $this->floor->pickup("type");
        $category_id = $this->floor->pickup("category_id");
        $title = $this->floor->pickup("title");
        
        $where = [];
        $joins = [];

        if($type === "movie"){
            $where["c.value_type"] = "M";
            if($title !== "") $where["LOWER(m.title)"] = ["\$CTN" => $title];
            if($category_id !== -1) $where["category_id"] = $category_id;
            $joins[] = [
                "TABLE" => "_movie as m",
                "WHERE" => [
                    "c.value_type" => "M",
                    "c.value_id" => ["m.id"],
                ]
            ];
        }
        else if($type === "serie"){
            $where["c.value_type"] = "S";
            if($title !== "") $where["LOWER(s.title)"] = ["\$CTN" => $title];
            if($category_id !== -1) $where["category_id"] = $category_id;
            $joins[] = [
                "TABLE" => "_serie as s",
                "WHERE" => [
                    "c.value_type" => "S",
                    "c.value_id" => ["s.id"],
                ]
            ];
        }
        else {
            $where["\$OR"] = [
                [
                    "LOWER(m.title)" => ["\$CTN" => strtolower($title)]
                ],
                [
                    "LOWER(s.title)" => ["\$CTN" => strtolower($title)]
                ]
            ];
            if($category_id !== -1) $where["category_id"] = $category_id;
            $joins = [
                [
                    "TABLE" => "_movie as m",
                    "WHERE" => [
                        "c.value_type" => "M",
                        "c.value_id" => ["m.id"],
                    ]
                ],
                [
                    "TABLE" => "_serie as s",
                    "WHERE" => [
                        "c.value_type" => "S",
                        "c.value_id" => ["s.id"],
                    ]
                ]
            ];
        }

        $request = QueryBuilder::createSelectRequest(
            "_content as c",
            ["c.id"],
            $where,
            [
                "ORDER_BY" => ["m.title", "s.title"],
                "OFFSET" => $page * 10,
                "LIMIT" => 10,
            ],
            [
                "LEFT_JOIN" => $joins
            ]
        );

        $contents = [];
        foreach ($request->fetchAll(\PDO::FETCH_ASSOC) as $value) {
            $contents[] = Content::findFirst(["id" => $value["id"]]);
        }

        Content::groups("value", "vote", "category");
        $response->code(200)->info("content.get")->send($contents);
    }
}

/**
 * @POST{/api/content/{id}/vote}
 */
class VoteContent extends MustBeConnected
{
    public function checkers(Request $request): array
    {
        return [
            ["type/int", $request->getBody()["vote"], "value", "content.vote.badNumber"],
            ["content/exist", $request->getParam("id"), "content"],
        ];
    }

    public function handler(Request $request, Response $response): void
    {  
        /** @var Content $content */
        $content = $this->floor->pickup("content");
        /** @var User $user */
        $user = $this->floor->pickup("user");
        $value = $this->floor->pickup("value");

        $vote = Vote::findFirst(["content" => $content, "user" => $user]);
        
        if($vote !== null)$vote->setValue($value == 1? 1 : -1)->save();
        else Vote::insertOne(
            fn (Vote $vote) => $vote
                ->setContent($content)
                ->setUser($user)
                ->setValue($value == 1? 1 : -1)
        );

        $response->code(204)->info("content.vote")->send();
    }
}

/**
 * @GET{/api/content/{id}/vote}
 */
class GetVoteContent extends MustBeConnected
{
    public function checkers(Request $request): array
    {
        return [
            ["content/exist", $request->getParam("id"), "content"],
        ];
    }

    public function handler(Request $request, Response $response): void
    {  
        /** @var Content $content */
        $content = $this->floor->pickup("content");
        /** @var User $user */
        $user = $this->floor->pickup("user");

        $response->code(200)->info("content.vote.get")->send(Vote::findFirst(["content" => $content, "user" => $user]));
    }
}

/**
 * @DELETE{/api/content/{id}/vote}
 */
class DeleteVoteContent extends MustBeConnected
{
    public function checkers(Request $request): array
    {
        return [
            ["content/exist", $request->getParam("id"), "content"],
        ];
    }

    public function handler(Request $request, Response $response): void
    {  
        /** @var Content $content */
        $content = $this->floor->pickup("content");
        /** @var User $user */
        $user = $this->floor->pickup("user");

        $vote = Vote::findFirst(["content" => $content, "user" => $user]);
        if($vote !== null) $vote->delete();

        $response->code(204)->info("content.vote.delete")->send();
    }
}