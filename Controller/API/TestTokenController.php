<?php

namespace controller\API\TestTokenController;

use Core\Controller;
use Core\Request;
use Core\Response;

use Entity\User;

use Services\Back\AuthService;


class checktoken extends Controller
{
    public function checkers(Request $request): array
    {
        return [];
    }

    public function handler(Request $request, Response $response): void
    {
        $auth = new AuthService();
        $token = $request->getBody()['token'];

        if (!$auth->checkToken($token)) {
            $response->info("user.token")->code(401)->send();
        }
        $response->send([
            'status' => 'ok'
        ]);
    }
}
