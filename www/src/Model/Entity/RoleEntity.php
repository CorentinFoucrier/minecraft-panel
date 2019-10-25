<?php
namespace App\Model\Entity;

use Core\Model\Entity;

class RoleEntity extends Entity
{
    /**
     * @var int $id
     */
    private $id;
    /**
     * @var string $role_name
     */
    private $role_name;

    /**
     * Get the value of id
     * 
     * @return null|int
     */ 
    public function getId(): ?int
    {
        return $this->id;
    }
    /**
     * Set the value of id
     *
     * @return self
     */ 
    public function setId(int $id): self
    {
        $this->id = $id;
        return $this;
    }
    /**
     * Get the value of role.name
     * 
     * @return null|string
     */ 
    public function getRoleName(): ?string
    {
        return $this->role_name;
    }
    /**
     * Set the value of role.name
     *
     * @return self
     */ 
    public function setRoleName(string $role_name): self
    {
        $this->role_name = $role_name;
        return $this;
    }
}