<?php

namespace Entity;

use Core\Entity;

class Watchlist extends Entity
{
    /** @type{int} */
    private int $id;

    private Film $film;

    private Serie $serie;

    /**
     * @notnullable{}
     */
    private User $user;

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

    /**
     * Get the value of id
     *
     * @return int
     */
    public function getId(): int
    {
        return parent::get("id");
    }

    /**
     * Get the value of user
     */
    public function getUser(): User
    {
        return parent::get("user");
    }

    /**
     * Get the value of created_at
     */
    public function getCreatedAt(): string
    {
        return parent::get("created_at");
    }

    /**
     * Get the value of updated_at
     */
    public function getUpdatedAt(): string
    {
        return parent::get("updated_at");
    }

    /**
     * Set the value of user
     *
     * @return  self
     */
    public function setUser(User $user): self
    {
        parent::set("user", $user);

        return $this;
    }

    /**
     * Set the value of created_at
     *
     * @return  self
     */
    public function setCreatedAt(string $created_at): self
    {
        parent::set("created_at", $created_at);

        return $this;
    }

    /**
     * Set the value of updated_at
     *
     * @return  self
     */

    public function setUpdatedAt(string $updated_at): self
    {
        parent::set("updated_at", $updated_at);

        return $this;
    }

    /**
     * Get the value of film
     */

    public function getFilm(): Film
    {
        return parent::get("film");
    }

    /**
     * Set the value of film
     *
     * @return  self
     */

    public function setFilm(Film $film): self
    {
        parent::set("film", $film);

        return $this;
    }

    /**
     * Get the value of serie
     */

    public function getSerie(): Serie
    {
        return parent::get("serie");
    }

    /**
     * Set the value of serie
     *
     * @return  self
     */
    public function setSerie(Serie $serie): self
    {
        parent::set("serie", $serie);

        return $this;
    }
}
