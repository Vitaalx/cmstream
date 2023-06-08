<?php

namespace Entity;

use Core\Entity;

class Role extends Entity
{
    /** @type{int} */
    private int $id;

    /**
     * @many{Entity\User,role}
     */
    private array $users;

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
}