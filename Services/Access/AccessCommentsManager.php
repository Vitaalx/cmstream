<?php

namespace Services\Access;

use Core\OverrideController;
use Core\Request;

/**
 * This class is used to check if the user has the right to access the comments manager.
 */
abstract class AccessCommentsManager extends OverrideController
{
    function extendCheckers(Request $request): array
    {
        return [
            ["token/checkAccessToken", $request->getCookie("token") ?? "", "payload"],
            ["user/exist", fn () => $this->floor->pickup("payload")["id"], "user"],
            ["permissions/commentsManager", fn () => $this->floor->pickup("user")]
        ];
    }

    public function checkers(Request $request): array
    {
        return [];
    }
}
