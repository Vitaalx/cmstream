<?php

namespace Controller\API\CommentController;

use Core\Controller;
use Core\Request;
use Core\Response;
use Entity\Comment;
use Entity\User;
use Entity\Video;
use Services\Access\AccessCommentsManager;
use Services\MustBeConnected;

/**
 * @POST{/api/comment}
 * @Body Json Request
 * @param $content
 * @param $video_id
 * @param $user_id
 * @param $status
 */
class addComment extends MustBeConnected
{
    public function checkers(Request $request): array
    {
        return [
            ["type/int", $request->getBody()["video_id"], "videoId"],
            ["type/int", $request->getBody()["user_id"], "userId"],
            ["user/exist", fn () => $this->floor->pickup("userId"), "user"],
            ["video/exist", fn () => $this->floor->pickup("videoId"), "video"],
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
            fn (Comment $comment) => $comment
                ->setVideo($video)
                ->setUser($user)
                ->setContent($this->floor->pickup("content"))
        );

        //Comment::groups("commentVideo", "commentAuthor");

        $response->code(200)->info("comment.posted")->send($commentToInsert);
    }
}

/**
 * @GET{/api/comments/verify/count}
 */
class GetCommentsVerifyCount extends AccessCommentsManager
{
    public function handler(Request $request, Response $response): void
    {
        $response
        ->code(200)
        ->info("comment.get.count")
        ->send(["count" => Comment::count(["status" => 0])]);
    }
}

/**
 * @GET{/api/comments/verify}
 */
class GetCommentsVerify extends AccessCommentsManager
{
    public function handler(Request $request, Response $response): void
    {
        $comment = Comment::findFirst(["status" => 0]);

        Comment::groups("commentAuthor", "commentVideo");

        $response
        ->code(200)
        ->info("comment.get.count")
        ->send($comment);
    }
}

/**
 * @GET{/api/video/{id}/comment}
 * @param $videoId
 */
class getComments extends Controller
{

    public function checkers(Request $request): array
    {
        return [
            ["type/int", $request->getParam("id"), "videoId"],
            ["video/exist", fn () => $this->floor->pickup("videoId"), "video"],
        ];
    }

    public function handler(Request $request, Response $response): void
    {
        /** @var Video $video */
        $video = $this->floor->pickup("video");

        $comments = $video->getComments();
        Comment::groups("commentAuthor", "dateProps");

        $response->code(200)->info("comments.get")->send($comments);
    }
}

/**
 * @DELETE{/api/comment/{id}}
 * @param $id
 */
class deleteComment extends AccessCommentsManager
{

    public function checkers(Request $request): array
    {
        return [
            ["type/int", $request->getParam("id"), "commentId"],
            ["comment/exist", fn () => $this->floor->pickup("commentId"), "comment"],
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

/**
 * @PATCH{/api/comment/{id}/validate}
 * @param $id
 */
class validateComment extends AccessCommentsManager
{

    public function checkers(Request $request): array
    {
        return [
            ["type/int", $request->getParam("id"), "commentId"],
            ["comment/exist", fn () => $this->floor->pickup("commentId"), "comment"],
        ];
    }

    public function handler(Request $request, Response $response): void
    {
        /** @var \Entity\Comment $comment */
        $comment = $this->floor->pickup("comment");
        $comment->setStatus(1);
        $comment->save();
        $response->code(200)->info("comment.validate")->send();
    }
}