<?php

namespace controller\API\user;

use Core\Request;
use Core\Controller;
use Core\Response;

///user/1?name=mathieu%20&lastname=campani&email=campani.mathieu@gmail.com
class Get extends Controller {
    public function checkers(Request $request): array
    {
        return [
            ["user/id", $request->getParam("id")],
            ["user/name", $request->getQuery("name")],
            ["user/lastname", $request->getQuery("lastname")],
            ["user/email", $request->getQuery("email")]
        ];
    }

    public function handler(Request $request, Response $response): void
    {
        $response->send([
            $this->floor->pickup("user/id"),
            $this->floor->pickup("user/name"),
            $this->floor->pickup("user/lastname"),
            $this->floor->pickup("user/email")
        ]);
    }
}