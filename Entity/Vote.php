<?php

namespace Entity;

use Core\Entity;

class Vote extends Entity
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
     */
    private int $value;

    public function getValue(): int
    {
        return parent::get("value");
    }

    public function setValue(int $value): self
    {
        parent::set("value", $value);
        return $this;
    }

    /** 
     * 
     */
    private Movie $movie;

    public function getMovie(): Movie
    {
        return parent::get("movie");
    }

    public function setMovie(Movie $movie): self
    {
        parent::set("movie", $movie);
        return $this;
    }

    /** 
     * 
     */
    private Serie $serie;

    public function getSerie(): Serie
    {
        return parent::get("serie");
    }

    public function setSerie(Serie $serie): self
    {
        parent::set("serie", $serie);
        return $this;
    }

    /** 
     * @notnullable{}
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