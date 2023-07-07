<?php

namespace Entity;

use Core\Entity;

class Url extends Entity
{
    /** @type{int} */
    private int $id;

    public function getId(): int
    {
        return parent::get("id");
    }

    /**
     * @type{TEXT}
     * @notnullable{}
     */
    private string $value;

    public function getValue(): string
    {
        return parent::get("value");
    }

    public function setValue(string $value): self
    {
        parent::set("value", $value);
        return $this;
    }

    /**
     * @notnullable{}
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
     * @type{Date}
     * @notnullable{}
     * @default{CURRENT_TIMESTAMP}
     * @groups{dateProps}
     */
    private string $created_at;

    public function getCreatedAt(): self
    {
        return parent::get("created_at");
    }
}
