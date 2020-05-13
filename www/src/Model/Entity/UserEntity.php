<?php

namespace App\Model\Entity;

use Core\Model\Entity;

class UserEntity extends Entity
{

    private int $id;

    private string $username;

    private string $password;

    private int $default_password;

    private string $lang;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(int $id): self
    {
        $this->id = $id;
        return $this;
    }

    public function getUsername(): ?string
    {
        return $this->username;
    }

    public function setUsername(string $username): self
    {
        $this->username = $username;
        return $this;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;
        return $this;
    }

    public function getDefaultPassword(): int
    {
        return $this->default_password;
    }

    public function setDefaultPassword($default_password): self
    {
        $this->default_password = $default_password;
        return $this;
    }

    public function getLang(): ?string
    {
        return $this->lang;
    }

    public function setLang(string $lang): self
    {
        $this->lang = $lang;
        return $this;
    }
}
