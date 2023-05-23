<?php

namespace Entity;

use Core\Entity;

class Comment extends Entity
{
    /** @type{int} */
    private int $id;

    /**
     * @type{Text}
     * @notnullable{}
     */
    private string $content;

    /**
     * @notnullable{}
     */
    private Video $video;

    /**
     * @notnullable{}
     */
    private User $user;

    /**
     * @type{Boolean}
     * @notnullable{}
     */
    private bool $status;

    /**
     * @type{Date}
     * @notnullable{}
     * @default{CURRENT_TIMESTAMP}
     */
    private string $created_at;

    /**
     * @type{Date}
     * @notnullable{}
     * @default{CURRENT_TIMESTAMP}
     */
    private string $updated_at;

    // Getters and Setters
    public function getId(): self
    {
        return parent::get("id");
    }

    function getContent(): self
    {
        return parent::get("content");
    }

    function getVideo(): self
    {
        return parent::get("video");
    }

    function getUser(): self
    {
        return parent::get("user");
    }

    function getStatus(): self
    {
        return parent::get("status");
    }

    function getCreatedAt(): self
    {
        return parent::get("created_at");
    }

    function getUpdatedAt(): self
    {
        return parent::get("updated_at");
    }

    function setContent(string $content): self
    {
        parent::set("content", $content);

        return $this;
    }

    function setVideo(Video $video): self
    {
        parent::set("video", $video);

        return $this;
    }

    function setUser(User $user): self
    {
        parent::set("user", $user);

        return $this;
    }

    function setStatus(bool $status): self
    {
        parent::set("status", $status);

        return $this;
    }

    function setCreatedAt(string $created_at): self
    {
        parent::set("created_at", $created_at);

        return $this;
    }

    function setUpdatedAt(string $updated_at): self
    {
        parent::set("updated_at", $updated_at);

        return $this;
    }
}