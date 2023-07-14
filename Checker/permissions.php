<?php
namespace checker\permissions;

use Core\Floor;
use Core\Response;
use Entity\User;
use Services\Permissions;

function exist(array $permissions, Floor $floor, Response $response): array
{
    foreach ($permissions as $permission) {
        if (Permissions::exist($permission) === false) {
            $response->info("permission.notfound")->code(404)->send();
        }
    }
    return $permissions;
}
function roleEditor(User $user, Floor $floor, Response $response){
    if($user->hasPermission(Permissions::RoleEditor) === false){
        $response->code(401)->info("permission.unauthorized.role_editor")->send();
    }
}

function commentsManager(User $user, Floor $floor, Response $response){
    if($user->hasPermission(Permissions::CommentsManager) === false){
        $response->code(401)->info("permission.unauthorized.comments_manager")->send();
    }
}

function contentsManager(User $user, Floor $floor, Response $response){
    if($user->hasPermission(Permissions::ContentsManager) === false){
        $response->code(401)->info("permission.unauthorized.contents_manager")->send();
    }
}

function statsViewer(User $user, Floor $floor, Response $response){
    if($user->hasPermission(Permissions::StatsViewer) === false){
        $response->code(401)->info("permission.unauthorized.stats_viewer")->send();
    }
}

function userEditor(User $user, Floor $floor, Response $response){
    if($user->hasPermission(Permissions::UserEditor) === false){
        $response->code(401)->info("permission.unauthorized.user_editor")->send();
    }
}

function configEditor(User $user, Floor $floor, Response $response){
    if($user->hasPermission(Permissions::ConfigEditor) === false){
        $response->code(401)->info("permission.unauthorized.config_editor")->send();
    }
}

function dashboard(User $user, Floor $floor, Response $response){
    if($user->hasPermission(Permissions::AccessDashboard) === false){
        $response->code(401)->info("permission.unauthorized.dashboard")->send();
    }
}