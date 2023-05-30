<?php

namespace Entity;

use Core\Entity;

class Serie extends Entity
{
    /** @type{int} */
    private int $id;

    /** 
     * @notnullable{}
     * @cascade{}
     */
    private Video $video;

    /** 
     * @type{int}
     * @notnullable{}
     */
    private int $episode;

    /** 
     * @type{int}
     * @notnullable{}
     */
    private int $season;

    /** 
     * @type{VARCHAR(255)}
     * @notnullable{}
     */
    private string $image;

    /** 
     * @type{VARCHAR(100)}
     * @notnullable{}
     */
    private string $title;

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

    public function getId(): int
    {
        return parent::get("id");
    }

    public function getVideo(): Video
    {
        return parent::get("video");
    }

    public function getEpisode(): int
    {
        return parent::get("episode");
    }

    public function getSeason(): int
    {
        return parent::get("season");
    }

    public function getImage(): string
    {
        return parent::get("image");
    }

    public function getCreatedAt(): string
    {
        return parent::get("created_at");
    }

    public function getUpdatedAt(): string
    {
        return parent::get("updated_at");
    }

    public function setEpisode(int $episode): self
    {
        parent::set("episode", $episode);
        return $this;
    }

    public function setSeason(int $season): self
    {
        parent::set("season", $season);
        return $this;
    }

    public function setImage(string $image): self
    {
        parent::set("image", $image);
        return $this;
    }

    public function setCreatedAt(string $created_at): self
    {
        parent::set("created_at", $created_at);
        return $this;
    }

    public function setUpdatedAt(string $updated_at): self
    {
        parent::set("updated_at", $updated_at);
        return $this;
    }

    public function setVideo(Video $video): self
    {
        parent::set("video", $video);
        return $this;
    }

    public function setTitle(string $title): self
    {
        parent::set("title", $title);
        return $this;
    }

    public function getTitle(): string
    {
        return parent::get("title");
    }
}
