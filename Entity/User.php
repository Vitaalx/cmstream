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
     */
    private string $firstname;

    /** 
     * @type{VARCHAR(120)} 
     * @notnullable{}
     */
    private string $lastname;

    /**
     * @type{VARCHAR(120)}
     * @notnullable{}
     * @unique{}
     */
    private string $username;

    /**
     * @type{VARCHAR(100)}
     * @notnullable{}
     * @unique{}
     */
    private string $email;

    /**
     * @type{VARCHAR(255)}
     * @notnullable{}
     * @groups{userPassword}
     */
    private string $password;

    private Role $role;

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
     * Get the value of username
     *
     * @return string
     */
    public function getUsername() : string {
        return parent::get("username");
    }

    /**
     * Set the value of username
     *
     * @param string $username
     *
     * @return self
     */
    public function setUsername(string $username): self
    {
        parent::set("username", $username);

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
     * Get the value of role
     *
     * @return string
     */
    public function getRole(): string
    {
        return parent::get("role");
    }

    /**
     * Set the value of role
     *
     * @param string
     *
     * @return self
     */
    public function setRole(string $role): self
    {
        parent::set("role", $role);

        return $this;
    }

    /**
     * Get the value of created_at
     *
     * @return string
     */
    public function getCreatedAt(): string
    {
        return parent::get("created_at");
    }

    /**
     * Get the value of updated_at
     *
     * @return string
     */
    public function getUpdatedAt(): string
    {
        return parent::get("updated_at");
    }
}
