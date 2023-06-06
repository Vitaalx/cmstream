<?php

namespace Entity;

use Core\Entity;

class Star extends Entity
{
    /** @type{int} */
    private int $id;

    /** 
     * notnullable{}
     * @groups{starVideo}
     */
    private Video $video;

    /** 
     * notnullable{}
     * @groups{starUser}
     */
    private User $user;

    /**
     * @type{int}
     * @notnullable{}
     */
    private int $note;

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
     * Get the value of note
     */
    public function getNote(): int
    {
        return parent::get("note");
    }

    /**
     * Get the value of created_at
     */
    public function getCreatedAt(): string
    {
        return parent::get("created_at");
    }

    /**
     * Get the value of updated_at
     */
    public function getUpdatedAt(): string
    {
        return parent::get("updated_at");
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

    /**
     * Set the value of note
     *
     * @return  self
     */
    public function setNote(int $note): self
    {
        parent::set("note", $note);

        return $this;
    }



    /**
     * Set the value of created_at
     *
     * @return  self
     */
    public function setCreatedAt(string $created_at): self
    {
        parent::set("created_at", $created_at);

        return $this;
    }

    /**
     * Set the value of updated_at
     *
     * @return  self
     */
    public function setUpdatedAt(string $updated_at): self
    {
        parent::set("updated_at", $updated_at);

        return $this;
    }
}
