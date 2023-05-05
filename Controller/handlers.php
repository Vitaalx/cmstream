<?php
namespace controller\handlers;

use Core\Controller;
use Core\Request;
use Core\Response;

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