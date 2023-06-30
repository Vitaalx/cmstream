<?php

namespace Entity;

use Core\Entity;

class Movie extends Entity
{
    /** @type{int} */
    private int $id;

    /** 
     * @notnullable{}
     * @cascade{}
     */
    private Video $video;

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
     * @type{TEXT}
     */
    private string $description;

    /**
     * notnullable{}
     * @groups{movieCategory}
     */
    private Category $category;

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

    public function getImage(): string
    {
        return parent::get("image");
    }

    public function getTitle(): string
    {
        return parent::get("title");
    }

    public function getDescription(): string
    {
        return parent::get("description");
    }

    public function getCreatedAt(): string
    {
        return parent::get("created_at");
    }

    public function getUpdatedAt(): string
    {
        return parent::get("updated_at");
    }

    public function setImage(string $image): self
    {
        parent::set("image", $image);
        return $this;
    }

    public function setTitle(string $title): self
    {
        parent::set("title", $title);
        return $this;
    }

    public function setDescription(string $description): self
    {
        parent::set("description", $description);
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

    public function setCategory(Category $category): self
    {
        parent::set("category", $category);
        return $this;
    }

    public function getCategory(): Category
    {
        return parent::get("category");
    }
}
