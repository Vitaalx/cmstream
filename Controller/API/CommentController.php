<?php

namespace Controller\API\CommentController;

use Core\Controller;
use Core\Logger;
use Core\Request;
use Core\Response;
use Entity\Comment;
use Entity\User;
use Entity\Video;
use Services\Access\AccessCommentsManager;
use Services\Access\AccessDashboard;
use Services\MustBeConnected;

/**
 * @POST{/api/video/{id}/comment}
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
            ["type/int", $request->getParam("id"), "videoId"],
            ["video/exist", fn () => $this->floor->pickup("videoId"), "video"],
            ["type/string", $request->getBody()["content"], "content"],
            ["comment/size", fn () => $this->floor->pickup("content"), "content"],
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

        $response->code(200)->info("comment.posted")->send($commentToInsert);
    }
}

/**
 * @GET{/api/comments/unverified/count}
 */
class GetCommentsUnverifiedCount extends AccessDashboard
{
    public function handler(Request $request, Response $response): void
    {
        $response
        ->code(200)
        ->info("comment.unverified.get.count")
        ->send(["count" => Comment::count(["status" => 0])]);
    }
}

/**
 * @GET{/api/comments/verified/count}
 */
class GetCommentsVerifiedCount extends AccessDashboard
{
    public function handler(Request $request, Response $response): void
    {
        $response
        ->code(200)
        ->info("comment.verified.get.count")
        ->send(["count" => Comment::count(["status" => 1])]);
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
            ["type/int", $request->getQuery("page") ?? 0, "page"],
        ];
    }

    public function handler(Request $request, Response $response): void
    {
        /** @var Video $video */
        $video = $this->floor->pickup("video");
        $page = $this->floor->pickup("page");

        $comments = Comment::findMany(["video" => $video, "status" => 1], ["OFFSET" => 10 * $page, "LIMIT" => 10]);
        Comment::groups("commentAuthor", "dateProps");
        $response->code(200)->info("comments.get")->send($comments);
    }
}

/**
 * @GET{/api/video/{id}/comment/self}
 * @param $videoId
 */
class GetCommentsSlef extends MustBeConnected
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
        /** @var Video $user */
        $user = $this->floor->pickup("user");

        $comments = Comment::findMany(["video" => $video, "user" => $user]);
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