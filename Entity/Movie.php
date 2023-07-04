<?php

namespace Entity;

use Core\Entity;

class Movie extends Entity
{
    /** @type{int} */
    private int $id;

    public function getId(): int
    {
        return parent::get("id");
    }

    /** 
     * @notnullable{}
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
     * @type{VARCHAR(255)}
     * @notnullable{}
     */
    private string $image;

    public function getImage(): string
    {
        return parent::get("image");
    }

    public function setImage(string $image): self
    {
        parent::set("image", $image);
        return $this;
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
     * @groups{category}
     */
    private Category $category;

    public function setCategory(Category $category): self
    {
        parent::set("category", $category);
        return $this;
    }

    public function getCategory(): Category
    {
        return parent::get("category");
    }

    /**
     * @many{Entity\Vote,movie}
     * @cascade{}
     */
    private array $votes;
    
    public function getUpVotes(): int
    {
        return Vote::count(["movie" => $this, "value" => 1]);
    }
    
    public function getDownVotes(): int
    {
        return Vote::count(["movie" => $this, "value" => 0]);
    }

    /**
     * @type{Date}
     * @notnullable{}
     */
    private string $release_date;

    public function getReleaseDate(): string
    {
        return parent::get("release_date");
    }

    public function setReleaseDate(string $release_date): self
    {
        parent::set("release_date", $release_date);
        return $this;
    }

    /**
     * @type{Date}
     * @notnullable{}
     * @default{CURRENT_TIMESTAMP}
     * @groups{dateProps}
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
     * @groups{dateProps}
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

    protected function onSerialize(array $array): array
    {
        if(in_array("vote", self::$groups)){
            $array["up_vote"] = $this->getUpVotes();
            $array["down_vote"] = $this->getDownVotes();
        }
        return $array;
    }
}
