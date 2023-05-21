<?php

namespace Entity;

use Core\Entity;

class Url extends Entity
{
    /** @type{int} */
    private int $id;

    /** @type{TEXT}
     * @notnullable{}
     */
    private string $url;

    private Video $video_url;

    /**
     * @type{Date}
     * @notnullable{}
     */
    private string $created_at;

    /**
     * @type{Date}
     * @notnullable{}
     */
    private string $updated_at;

    // Getters and Setters

    public function getId(): int
    {
        return parent::get("id");
    }

    public function getUrl(): self
    {
        return parent::get("url");
    }

    public function setUrl(string $url): self
    {
        parent::set("url", $url);
        return $this;
    }

    public function getVideoUrl(): Video
    {
        return parent::get("video_url");
    }

    public function setVideoUrl(Video $video_url): self
    {
        parent::set("video_url", $video_url);
        return $this;
    }

    public function getCreatedAt(): self
    {
        return parent::get("created_at");
    }

    public function setCreatedAt(string $created_at): self
    {
        parent::set("created_at", $created_at);
        return $this;
    }

    public function getUpdatedAt(): self
    {
        return parent::get("updated_at");
    }

    public function setUpdatedAt(string $updated_at): self
    {
        parent::set("updated_at", $updated_at);
        return $this;
    }
}