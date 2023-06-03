<?php
namespace Controller\SPA\front;

use Services\IndexHandler;
use Core\Request;

/**
 * @GET{/}
 */
class index extends IndexHandler{}

/**
 * @GET{/signin}
 * @GET{/signup}
 */
class connection extends IndexHandler{
    public function checkers(Request $request): array
    {
        return [
            
        ];
    }
}