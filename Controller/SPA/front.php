<?php
namespace Controller\SPA\front;

use Core\Logger;
use Services\IndexHandler;
use Core\Request;
use Services\Permissions;

/**
 * @GET{/}
 * @GET{/catalog}
 */
class index extends IndexHandler{}

/**
 * @GET{/signin}
 * @GET{/signup}
 * @GET{/validate}
 * @GET{/forgot-password}
 * @GET{/admin} // TODO: move to connected class when admin panel will be ready
 */
class guest extends IndexHandler{
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
class resetPassword extends IndexHandler{
    public function checkers(Request $request): array
    {
        return [
            ["page/onlyResetPasswordToken", str_replace(" ", "+", $request->getQuery("token")) ?? ""]
        ];
    }
}

/**
 * @GET{/account}
 * @GET{/account/email}
 * @GET{/account/password}
 */
class connected extends IndexHandler{
    public function checkers(Request $request): array
    {
        return [
            ["page/onlyConnected", $request->getCookie("token") ?? ""]
        ];
    }
}

/**
 * @GET{/admin}
 */
class admin extends IndexHandler{
    public function checkers(Request $request): array
    {
        return [
            ["page/onlyConnected", "", "user"],
            ["page/mustHavePermission", Permissions::AccessDashboard]
        ];
    }
}