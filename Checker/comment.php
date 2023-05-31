<?php

namespace checker\comment;

use Core\Floor;
use Core\Response;
use Entity\Comment;

function videoId(int $id): int {
    return $id;
}

function userId(int $id): int {
    return $id;
}

function id(int $id): int {
    return $id;
}

function status(int $id): int {
    return $id;
}

function content(string $content): string {
    return htmlspecialchars($content);
}

function exist(int $commentId, Floor $floor, Response $response): Comment
{
    $comment = Comment::findFirst(["id" => $commentId]);
    if($comment === null) $response->info("comment.notfound")->code(404)->send();
    return $comment;
}