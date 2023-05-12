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
        
        // $user = User::insertOne(["firstname" => "mathieu", "lastname" => "campani", "country" => "FR"]);
        // $user = User::findFirst(["id" => 1]);
        // $post = Post::insertOne(["title" => "Mon Super Post", "author_id" => 1, "subtitle" => null]);
        $posts = Post::findMany([]);
        // $post = Post::findFirst(["id" => 22]);
        // $post->join("author");
        // $post->delete();
        // $post->setSubtitle("testttt");
        // $post->save();
        // $post->join("author");
        // $user = $post->getAuthor();
        // $user = User::findFirst();
        // $user->join("posts");

        // $response->send($user);
        $response->send($posts);
        // $response->send($post);
        // $response->render("front@index", ["id" => $post->getId()]);
    }
}