<?php
namespace controller\handlers;

use Core\Controller;
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
        $response->sendFile(
            $this->floor->pickup("path")
        );
    }
}

class notfound extends Controller{
    public function checkers(Request $request): array
    {
        return [];
    }

    public function handler(Request $request, Response $response): void
    {
        $response->code(404)->info("notfound")->send();
    }
}