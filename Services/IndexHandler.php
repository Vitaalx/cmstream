<?php
namespace Services;

use Core\OverrideController;
use Core\Request;
use Core\Response;

abstract class IndexHandler extends OverrideController{
    public int $code = 200;

    public function extendCheckers(Request $request): array
    {
        return [];
    }

    public function checkers(Request $request): array
    {
        return [];
    }

    public function handler(Request $request, Response $response): void
    {
        $response
        ->code($this->code)
        ->render("index", "none");
    }
}
