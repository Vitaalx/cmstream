<?php

namespace Entity;

use Core\Entity;

class Reset_Password extends Entity
{
    /** @type{int} */
    private int $id;

    /** 
     * @notnullable{}
     * @groups{userResetPassword}
     */
    private User $user;

    /**
     * @type{int}
     * @notnullable{}
     * @default{date_part('epoch'::text, now())}
     */
    private int $timestamp;

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

    public function getUser(): User
    {
        return parent::get("user");
    }

    public function getCreated_at(): string
    {
        return parent::get("created_at");
    }

    public function getTimestamp(): int
    {
        return parent::get("timestamp");
    }

    public function setUser(User $user): self
    {
        parent::set("user", $user);
        return $this;
    }
}