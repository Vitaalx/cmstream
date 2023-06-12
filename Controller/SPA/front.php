<?php
namespace Controller\SPA\front;

use Core\Logger;
use Services\IndexHandler;
use Core\Request;

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
 * @GET{/admin}
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
 * 
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
            ["token/checkAccessToken", $request->getCookie("token") ?? "", "payload"],
            ["user/exist", fn () => $this->floor->pickup("payload")["id"], "user"],
            ["user/mustBeAdmin", fn () => $this->floor->pickup("user"), "user"]
        ];
    }
}