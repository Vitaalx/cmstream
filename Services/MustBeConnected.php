<?php

namespace Services;

use Core\OverrideController;
use Core\Request;

/**
 * This class is used to check if the user is connected.
 * Allows preset checker to be used in other Controllers.
 */
abstract class MustBeConnected extends OverrideController
{
    function extendCheckers(Request $request): array
    {
        return [
            ["token/checkAccessToken", $request->getCookie("token") ?? "", "payload"],
            ["user/exist", fn () => $this->floor->pickup("payload")["id"], "user"],
        ];
    }

    public function checkers(Request $request): array
    {
        return [];
    }
}
