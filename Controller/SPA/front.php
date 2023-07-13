<?php

namespace Controller\SPA\front;

use Core\File;
use Core\LiteController;
use Core\Logger;
use Services\IndexHandler;
use Core\Request;
use Core\Response;
use Entity\Episode;
use Entity\Movie;
use Entity\Serie;
use Services\Permissions;

/**
 * @GET{/}
 * @GET{/catalog}
 * @GET{/pages/{name}}
 */
class index extends IndexHandler{}

/**
 * @GET{/signin}
 * @GET{/signup}
 * @GET{/validate}
 * @GET{/forgot-password}
 */
class guest extends IndexHandler
{
    public function checkers(Request $request): array
    {
        return [
            ["page/onlyGuest", $request->getCookie("token") ?? ""]
        ];
    }
}

/**
 * @GET{/reset-password}
 */
class resetPassword extends IndexHandler
{
    public function checkers(Request $request): array
    {
        return [
            ["page/onlyResetPasswordToken", str_replace(" ", "+", $request->getQuery("token")) ?? ""]
        ];
    }
}

/**
 * @GET{/lists}
 * @GET{/account}
 * @GET{/account/email}
 * @GET{/account/password}
 */
class connected extends IndexHandler
{
    public function checkers(Request $request): array
    {
        return [
            ["page/onlyConnected", $request->getCookie("token") ?? ""]
        ];
    }
}

/**
 * @GET{/dashboard}
 */
class admin extends IndexHandler
{
    public function checkers(Request $request): array
    {
        return [
            ["page/onlyConnected", "", "user"],
            ["page/mustHavePermission", Permissions::AccessDashboard],
            ["page/mustHavePermission", Permissions::StatsViewer]
        ];
    }
}

/**
 * @GET{/dashboard/users}
 */
class adminUser extends IndexHandler
{
    public function checkers(Request $request): array
    {
        return [
            ["page/onlyConnected", "", "user"],
            ["page/mustHavePermission", Permissions::AccessDashboard],
            ["page/mustHavePermission", Permissions::UserEditor]
        ];
    }
}

/**
 * @GET{/dashboard/roles}
 */
class adminRole extends IndexHandler
{
    public function checkers(Request $request): array
    {
        return [
            ["page/onlyConnected", "", "user"],
            ["page/mustHavePermission", Permissions::AccessDashboard],
            ["page/mustHavePermission", Permissions::RoleEditor]
        ];
    }
}

/**
 * @GET{/dashboard/config-app}
 * @GET{/dashboard/config-mail}
 */
class adminConfig extends IndexHandler
{
    public function checkers(Request $request): array
    {
        return [
            ["page/onlyConnected", "", "user"],
            ["page/mustHavePermission", Permissions::AccessDashboard],
            ["page/mustHavePermission", Permissions::ConfigEditor]
        ];
    }
}

/**
 * @GET{/dashboard/comments}
 */
class dashboardManager extends IndexHandler
{
    public function checkers(Request $request): array
    {
        return [
            ["page/onlyConnected", "", "user"],
            ["page/mustHavePermission", Permissions::AccessDashboard],
            ["page/mustHavePermission", Permissions::CommentsManager],
        ];
    }
}

/**
 * @GET{/dashboard/add-content}
 * @GET{/dashboard/categories}
 * @GET{/dashboard/edit-video/{typeEdit}/{id}}
 * @GET{/dashboard/series}
 * @GET{/dashboard/movies}
 * @GET{/dashboard/pages}
 */
class adminContent extends IndexHandler
{
    public function checkers(Request $request): array
    {
        return [
            ["page/onlyConnected", "", "user"],
            ["page/mustHavePermission", Permissions::AccessDashboard],
            ["page/mustHavePermission", Permissions::ContentsManager]
        ];
    }
}

/**
 * @GET{/movie/{id}}
 */
class GetMovie extends IndexHandler{
    public function extendHandler(Request $request, Response $response): void
    {
        if($request->getHeader("Page-Access") === null){
            $movie = Movie::findFirst(["id" => $request->getParam("id")]);
            if($movie === null) return;
            $this->description = $movie->getDescription();
            $this->keywords = "movie, {$movie->getTitle()}";
        }
    }
}

/**
 * @GET{/serie/{id}}
 */
class GetSerie extends IndexHandler{
    public function extendHandler(Request $request, Response $response): void
    {
        if($request->getHeader("Page-Access") === null){
            $serie = Serie::findFirst(["id" => $request->getParam("id")]);
            if($serie === null) return;
            $this->description = $serie->getDescription();
            $this->keywords = "serie, {$serie->getTitle()}";
        }
    }
}

/**
 * @GET{/serie/{id}/season/{season}/episode/{episode}}
 */
class GetEpisode extends IndexHandler{
    public function extendHandler(Request $request, Response $response): void
    {
        if($request->getHeader("Page-Access") === null){
            $episode = Episode::findFirst([
                "serie_id" => $request->getParam("id"), 
                "season" => $request->getParam("season"),
                "episode" => $request->getParam("episode")
            ]);
            if($episode === null) return;
            $this->description = $episode->getDescription();
            $this->keywords = "episode, {$episode->getTitle()}";
        }
    }
}

/**
 * @GET{/sitemap.xml}
 */
class GetSitemap extends LiteController
{
    public function handler(Request $request, Response $response): void
    {
        $pages = new File(__DIR__ . "/../../public/cuteVue/pages.json");
        
        $vars = [
            "config" => CONFIG,
            "pages" => json_decode($pages->read(), true),
            "movies" => Movie::findIterator([]),
            "episodes" => Episode::findIterator([]),
        ];

        $response->code(200)->info("sitemap")->setHeader("Content-Type", "text/xml")->render("sitemap", "none", $vars);
    }
}
