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
        
        // $user = User::insertOne(["firstname" => "mathieu2", "lastname" => "campani", "country" => "FR"]);
        // $response->send($user);

        // $user = User::findFirst(["id" => 1]);
        // $response->send($user);
        
        // $posts = Post::findMany([]);
        // $response->send($posts);


        // $post = Post::findFirst(["id" => 22]);
        $user = User::findFirst(["id" => 6]);
        // $post->setAuthor($user);
        // $post->save();
        // $user->getPosts();
        // $response->send($user);
        $user->groups("posts");
        $response->send($user);
        // $response->render("front@index", ["id" => $post->getId()]);
    }
}