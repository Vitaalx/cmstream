<?php
namespace controller\SPA\front;

use Core\Controller;
use Core\Request;
use Core\Response;

/**
 * @GET{/}
 */
class index extends Controller{
    public function checkers(Request $request): array
    {
        return [
            ["file/exist", __DIR__ . "/../../index.html", "path"]
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

/**
 * @GET{/signin}
 * @GET{/signup}
 */
class connection extends Controller{
    public function checkers(Request $request): array
    {
        return [
            ["file/exist", __DIR__ . "/../../index.html", "path"]
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