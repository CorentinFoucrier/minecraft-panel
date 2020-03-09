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

    /**
     * Get the value of id
     *
     * @return integer|null
     */
    public function getId(): ?int
    {
        return $this->id;
    }
    /**
     * Set the value of id
     *
     * @param integer $id
     * @return self
     */
    public function setId(int $id): self
    {
        $this->id = $id;
        return $this;
    }
    /**
     * Get the value of name
     *
     * @return string|null
     */
    public function getName(): ?string
    {
        return $this->name;
    }
    /**
     * Set the value of name
     *
     * @param string $name
     * @return self
     */
    public function setName(string $name): self
    {
        $this->name = $name;
        return $this;
    }
    /**
     * Get the value of status
     *
     * @return integer|null
     */
    public function getStatus(): ?int
    {
        return $this->status;
    }
    /**
     * Set the value of status
     *
     * @param integer $status
     * @return self
     */
    public function setStatus(int $status): self
    {
        $this->status = $status;
        return $this;
    }
    /**
     * Get the value of version
     *
     * @return string|null
     */
    public function getVersion(): ?string
    {
        return $this->version;
    }
    /**
     * Set the value of version
     *
     * @param string $version
     * @return self
     */
    public function setVersion(int $version): self
    {
        $this->version = $version;
        return $this;
    }
    /**
     * Get the value of ram_min
     *
     * @return integer|null
     */
    public function getRamMin(): ?int
    {
        return $this->ram_min;
    }
    /**
     * Set the value of ram_min
     *
     * @param integer $ram_min
     * @return self
     */
    public function setRamMin(int $ram_min): self
    {
        $this->ram_min = $ram_min;
        return $this;
    }
    /**
     * Get the value of ram_max
     *
     * @return integer|null
     */
    public function getRamMax(): ?int
    {
        return $this->ram_max;
    }
    /**
     * Set the value of ram_max
     *
     * @param integer $ram_max
     * @return self
     */
    public function setRamMax(int $ram_max): self
    {
        $this->ram_max = $ram_max;
        return $this;
    }
}
