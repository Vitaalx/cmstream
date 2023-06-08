<?php

namespace Controller\API\ContentManager\RoleController;

use Core\Token;
use Core\Controller;
use Core\Request;
use Core\Response;

use Entity\Role;
use Entity\User;
use PHPMailer\Exception;
use Services\MustBeAdmin;
use Services\MustBeConnected;


/**
 * @POST{/api/role}
 * @Body Json Request
 * @param $name
 *
 * @return $role
 */
class addRole extends MustBeAdmin
{
    public function checkers(Request $request): array
    {
        return [
            ["type/string", $request->getBody()["name"], "name"],
            ["role/alreadyExistByName", fn () => $request->getBody()["name"], "name"]
        ];
    }

    public function handler(Request $request, Response $response): void
    {
        /** @var Role $role */
        $role = Role::insertOne([
            "name" => $request->getBody()["name"]
        ]);
        $response->code(200)->info("role.created")->send(["role" => $role]);
    }
}

/**
 * @POST{/api/role/user}
 * @Body Json Request
 * @param $name
 * @param $user_id
 *
 * @return $role
 */
class setRole extends MustBeAdmin
{
    public function checkers(Request $request): array
    {
        return [
            ["type/string", $request->getBody()["name"], "name"],
            ["role/existByName", fn () => $this->floor->pickup("name"), "role"],
            ["type/int", $request->getBody()["user_id"], "user_id"],
            ["user/exist", fn () => $this->floor->pickup("user_id"), "user"]
        ];
    }

    public function handler(Request $request, Response $response): void
    {
        /** @var User $user */
        $user = $this->floor->pickup("user");
        /** @var Role $role */
        $role = $this->floor->pickup("role");
        $user->setRole($role);
        date_default_timezone_set('Europe/Paris');
        $user->setUpdatedAt(date("Y-m-d H:i:s"));
        $user::groups("userRole");
        $user->save();
        $response->code(200)->info("user.role.added")->send(["user" => $user]);
    }
}

/**
 * @GET{/api/roles}
 *
 * @return $role
 */
class getRoles extends MustBeAdmin
{
    public function checkers(Request $request): array
    {
        return [];
    }

    public function handler(Request $request, Response $response): void
    {
        /** @var Role $roles */
        $roles = Role::findMany();
        $response->code(200)->info("roles.get")->send(["role" => $roles]);
    }
}


/**
 * @PUT{/api/role/{id}}
 * @param int $id
 * @Body Json Request
 * @param $name
 *
 * @return void
 */
class modifyRole extends MustBeAdmin
{
    public function checkers(Request $request): array
    {
        return [
            ["type/int" , $request->getParam("id"), "id"],
            ["type/string", $request->getBody()["name"], "name"],
            ["role/alreadyExistByName", fn () => $request->getBody()["name"], "name"],
            ["role/admin", fn () => $this->floor->pickup("id")],
            ["role/exist", fn () => $this->floor->pickup("id"), "role"]
        ];
    }

    public function handler(Request $request, Response $response): void
    {
        /** @var Role $role */
        $role = $this->floor->pickup("role");
        $role->setName($request->getBody()["name"]);
        date_default_timezone_set('Europe/Paris');
        $role->setUpdatedAt(date("Y-m-d H:i:s"));
        $role->save();
        $response->code(200)->info("role.modified")->send();
    }
}

/**
 * @DELETE{/api/role/{id}}
 * @param int $id
 *
 * @return void
 */
class deleteRole extends MustBeAdmin
{
    public function checkers(Request $request): array
    {
        return [
            ["type/int" , $request->getParam("id"), "id"],
            ["role/admin", fn () => $this->floor->pickup("id")],
            ["role/exist", fn () => $this->floor->pickup("id"), "role"]
        ];
    }

    public function handler(Request $request, Response $response): void
    {
        /** @var Role $role */
        $role = $this->floor->pickup("role");
        $role->delete();
        $response->code(200)->info("role.deleted")->send();
    }
}

/**
 * @PUT{/api/role/user/{id}}
 * @param int $user_id
 *
 * @return void
 */
class unsetRole extends MustBeAdmin
{
    public function checkers(Request $request): array
    {
        return [
            ["type/int" , $request->getParam("id"), "id"],
            ["user/exist", fn () => $this->floor->pickup("id"), "user"],
            ["role/hasRole", fn () => $this->floor->pickup("id")]
        ];
    }

    public function handler(Request $request, Response $response): void
    {
        /** @var User $user */
        $user = $this->floor->pickup("user");
        $user->getRole()->delete();
        date_default_timezone_set('Europe/Paris');
        $user->setUpdatedAt(date("Y-m-d H:i:s"));
        $user->save();
        $response->code(200)->info("role.removed")->send();
    }
}