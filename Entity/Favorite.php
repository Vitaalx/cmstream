<?php

namespace Entity;

use Core\Entity;

class Favorite extends Entity
{
    /** @type{int} */
    private int $id;

     /**
     * @many{Entity\Video,id}
     * @groups{videos}
     */
    private array $video;

    /**
     * @many{Entity\User,id}
     * @groups{users}
     */
    private array $user;

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

    /**
     * Get the value of id
     *
     * @return int
     */
    public function getId(): int
    {
        return parent::get("id");
    }

    /**
     * Get the value of video
     */
    public function getVideo(): Video
    {
        return parent::get("video");
    }

    /**
     * Get the value of user
     */
    public function getUser(): User
    {
        return parent::get("user");
    }

    /**
     * Set the value of video
     *
     * @return  self
     */
    public function setVideo(Video $video): self
    {
        parent::set("video", $video);

        return $this;
    }

    /**
     * Set the value of user
     *
     * @return  self
     */
    public function setUser(User $user): self
    {
        parent::set("user", $user);

        return $this;
    }
}