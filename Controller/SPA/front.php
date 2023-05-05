<?php
namespace controller\SPA\front;

use Core\Controller;
use Core\Request;
use Core\Response;
use Entity\User ;

class index extends Controller{
    public function checkers(Request $request): array
    {
        return [];
    }

    public function handler(Request $request, Response $response): void
    {
        $user = User::findOne(1);
        // $post = $user->post->joinOne(2);
        // $userLike = $post->userLike->joinArray();
        $response->send();
        // $response->render("front@index", ["id" => "33"]);
    }
}