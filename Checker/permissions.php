<?php
namespace checker\permissions;

use Core\Floor;
use Core\Response;
use Entity\User;
use Services\Permissions;

function userEditor(User $user, Floor $floor, Response $response){
    if($user->hasPermission(Permissions::UserEditor) === false){
        $response->code(401)->info("permission.unauthorized.user_editor")->send();
    }
}