<?php
namespace Controller\handlers;

use Core\Controller;
use Core\LiteController;
use Core\Logger;
use Core\Request;
use Core\Response;
use Services\IndexHandler;

class assets extends Controller{
    public function checkers(Request $request): array
    {
        return [
            ["file/checkPath", $request->getPath(), "path"],
            ["file/exist", fn() => $this->floor->pickup("path")]
        ];
    }

    public function handler(Request $request, Response $response): void
    {
        Logger::disable();

        $response
        ->setHeader("Access-Control-Allow-Methods", "GET")
        ->code(200)
        ->sendFile(
            $this->floor->pickup("path")
        );
    }
}

class notfoundIndex extends IndexHandler{
    public int $code = 404;
}

class notfound extends LiteController{
    public function handler(Request $request, Response $response): void
    {
        $response->code(404)->info("notfound")->send();
    }
}