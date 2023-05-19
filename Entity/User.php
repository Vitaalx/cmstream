<?php

namespace Entity;

use Core\Entity;

class User extends Entity
{
    /** @type{int} */
    private int $id;

    /** 
     * @type{VARCHAR(60)} 
     * @notnullable{}
     * @unique{}
     */
    private string $firstname;

    /** 
     * @type{VARCHAR(120)} 
     * @notnullable{}
     */
    private string $lastname;

    /** 
     * @type{VARCHAR(2)} 
     * @notnullable{}
     */
    private string $country;

    /** 
     * @many{Entity\Post,author}
     * @groups{posts}
     */
    private array $posts;

    /**
     * @type{VARCHAR(100)}
     * @notnullable{}
     */
    private string $email;

    /**
     * @type{VARCHAR(255)}
     * @notnullable{}
     */
    private string $password;

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
     * Get the value of firstname
     *
     * @return string
     */
    public function getFirstname(): string
    {
        return parent::get("firstname");
    }

    /**
     * Set the value of firstname
     *
     * @param string $firstname
     *
     * @return self
     */
    public function setFirstname(string $firstname): self
    {
        parent::set("firstname", $firstname);

        return $this;
    }

    /**
     * Get the value of lastname
     *
     * @return string
     */
    public function getLastname(): string
    {
        return parent::get("lastname");
    }

    /**
     * Set the value of lastname
     *
     * @param string $lastname
     *
     * @return self
     */
    public function setLastname(string $lastname): self
    {
        parent::set("lastname", $lastname);

        return $this;
    }

    /**
     * Get the value of email
     *
     * @return string
     */
    public function getEmail(): string
    {
        return parent::get("email");
    }

    /**
     * Set the value of email
     *
     * @param string
     *
     * @return self
     */
    public function setEmail(string $email): self
    {
        parent::set("email", $email);

        return $this;
    }

    /**
     * Get the value of password
     *
     * @return string
     */
    public function getPassword(): string
    {
        return parent::get("password");
    }

    /**
     * Set the value of password
     *
     * @param string
     *
     * @return self
     */
    public function setPassword(string $email): self
    {
        parent::set("password", $email);

        return $this;
    }



    /**
     * Get the value of country
     *
     * @return string
     */
    public function getCountry(): string
    {
        return parent::get("country");
    }

    /**
     * Set the value of country
     *
     * @param string $country
     *
     * @return self
     */
    public function setCountry(string $country): self
    {
        parent::set("country", $country);

        return $this;
    }

    /**
     * Get the value of posts
     *
     * @return array
     */
    public function getPosts(): array
    {
        return parent::get("posts");
    }
}
