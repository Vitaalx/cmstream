<?php
namespace Services;

use Core\OverrideController;
use Core\Request;

abstract class MustBeAdmin extends OverrideController
{
    function extendCheckers(Request $request): array
    {
        return [
            ["token/check", $request->getCookie("token") ?? "", "payload"],
            ["user/exist", fn () => $this->floor->pickup("payload")["id"], "user"],
            ["user/mustBeAdmin", fn () => $this->floor->pickup("user"), "user"]
        ];
    }
}