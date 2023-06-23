<?php

namespace Services\Access;

use Core\OverrideController;
use Core\Request;

abstract class AccessRoleEditor extends OverrideController
{
    function extendCheckers(Request $request): array
    {
        return [
            ["token/checkAccessToken", $request->getCookie("token") ?? "", "payload"],
            ["user/exist", fn () => $this->floor->pickup("payload")["id"], "user"],
            ["permissions/roleEditor", fn () => $this->floor->pickup("user")]
        ];
    }

    public function checkers(Request $request): array
    {
        return [];
    }
}