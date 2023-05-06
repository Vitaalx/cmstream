<?php
namespace controller\SPA\front;

use Core\Controller;
use Core\QueryBuilder;
use Core\Request;
use Core\Response;

class index extends Controller{
    public function checkers(Request $request): array
    {
        return [];
    }

    public function handler(Request $request, Response $response): void
    {
        $query = new QueryBuilder("user");
        $query->select()
        ->column("id");

        $query->where()
        ->equal("id", 2)->or()
        ->equal("id", 5);

        $response->render("front@index", ["id" => "33"]);
    }
}