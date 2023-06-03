<?php
namespace Controller\handlers;

use Core\Controller;
use Core\LiteController;
use Core\Request;
use Core\Response;

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
        $response
        ->code(200)
        ->sendFile(
            $this->floor->pickup("path")
        );
    }
}

class notfoundIndex extends Controller{
    public function checkers(Request $request): array
    {
        return [
            ["file/exist", __DIR__ . "/../index.html", "path"]
        ];
    }

    public function handler(Request $request, Response $response): void
    {
        $response
        ->code(404)
        ->sendFile(
            $this->floor->pickup("path")
        );
    }
}

class notfound extends LiteController{
    public function handler(Request $request, Response $response): void
    {
        $response->code(404)->info("notfound")->send();
    }
}