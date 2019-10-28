<?php
namespace App\Model\Entity;

use Core\Model\Entity;
use App\Model\Entity\RoleEntity;

class UserEntity extends Entity
{
    /**
     * @var int $id
     */
    private $id;

    /**
     * @var string $username
     */
    private $username;

    /**
     * @var string $password
     */
    private $password;

    /**
     * @var int $role_id
     */
    private $role_id;

    /**
     * @var RoleEntity $RoleEntity
     */
    private $RoleEntity;

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
     * Get the value of username
     * 
     * @return null|string
     */ 
    public function getUsername(): ?string
    {
        return $this->username;
    }
    /**
     * Set the value of username
     *
     * @return self
     */ 
    public function setUsername(string $username): self
    {
        $this->username = $username;
        return $this;
    }
    /**
     * Get the value of password
     * 
     * @return null|string
     */ 
    public function getPassword(): ?string
    {
        return $this->password;
    }
    /**
     * Set the value of password
     *
     * @return self
     */ 
    public function setPassword(string $password): self
    {
        $this->password = $password;
        return $this;
    }

    /**
     * Get the value of roleId
     * 
     * @return int
     */ 
    public function getRoleId(): int
    {
        return $this->role_id;
    }
    /**
     * Set the value of roleId
     *
     * @return  self
     */ 
    public function setRoleId($role_id): self
    {
        $this->role_id = $role_id;
        return $this;
    }
    /**
     * Get the value of RoleEntity
     * 
     * @return RoleEntity
     */ 
    public function getRoleEntity(): RoleEntity
    {
        return $this->RoleEntity;
    }
    /**
     * Set the value of RoleEntity
     *
     * @return  self
     */ 
    public function setRoleEntity(RoleEntity $RoleEntity): self
    {
        $this->RoleEntity = $RoleEntity;
        return $this;
    }
}