<?php
namespace controller\SPA\front;

use Core\Controller;
use Core\QueryBuilder;
use Core\Request;
use Core\Response;
use Entity\Post;
use Entity\User;

class index extends Controller{
    public function checkers(Request $request): array
    {
        return [];
    }

    public function handler(Request $request, Response $response): void
    {
        
        // $user = User::insert(["firstname" => "mathieu", "lastname" => "campani", "country" => "FR"]);
        // $user = User::findOne(["id" => 1]);
        // $post = Post::insert(["title" => "test", "author_id" => 1]);
        $post = Post::findOne(["title" => "test"]);
        $post->join("author");
        $user = $post->getAuthor();
        $response->render("front@index", ["id" => $user->getId(), "firstName" => $user->getFirstname()]);
    }
}