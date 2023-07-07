<?php

namespace Entity;

use Core\Entity;

class Content extends Entity
{
    /** @type{int} */
    private int $id;

    public function getId(): int
    {
        return parent::get("id");
    }

    /**
     * @many{Entity\Vote,content}
     * @cascade{}
     */
    private array $votes;
    
    public function getUpVotes(): int
    {
        return Vote::count(["content" => $this, "value" => 1]);
    }
    
    public function getDownVotes(): int
    {
        return Vote::count(["content" => $this, "value" => -1]);
    }
    
    /** 
     * @groups{category}
     */
    private Category $category;

    public function getCategory(): Category
    {
        return parent::get("category");
    }

    public function setCategory(Category $category): self
    {
        parent::set("category", $category);
        return $this;
    }

    /** 
     * @type{int}
     * @groups{contentValue}
     * @notnullable{}
     */
    private int $value_id;

    /** 
     * @type{CHAR(1)}
     * @groups{contentValue}
     * @notnullable{}
     */
    private string $value_type;

    public function getValue(): Movie | Serie | null
    {
        if(parent::get("value_type") === "S") return Serie::findFirst(["id" => parent::get("value_id")]);
        else return Movie::findFirst(["id" => parent::get("value_id")]);
    }

    public function setValue(Movie | Serie $value): self
    {
        if($value instanceof Serie)parent::set("value_type", "S");
        else parent::set("value_type", "M");
        parent::set("value_id", $value->getId());
        return $this;
    }

    /**
     * @type{Date}
     * @notnullable{}
     * @default{CURRENT_TIMESTAMP}
     * @groups{dateProps}
     */
    private string $created_at;

    function getCreatedAt(): string
    {
        return parent::get("created_at");
    }

    protected function onSerialize(array $array): array
    {
        if(in_array("vote", self::$groups)){
            $array["up_vote"] = $this->getUpVotes();
            $array["down_vote"] = $this->getDownVotes();
        }
        if(in_array("value", self::$groups)){
            $array["type"] = parent::get("value_type");
            $array["value"] = $this->getValue();
        }
        return $array;
    }
}