<?php

namespace Controller\SPA\front;

use Core\Logger;
use Services\IndexHandler;
use Core\Request;
use Services\Permissions;

/**
 * @GET{/}
 * @GET{/catalog}
 * @GET{/video}
 * @GET{/show}
 */
class index extends IndexHandler
{
}

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
 * @GET{/dashboard/categories}
 * @GET{/dashboard/series}
 * @GET{/dashboard/movies}
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
class dashboradManager extends IndexHandler
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
