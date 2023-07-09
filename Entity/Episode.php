<?php

namespace Entity;

use Core\Entity;

class Episode extends Entity
{
    /** @type{int} */
    private int $id;

    public function getId(): int
    {
        return parent::get("id");
    }

    /** 
     * @type{VARCHAR(100)}
     * @notnullable{}
     */
    private string $title;

    public function getTitle(): string
    {
        return parent::get("title");
    }

    public function setTitle(string $title): self
    {
        parent::set("title", $title);
        return $this;
    }

    /** 
     * @type{TEXT}
     */
    private string $description;

    public function getDescription(): string
    {
        return parent::get("description");
    }

    public function setDescription(string $description): self
    {
        parent::set("description", $description);
        return $this;
    }

    /** 
     * @type{int}
     * @notnullable{}
     */
    private int $episode;

    public function getEpisode(): int
    {
        return parent::get("episode");
    }

    public function setEpisode(int $episode): self
    {
        parent::set("episode", $episode);
        return $this;
    }

    /** 
     * @type{int}
     * @notnullable{}
     */
    private int $season;

    public function getSeason(): int
    {
        return parent::get("season");
    }

    public function setSeason(int $season): self
    {
        parent::set("season", $season);
        return $this;
    }

    /** 
     * @groups{video}
     * @cascade{}
     */
    private Video $video;

    public function getVideo(): Video
    {
        return parent::get("video");
    }

    public function setVideo(Video $video): self
    {
        parent::set("video", $video);
        return $this;
    }

    /** 
     * @groups{serie}
     * @notnullable{}
     */
    private Serie $serie;

    public function getSerie(): Serie
    {
        return parent::get("serie");
    }

    public function setSerie(Serie $serie): self
    {
        parent::set("serie", $serie);
        return $this;
    }

    /**
     * @type{Date}
     * @notnullable{}
     * @default{CURRENT_TIMESTAMP}
     */
    private string $created_at;

    public function getCreatedAt(): string
    {
        return parent::get("created_at");
    }

    /**
     * @type{Date}
     * @notnullable{}
     * @default{CURRENT_TIMESTAMP}
     */
    private string $updated_at;

    public function getUpdatedAt(): string
    {
        return parent::get("updated_at");
    }

    public function setUpdatedAt(string $updated_at): self
    {
        parent::set("updated_at", $updated_at);
        return $this;
    }
}
