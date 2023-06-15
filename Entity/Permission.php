<?php

namespace Entity;

use Core\Entity;
use Services\Permissions;

class Permission extends Entity{
    /** 
     * @type{int}
     * @groups{}
     */
    private int $id;

    /**
     * @notnullable{}
     */
    private Role $role;

    /**
     * @type{varchar(20)}
     * @notnullable{}
     */
    private string $name;

    /**
     * @type{Date}
     * @notnullable{}
     * @default{CURRENT_TIMESTAMP}
     * @groups{dateProps}
     */
    private string $created_at;

    public function getId(): int
    {
        return parent::get("id");
    }

    /**
     * Get the value of role
     *
     * @return Role
     */
    public function getRole(): Role
    {
        return parent::get("role");
    }

    /**
     * Set the value of role
     *
     * @param Role $role
     *
     * @return self
     */
    public function setRole(Role $role): self
    {
        parent::set("role", $role);

        return $this;
    }

    /**
     * Get the value of name
     *
     * @return string
     */
    public function getName(): string
    {
        return parent::get("name");
    }

    /**
     * Set the value of name
     *
     * @param permissions $name
     *
     * @return self
     */
    public function setName(Permissions $name): self
    {
        parent::set("name", $name->value);

        return $this;
    }

    /**
     * Get the value of created_at
     *
     * @return string
     */
    public function getCreatedAt(): string
    {
        return $this->created_at;
    }
}