<?php
namespace Entity;

use Core\Entity;

class Post extends Entity{
    /** @type{int} */
    private int $id;

    /** 
     * @type{VARCHAR(120)} 
     * @notnullable{}
     * @unique{}
    */
    private string $title;

    /** 
     * @type{VARCHAR(240)} 
     * 
    */
    private string $subTitle;

    /**
     * @notnullable{}
     */
    private User $author;

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
     * Get the value of title
     *
     * @return string
     */
    public function getTitle(): string
    {
        return $this->title;
    }

    /**
     * Set the value of title
     *
     * @param string $title
     *
     * @return self
     */
    public function setTitle(string $title): self
    {
        $this->title = $title;

        return $this;
    }

    /**
     * Get the value of author
     *
     * @return User
     */
    public function getAuthor(): User
    {
        return $this->author;
    }

    /**
     * Set the value of author
     *
     * @param User $author
     *
     * @return self
     */
    public function setAuthor(User $author): self
    {
        $this->author = $author;

        return $this;
    }

    /**
     * Get the value of subTitle
     *
     * @return string
     */
    public function getSubTitle(): string
    {
        return $this->subTitle;
    }

    /**
     * Set the value of subTitle
     *
     * @param string $subTitle
     *
     * @return self
     */
    public function setSubTitle(string $subTitle): self
    {
        $this->subTitle = $subTitle;

        return $this;
    }
}