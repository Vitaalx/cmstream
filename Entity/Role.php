<?php

namespace Entity;

use Core\Entity;
use Services\Permissions;

class Role extends Entity
{
    /** @type{int} */
    private int $id;

    /**
     * @many{Entity\User,role}
     */
    private array $users;

    /**
     * @many{Entity\Permission,role}
     * @cascade{}
     * @groups{rolePermission}
     */
    private array $permissions;

    /**
     * @type{VARCHAR(20)}
     * @notnullable{}
     */
    private String $name;

    /**
     * @type{Date}
     * @notnullable{}
     * @default{CURRENT_TIMESTAMP}
     * @groups{dateProps}
     */
    private string $created_at;

    /**
     * @type{Date}
     * @notnullable{}
     * @default{CURRENT_TIMESTAMP}
     * @groups{dateProps}
     */
    private string $updated_at;

    // Getters and Setters

    public function getId(): int
    {
        return parent::get("id");
    }

    /**
     * @return \Entity\User[]
     */
    public function getUsers(): array
    {
        return parent::get("users");
    }

    public function getName(): String
    {
        return parent::get("name");
    }

    public function setName(String $name): Role
    {
        parent::set("name", $name);

        return $this;
    }

    public function getCreatedAt(): string
    {
        return parent::get("created_at");
    }

    public function getUpdatedAt(): string
    {
        return parent::get("updated_at");
    }

    public function setUpdatedAt(string $date): self
    {
        parent::set("updated_at", $date);

        return $this;
    }

    /**
     * Get the value of permissions
     *
     * @return Permission[]
     */
    public function getPermissions(): array
    {
        return parent::get("permissions");
    }

    public function addPermission(Permissions $permissionName): void
    {
        $perm = Permission::findFirst([
            "role_id" => parent::get("id"),
            "name" => $permissionName->value
        ]);
        if($perm === null) Permission::insertOne(
            fn (Permission $permission) => $permission
                ->setName($permissionName)
                ->setRole($this)
        );
    }

    public function removePermission(Permissions $permissionName): void
    {
        $permissionName = $permissionName->value;
        $perm = Permission::findFirst([
            "role_id" => parent::get("id"),
            "name" => $permissionName
        ]);
        if($perm !== null) $perm->delete();
    }

    public function hasPermission(Permissions $permissionName): bool
    {
        $permissionName = $permissionName->value;
        $perm = Permission::findFirst([
            "role_id" => parent::get("id"),
            "name" => $permissionName
        ]);
        return !!$perm;
    }
}