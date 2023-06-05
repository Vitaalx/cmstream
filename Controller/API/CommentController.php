<?php

namespace Controller\API\CommentController;

use Core\Controller;
use Core\Request;
use Core\Response;
use Entity\Comment;


/**
 * @POST{/comment}
 * @Body Json Request
 * @param $content
 * @param $video_id
 * @param $user_id
 * @param $status
 */
class addComment extends Controller
{

    public function checkers(Request $request): array
    {
        $comment = $request->getBody();
        return [
            ["type/int", $comment['video'], "videoId"],
            ["video/exist", fn() => $this->floor->pickup("videoId"), "video"],
            ["type/int", $comment['user'], "userId"],
            ["type/flawless", $comment['content'], "content"],
            ["user/exist", fn() => $this->floor->pickup("userId"), "user"],
        ];
    }

    public function handler(Request $request, Response $response): void
    {
        $commentToInsert = Comment::insertOne([
            "content" => $this->floor->pickup("content"),
            "video" => $this->floor->pickup("video"),
            "user" => $this->floor->pickup("user"),
            "status" => 1,
        ]);

        //Comment::groups("commentVideo", "commentAuthor");

        $response->code(200)->info("comment.posted")->send($commentToInsert);
    }
}

/**
 * @GET{/comments/{id}}
 * @param $videoId
 */
class getComments extends Controller
{

    public function checkers(Request $request): array
    {
        return [
            ["type/int", $request->getParam("id"), "videoId"],
            ["video/exist", fn() => $this->floor->pickup("videoId"), "video"],
        ];
    }

    public function handler(Request $request, Response $response): void
    {
        /** @var \Entity\Video $video */
        $video = $this->floor->pickup("video");

        $comments = Comment::findMany(["video_id" => $video->getId(), "status" => 1]);
        Comment::groups("commentAuthor");

        $response->code(200)->info("comments.get")->send($comments);
    }
}

/**
 * @DELETE{/comment/{id}}
 * @param $id
 */
class deleteComment extends Controller
{

    public function checkers(Request $request): array
    {
        return [
            ["type/int", $request->getParam("id"), "commentId"],
            ["comment/exist", fn() => $this->floor->pickup("commentId"), "comment"],
        ];
    }

    public function handler(Request $request, Response $response): void
    {
        /** @var \Entity\Comment $comment */
        $comment = $this->floor->pickup("comment");
        $comment->delete();
        $response->code(200)->info("comment.deleted")->send($comment);
    }
}