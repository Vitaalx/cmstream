<?php

namespace Entity;

use Core\Entity;
use Entity\RoleEnum;

class Role extends Entity
{
    /** @type{int} */
    private int $id;

    /** 
     * @type{VARCHAR(100)} 
     * @notnullable{}
     */
    private string $title;

    /**
     * @many{Entity\User,role}
     * @groups{users}
     */
    private array $user;

    /**
     * @type{VARCHAR(20)}
     * @notnullable{}
     */
    private String $role;

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

    public function setId(int $id): void
    {
        parent::set("id", $id);
    }

    public function getTitle(): self
    {
        return parent::get("title");
    }

    public function setTitle(string $title): void
    {
        parent::set("title", $title);
    }

    public function getUser(): User
    {
        return parent::get("user");
    }

    public function setUser(User $user): void
    {
        parent::set("user", $user);
    }

    public function getRole(): String
    {
        return parent::get("role");
    }

    public function setRole(String $role): void
    {
        parent::set("role", $role);
    }

    public function getCreatedAt(): string
    {
        return parent::get("created_at");
    }

    public function getUpdatedAt(): string
    {
        return parent::get("updated_at");
    }
}