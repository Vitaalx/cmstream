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
     * @groups{commentVideo}
     */
    private Video $video;

    /**
     * @notnullable{}
     * @groups{commentAuthor}
     */
    private User $user;

    /**
     * @type{int}
     * @notnullable{}
     * @default{0}
     */
    private int $status;

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

    function setStatus(int $status): self
    {
        parent::set("status", $status);

        return $this;
    }
}