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
     * @unique{}
     */
    private string $title;

    /** @type{TEXT} */
    private string $description;

    /**
     * @many{Entity\Url,video_url}
     * @groups{urls}
     * @cascade{}
     */
    private array $urls;

    /**
     * @many{Entity\Comment,video}
     * @groups{comments}
     * @cascade{}
     */
    private array $comments;

    /** 
     * @notnullable{}
     * @groups{videoCategory}
     */
    private Category $category;

    /**
     * @many{Entity\Star,video}
     * @groups{stars}
     */
    private array $stars;

    /**
     * @many{Entity\Film,video}
     * @groups{film}
     */
    private array $film;

    /**
     * @many{Entity\Serie,video}
     * @groups{series}
     */
    private array $series;

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

    public function getId(): int
    {
        return parent::get("id");
    }

    public function getTitle(): string
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

    public function getUrls(): array
    {
        return parent::get("urls");
    }

    public function getComments(): array
    {
        return parent::get("comments");
    }

    public function getCategory(): array
    {
        return parent::get("category");
    }

    public function setCategory(Category $category): self
    {
        parent::set("category", $category);

        return $this;
    }

    public function getStars(): array
    {
        return parent::get("stars");
    }

    public function getCreatedAt(): string
    {
        return parent::get("created_at");
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

    public function getFilm(): array
    {
        return parent::get("film");
    }

    public function getSeries(): array
    {
        return parent::get("series");
    }
}
