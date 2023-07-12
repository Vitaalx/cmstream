<?php

namespace Entity;

use Core\Entity;

class History extends Entity
{
    /** @type{int} */
    private int $id;

    public function getId(): int
    {
        return parent::get("id");
    }

    /**
     * @groups{users}
     */
    private User $user;

    public function getUser(): User
    {
        return parent::get("user");
    }

    public function setUser(User $user): self
    {
        parent::set("user", $user);

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

    public function getValue(): Movie | Episode
    {
        if(parent::get("value_type") === "E") return Episode::findFirst(["id" => parent::get("value_id")]);
        else return Movie::findFirst(["id" => parent::get("value_id")]);
    }

    public function setValue(Movie | Episode $value): self
    {
        if($value instanceof Episode)parent::set("value_type", "E");
        else parent::set("value_type", "M");
        parent::set("value_id", $value->getId());
        return $this;
    }

    /** 
     * @type{VARCHAR(50)}
     * @groups{uniqueKey}
     * @notnullable{}
     * @unique{}
     */
    private int $unique_key;

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

    protected function onSave(bool $isInsert): void
    {
        parent::set(
            "unique_key",
            parent::get("value_id") . "_" .  parent::get("value_type") . "_" . parent::get("user_id")
        );
    }

    protected function onSerialize(array $array): array
    {
        if(in_array("HistoryValue", self::$groups)){
            $array["type"] = parent::get("value_type");
            $array["value"] = $this->getValue();
        }
        return $array;
    }
}