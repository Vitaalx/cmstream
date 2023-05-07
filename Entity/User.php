<?php
namespace Entity;

use Core\Entity;

class User extends Entity{
    /** @type{int} */
    private int $id;

    /** @type{VARCHAR(60)} */
    private string $firstname;

    /** @type{VARCHAR(120)} */
    private string $lastname;

    /** @type{VARCHAR(2)} */
    private string $country;

    /** @join{Entity\Post,author} */
    private array $posts;

    /**
     * Get the value of id
     *
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * Set the value of id
     *
     * @param int $id
     *
     * @return self
     */
    public function setId(int $id): self
    {
        $this->id = $id;

        return $this;
    }

    /**
     * Get the value of firstname
     *
     * @return string
     */
    public function getFirstname(): string
    {
        return $this->firstname;
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
        $this->firstname = $firstname;

        return $this;
    }

    /**
     * Get the value of lastname
     *
     * @return string
     */
    public function getLastname(): string
    {
        return $this->lastname;
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
        $this->lastname = $lastname;

        return $this;
    }

    /**
     * Get the value of country
     *
     * @return string
     */
    public function getCountry(): string
    {
        return $this->country;
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
        $this->country = $country;

        return $this;
    }

    /**
     * Get the value of posts
     *
     * @return array
     */
    public function getPosts(): array
    {
        return $this->posts;
    }

    /**
     * Set the value of posts
     *
     * @param array $posts
     *
     * @return self
     */
    public function setPosts(array $posts): self
    {
        $this->posts = $posts;

        return $this;
    }
}