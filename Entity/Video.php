<?php

namespace Entity;
use Core\Entity;

class Video extends Entity
{
    /** @type{int} */
    private int $id;

    /** 
     * @type{VARCHAR(100)}
     * @notnullable{} 
     */
    private string $title;

    /** @type{TEXT} */
    private string $description;

    /**
     * @many{Entity\Url,video_url}
     * @groups{urls}
     */
    private array $urls;

    /**
     * @many{Entity\Comment,video}
     * @groups{comments}
     */
    private array $comments;

    /** @notnullable{} */
    private Category $category;

    /**
     * @many{Entity\Star,video}
     * @groups{stars}
     */
    private array $stars;

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

    public function getDescription(): string
    {
        return parent::get("description");
    }

    public function setDescription(string $description): self
    {
        parent::set("description", $description);

        return $this;
    }

    public function getUrls(): Url
    {
        return parent::get("urls");
    }

    public function getComments(): Comment
    {
        return parent::get("comments");
    }

    public function getCategory(): Category
    {
        return parent::get("category");
    }

    public function setCategory(Category $category): self
    {
        parent::set("category", $category);

        return $this;
    }

    public function getStars(): Star
    {
        return parent::get("stars");
    }

    public function getCreatedAt(): string
    {
        return parent::get("created_at");
    }

    public function setCreatedAt(string $created_at): self
    {
        parent::set("created_at", $created_at);

        return $this;
    }

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