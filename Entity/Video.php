<?php

namespace Entity;

use Core\Entity;
use Core\QueryBuilder;

class Video extends Entity
{
    /** @type{int} */
    private int $id;

    public function getId(): int
    {
        return parent::get("id");
    }

    /**
     * @many{Entity\Url,video}
     * @groups{urls}
     * @cascade{}
     */
    private array $urls;

    /**
     * @return Url[];
     */
    public function getUrls(): array
    {
        return parent::get("urls");
    }

    /**
     * @many{Entity\Comment,video}
     * @groups{comments}
     * @cascade{}
     */
    private array $comments;

    /**
     * @return Comment[];
     */
    public function getComments(): array
    {
        return parent::get("comments");
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
}
