<?php

namespace Entity;

use Core\Entity;

class Episode extends Entity
{
    /** @type{int} */
    private int $id;

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

    /** 
     * @notnullable{}
     */
    private Video $video;

    /** 
     * @notnullable{}
     */
    private Serie $serie;

    // Getters and setters

    public function getId(): int
    {
        return parent::get("id");
    }

    public function getEpisode(): int
    {
        return parent::get("episode");
    }

    public function getSeason(): int
    {
        return parent::get("season");
    }

    public function getCreatedAt(): string
    {
        return parent::get("created_at");
    }

    public function getUpdatedAt(): string
    {
        return parent::get("updated_at");
    }

    public function getVideo(): Video
    {
        return parent::get("video");
    }

    public function getSerie(): Serie
    {
        return parent::get("serie");
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

    public function setVideo(Video $video): self
    {
        parent::set("video", $video);
        return $this;
    }

    public function setSerie(Serie $serie): self
    {
        parent::set("serie", $serie);
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
}
