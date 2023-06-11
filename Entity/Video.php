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
     * @many{Entity\Star,video}
     * @groups{stars}
     * @cascade{}
     */
    private array $stars;

    /**
     * @many{Entity\Movie,video}
     * @groups{movie}
     * @cascade{}
     */
    private array $movie;

    /**
     * @many{Entity\Episode,video}
     * @groups{episodes}
     * @cascade{}
     */
    private array $episodes;

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

    public function getCategory(): Category
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

    public function getMovie(): array
    {
        return parent::get("movie");
    }

    public function getEpisodes(): array
    {
        return parent::get("episodes");
    }

    public function AvgStars(): int
    {
        $req = $this->getDb()->prepare("SELECT AVG(note) FROM _star WHERE video_id = :video");
        $req->execute([
            "video" => $this->getId()
        ]);
        return intval($req->fetchColumn());
    }
}
