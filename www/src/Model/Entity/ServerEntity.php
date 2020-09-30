<?php

namespace App\Model\Entity;

use Core\Model\Entity;

class ServerEntity extends Entity
{

    private int $id;

    private string $name;

    private int $status;

    private string $version;

    private int $ram_min;

    private int $ram_max;

    private int $last_update;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(int $id): self
    {
        $this->id = $id;
        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;
        return $this;
    }

    public function getStatus(): ?int
    {
        return $this->status;
    }

    public function setStatus(int $status): self
    {
        $this->status = $status;
        return $this;
    }

    public function getVersion(): ?string
    {
        return $this->version;
    }

    public function setVersion(string $version): self
    {
        $this->version = $version;
        return $this;
    }

    public function getRamMin(): ?int
    {
        return $this->ram_min;
    }

    public function setRamMin(int $ram_min): self
    {
        $this->ram_min = $ram_min;
        return $this;
    }

    public function getRamMax(): ?int
    {
        return $this->ram_max;
    }

    public function setRamMax(int $ram_max): self
    {
        $this->ram_max = $ram_max;
        return $this;
    }

    public function getLastUpdate(): ?int
    {
        return $this->last_update;
    }

    public function setLastUpdate(int $last_update): self
    {
        $this->last_update = $last_update;
        return $this;
    }
}
