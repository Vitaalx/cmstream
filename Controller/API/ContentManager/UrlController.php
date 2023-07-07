<?php

namespace Controller\API\ContentManager\UrlController;

use Core\Controller;
use Core\Request;
use Core\Response;

use Entity\Url;
use Services\Access\AccessContentsManager;
use Services\Back\VideoManagerService as VideoManager;


/**
 * @POST{/api/url/{video_id}}
 */
class createUrl extends AccessContentsManager
{
    public function checkers(Request $request): array
    {
        return [
            ["type/string", $request->getBody()['url'], "url"],
            ["url/url", fn () => $this->floor->pickup("url"), "url"],
            ["type/int", $request->getParam('video_id'), "video_id"],
            ["video/exist", fn () => $this->floor->pickup("video_id"), "video"]
        ];
    }

    public function handler(Request $request, Response $response): void
    {
        $url = VideoManager::createUrlWhereVideo(
            $this->floor->pickup("video_id"),
            $this->floor->pickup("url")
        );
        $response->code(201)->info("url.created")->send($url);
    }
}

/**
 * @PUT{/api/url/{url_id}}
 */
class updateUrl extends AccessContentsManager
{
    public function checkers(Request $request): array
    {
        return [
            ["type/string", $request->getBody()['url'], "content"],
            ["url/url", fn () => $this->floor->pickup("content"), "content"],
            ["type/int", $request->getParams()['url_id'], "url"],
            ["url/exist", fn () => $this->floor->pickup("url"), "url"]
        ];
    }

    public function handler(Request $request, Response $response): void
    {
        /** @var Url $url */
        $url = $this->floor->pickup("url");
        $url->setUrl($this->floor->pickup("content"));
        $url->save();
        $response->code(200)->info("url.updated")->send($url);
    }
}

/**
 * @DELETE{/api/url/{url_id}}
 */
class deleteUrl extends AccessContentsManager
{
    public function checkers(Request $request): array
    {
        return [
            ["type/int", $request->getParams()['url_id'], "url"],
            ["url/exist", fn () => $this->floor->pickup("url"), "url"]
        ];
    }

    public function handler(Request $request, Response $response): void
    {
        /** @var Url $url */
        $url = $this->floor->pickup("url");
        $url->delete();
        $response->code(204)->info("url.deleted")->send();
    }
}

/**
 * @GET{/api/url/{video_id}}
 */
class getUrlWhereVideo extends Controller
{
    public function checkers(Request $request): array
    {
        return [
            ["type/int", $request->getParams()['video_id'], "video_id"],
            ["video/exist", fn () => $this->floor->pickup("video_id"), "video"]
        ];
    }

    public function handler(Request $request, Response $response): void
    {
        $urls = VideoManager::getUrlWhereVideo($this->floor->pickup("video_id"));
        $response->code(200)->info("url.get")->send($urls);
    }
}