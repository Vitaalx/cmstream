<?php

namespace Entity;

use Core\Entity;

class Category extends Entity
{
    /** @type{int} */
    private int $id;

    /** 
     * @type{VARCHAR(100)} 
     * @notnullable{}
     */
    private string $title;

    /**
     * @many{Entity\Video,category}
     * @groups{catagories}
     */
    private array $videos;

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

    public function getTitle(): self
    {
        return parent::get("title");
    }

    public function setTitle(string $title): self
    {
        parent::set("title", $title);

        return $this;
    }

    public function getVideo(): array
    {
        return parent::get("video");
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