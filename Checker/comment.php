<?php

namespace checker\comment;

use Core\Floor;
use Core\Response;
use Entity\Comment;

function exist(int $commentId, Floor $floor, Response $response): Comment
{
    $comment = Comment::findFirst(["id" => $commentId]);
    if($comment === null) $response->info("comment.notfound")->code(404)->send();
    return $comment;
}

function size(string $content, Floor $floor, Response $response): string
{
    $length = strlen($content);
    if($length < 1 || $length > 1500) $response->info("comment.wrongSize")->code(400)->send(); 
    return $content;
}