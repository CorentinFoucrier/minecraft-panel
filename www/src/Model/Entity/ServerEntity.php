<?php
namespace App\Model\Entity;

use Core\Model\Entity;

class ServerEntity extends Entity
{
    /**
     * @var int $id
     */
    private $id;

    /**
     * @var string $name
     */
    private $name;

    /**
     * @var int $panel_port
     */
    private $panel_port;

    /**
     * @var int $is_installed
     */
    private $is_installed;

    /**
     * @var int $status
     */
    private $status;

    /**
     * @var string
     */
    private $version;

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
     * Get the value of panel_port
     *
     * @return integer|null
     */
    public function getPanelPort(): ?int
    {
        return $this->panel_port;
    }

    /**
     * Set the value of panel_port
     *
     * @param integer $panel_port
     * @return self
     */
    public function setPanelPort(int $panel_port): self
    {
        $this->panel_port = $panel_port;
        return $this;
    }

    /**
     * Get the value of is_installed
     *
     * @return integer|null
     */
    public function getIsInstalled(): ?int
    {
        return $this->is_installed;
    }

    /**
     * Set the value of is_installed
     *
     * @param integer $is_installed
     * @return self
     */
    public function setIsInstalled(int $is_installed): self
    {
        $this->is_installed = $is_installed;
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
}