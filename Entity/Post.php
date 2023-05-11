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
    private ?string $subtitle;

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
        return parent::getId();
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
    protected function setAuthor(User $author): self
    {
        $this->author = $author;

        return $this;
    }

    /**
     * Get the value of subTitle
     *
     * @return string
     */
    public function getSubTitle(): ?string
    {
        return $this->subtitle;
    }

    /**
     * Set the value of subTitle
     *
     * @param string $subTitle
     *
     * @return self
     */
    public function setSubtitle(?string $subTitle): self
    {
        $this->subtitle = $subTitle;

        return $this;
    }
}