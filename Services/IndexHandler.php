<?php
namespace Services;

use Core\OverrideController;
use Core\Request;
use Core\Response;

abstract class IndexHandler extends OverrideController{
    public function extendCheckers(Request $request): array
    {
        return [
            ["file/exist", __DIR__ . "/../index.html", "path"]
        ];
    }

    public function checkers(Request $request): array
    {
        return [];
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
