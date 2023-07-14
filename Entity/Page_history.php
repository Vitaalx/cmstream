<?php

namespace Entity;

use Core\Entity;

class Page_history extends Entity
{
    /** @type{int} */
    private int $id;

    public function getId(): int
    {
        return parent::get("id");
    }

    /**
     * @type{int}
     * @notnullable{}
     * @default{date_part('epoch'::text, now())}
     */
    private int $timestamp;

    /**
     * @type{text}
     * @notnullable{}
     * @groups{PageHistoryValue}
     */
    private string $value;
    public function getValue(): string
    {
        return parent::get("value");
    }

    public function setValue(string $value): self
    {
        parent::set("value", $value);
        return $this;
    }
}