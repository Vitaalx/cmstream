<?php

namespace Controller\API\ContentManager\RoleController;

use Core\Request;
use Core\Response;

use Services\Permissions;
use Entity\Role;
use Entity\User;
use Services\Access\AccessRoleEditor;


/**
 * @POST{/api/role}
 * @Body Json Request
 * @param $name
 *
 * @return $role
 */
class addRole extends AccessRoleEditor
{
    public function checkers(Request $request): array
    {
        return [
            ["type/string", $request->getBody()["name"], "name"],
            ["type/notEmpty", fn () => $this->floor->pickup("name")],
            ["role/alreadyExistByName", fn () => $this->floor->pickup("name")],
        ];
    }

    public function handler(Request $request, Response $response): void
    {
        /** @var Role $role */
        $role = Role::insertOne([
            "name" => $this->floor->pickup("name")
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
class setRole extends AccessRoleEditor
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
class getRoles extends AccessRoleEditor
{
    public function checkers(Request $request): array
    {
        return [];
    }

    public function handler(Request $request, Response $response): void
    {
        /** @var Role $roles */
        $roles = Role::findMany();
        Role::groups("rolePermission");
        $response->code(200)->info("roles.get")->send(["roles" => $roles]);
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
class modifyRole extends AccessRoleEditor
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
 * @PUT{/api/role/{id}/permissions}
 * @param int $id
 * @Body Json Request
 * @param $permissions
 *
 * @return void
 */
class modifyPermissionByRole extends AccessRoleEditor
{
    public function checkers(Request $request): array
    {
        return [
            ["type/int" , $request->getParam("id"), "id"],
            ["role/admin" , fn () => $this->floor->pickup("id")],
            ["type/arrayCheck", $request->getBody()["permissions"], "permissions"],
            ["role/exist", fn () => $this->floor->pickup("id"), "role"],
            ["permissions/exist", fn () => $this->floor->pickup("permissions"), "permissions"]
        ];
    }

    public function handler(Request $request, Response $response): void
    {
        /** @var Role $role */
        $role = $this->floor->pickup("role");
        $permissions = $this->floor->pickup("permissions");
        foreach (Permissions::getAllPermissions() as $permission) {
            if (in_array($permission, $permissions)) {
                $role->addPermission(Permissions::getPermissionByName($permission));
            } else {
                $role->removePermission(Permissions::getPermissionByName($permission));
            }
        }
        date_default_timezone_set('Europe/Paris');
        $role->setUpdatedAt(date("Y-m-d H:i:s"));
        $role->save();
        $response->code(200)->info("role.permission.added")->send();
    }
}

/**
 * @DELETE{/api/role/{id}}
 * @param int $id
 *
 * @return void
 */
class deleteRole extends AccessRoleEditor
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
class unsetRole extends AccessRoleEditor
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
        $user->setRole(null);
        date_default_timezone_set('Europe/Paris');
        $user->setUpdatedAt(date("Y-m-d H:i:s"));
        $user->save();
        $response->code(200)->info("role.removed")->send();
    }
}