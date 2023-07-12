<?php

namespace Entity;

use Core\Entity;
use Core\Logger;

class Serie extends Entity
{
    /** @type{int} */
    private int $id;

    public function getId(): int
    {
        return parent::get("id");
    }

    /** 
     * @many{Entity\Episode,serie}
     * @groups{episodes}
     * @cascade{}
     */
    private array $episodes;

    /**
     * @return Episode[]
     */
    public function getEpisodes(): array
    {
        return parent::get("episodes");
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

    public function getContent(): Content|null
    {
        return Content::findFirst(["value" => $this, "value_type" => "S"]);
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
        if(in_array("content", self::$groups)){
            $array["content"] = $this->getContent();
        }
        return $array;
    }

    protected function onDelete(){
        Content::findFirst(["value_id" => $this->getId(), "value_type" => "S"])->delete();
    }
}
