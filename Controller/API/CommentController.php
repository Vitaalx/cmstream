<?php

namespace controller\API\CommentController;

use Core\Controller;
use Core\Request;
use Core\Response;
use Entity\Comment;
use Entity\User;
use Entity\Video;
use Exceptions\CommentNotFoundException;
use Exceptions\UserNotFoundException;
use Exceptions\VideoNotFoundException;


/**
 * @method POST
 * @path /addComment
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
            ["comment/videoId", $comment['video']],
            ["comment/userId", $comment['user']],
            ["comment/content", $comment['content']]
        ];
    }

    public function handler(Request $request, Response $response): void
    {
        try {
            $user = User::findFirst([
                "id" => $this->floor->pickup("comment/userId")
            ]);

            if(!$user) throw new UserNotFoundException();

            $video = Video::findFirst([
                "id" => $this->floor->pickup("comment/videoId")
            ]);

            if(!$video) throw new VideoNotFoundException();

            $commentToInsert = Comment::insertOne([
                "content" => $this->floor->pickup("comment/content"),
                "video" => $video,
                "user" => $user,
                "status" => 1,
                "created_at" => date("Y-m-d H:i:s"),
                "updated_at" => date("Y-m-d H:i:s")
            ]);

            $commentToInsert->save();

            $response->code(204)->send([
                "comment" => $commentToInsert
            ]);
        } catch (\Exception $e) {
            $response->code($e->getCode())->send([
                "error" => $e->getMessage(), "code" => $e->getCode(), "string" => $e->__toString()
            ]);
        }
    }
}

/**
 * @method GET
 * @path /getComment
 * @param $id
 */
class getComments extends Controller
{

    public function checkers(Request $request): array
    {
        return [
            ["comment/videoId", $request->getQuery("id")]
        ];
    }

    public function handler(Request $request, Response $response): void
    {
        try {
            $video = Video::findFirst([
                "id" => $this->floor->pickup("comment/videoId")
            ]);

            if(!$video) throw new VideoNotFoundException();

            $comments = $video->getComments();

            $video->groups("commentAuthor");

            $response->code(200)->send([
                "comment" => $comments
            ]);
        } catch (\Exception $e) {
            $response->code($e->getCode())->send([
                "error" => $e->getMessage(), "code" => $e->getCode(), "string" => $e->__toString()
            ]);
        }
    }
}

/**
 * @method DELETE
 * @path /deleteComment
 * @param $id
 */
class deleteComment extends Controller
{

    public function checkers(Request $request): array
    {
        return [
            ["comment/id", $request->getQuery("id")]
        ];
    }

    public function handler(Request $request, Response $response): void
    {
        try {
            $comment = Comment::findFirst([
                "id" => $this->floor->pickup("comment/videoId")
            ]);

            if(!$comment) throw new CommentNotFoundException();

            $comment->delete();

            $response->code(200)->send([
                "comment" => $comment
            ]);
        } catch (\Exception $e) {
            $response->code($e->getCode())->send([
                "error" => $e->getMessage(), "code" => $e->getCode(), "string" => $e->__toString()
            ]);
        }
    }
}

//Modify comment status or content
/**
 * @method PUT
 * @path /modifyComment
 * @Body Json Request
 * @param $id
 * @param $content
 * @param $status
 */
class modifyComment extends Controller
{

    public function checkers(Request $request): array
    {
        $comment = $request->getBody();
        return [
            ["comment/id", $request->getQuery("id")],
            ["comment/status", $comment['status']],
            ["comment/content", $comment['content']]
        ];
    }

    public function handler(Request $request, Response $response): void
    {
        try {
            $comment = Comment::findFirst([
                "id" => $this->floor->pickup("comment/id")
            ]);

            if(!$comment) throw new CommentNotFoundException();

            if($this->floor->pickup("comment/status") != null || $this->floor->pickup("comment/status") != "") {
                $comment->setStatus($request->getBody()["status"] == 1 ? 1 : 0);
            }

            if($this->floor->pickup("comment/content") != null || $this->floor->pickup("comment/content") != "") {
                $comment->setContent($request->getBody()["content"]);
            }

            $comment->save();

            $response->code(200)->send([
                "comment" => $comment
            ]);
        } catch (\Exception $e) {
            $response->code($e->getCode())->send([
                "error" => $e->getMessage(), "code" => $e->getCode(), "string" => $e->__toString()
            ]);
        }
    }
}