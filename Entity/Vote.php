<?php

namespace Entity;

use Core\Entity;

class Vote extends Entity
{
    /** @type{int} */
    private int $id;

    public function getId(): int
    {
        return parent::get("id");
    }
 
    /**
     * @type{int}
     * @notnullable{}
     */
    private int $value;

    public function getValue(): int
    {
        return parent::get("value");
    }

    public function setValue(int $value): self
    {
        parent::set("value", $value);
        return $this;
    }

    /** 
     * @notnullable{}
     */
    private Content $content;

    public function getContent(): Content
    {
        return parent::get("content");
    }

    public function setContent(Content $content): self
    {
        parent::set("content", $content);
        return $this;
    }

    /** 
     * @notnullable{}
     */
    private User $user;

    public function getUser(): User
    {
        return parent::get("user");
    }

    public function setUser(User $user): self
    {
        parent::set("user", $user);
        return $this;
    }

    /**
     * @type{Date}
     * @notnullable{}
     * @default{CURRENT_TIMESTAMP}
     * @groups{dateProps}
     */
    private string $created_at;

    public function getCreatedAt(): string
    {
        return parent::get("created_at");
    }
}