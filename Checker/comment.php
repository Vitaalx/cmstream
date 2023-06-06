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