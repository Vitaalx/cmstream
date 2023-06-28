<?php

namespace Controller\API\CommentController;

use Core\Controller;
use Core\Request;
use Core\Response;
use Entity\Comment;
use Entity\User;
use Entity\Video;
use Services\MustBeAdmin;
use Services\MustBeConnected;

/**
 * @POST{/api/comment}
 * @Body Json Request
 * @param $content
 * @param $video_id
 * @param $status
 */
class addComment extends MustBeConnected
{
    public function checkers(Request $request): array
    {
        return [
            ["type/int", $request->getBody()["video_id"], "videoId"],
            ["type/int", $request->getBody()["user_id"], "userId"],
            ["user/exist", fn() => $this->floor->pickup("userId"), "user"],
            ["video/exist", fn() => $this->floor->pickup("videoId"), "video"],
            ["type/flawless", $request->getBody()['content'], "content"],
        ];
    }

    public function handler(Request $request, Response $response): void
    {
        /** @var User $user */
        $user = $this->floor->pickup("user");
        /** @var Video $video */
        $video = $this->floor->pickup("video");
        $commentToInsert = Comment::insertOne(
            fn(Comment $comment) => $comment
                ->setVideo($video)
                ->setUser($user)
                ->setContent($this->floor->pickup("content"))
                ->setStatus(1)
        );

        //Comment::groups("commentVideo", "commentAuthor");

        $response->code(200)->info("comment.posted")->send($commentToInsert);
    }
}

/**
 * @GET{/api/comments/{id}}
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
        /** @var Video $video */
        $video = $this->floor->pickup("video");

        $comments = Comment::findMany(["video_id" => $video->getId(), "status" => 1]);
        Comment::groups("commentAuthor", "dateProps");


        $response->code(200)->info("comments.get")->send($comments);
    }
}

/**
 * @DELETE{/api/comment/{id}}
 * @param $id
 */
class deleteComment extends MustBeAdmin
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
