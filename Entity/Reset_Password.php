<?php

namespace Entity;

use Core\Entity;

class Reset_Password extends Entity
{
    /** @type{int} */
    private int $id;

    /**
     * @type{VARCHAR(255)}
     * @notnullable{}
     */
    private string $password;

    /** 
     * @notnullable{}
     * @groups{userResetPassword}
     */
    private User $user;

    /**
     * @type{Date}
     * @notnullable{}
     * @default{CURRENT_TIMESTAMP}
     */
    private string $created_at;

    // Getters and Setters

    public function getId(): int
    {
        return parent::get("id");
    }

    public function getPassword(): string
    {
        return parent::get("password");
    }

    public function getUser(): User
    {
        return parent::get("user");
    }

    public function getCreated_at(): string
    {
        return parent::get("created_at");
    }

    public function setPassword(string $password): self
    {
        parent::set("password", $password);
        return $this;
    }

    public function setUser(User $user): self
    {
        parent::set("user", $user);
        return $this;
    }

    public function setCreated_at(string $created_at): self
    {
        parent::set("created_at", $created_at);
        return $this;
    }
}