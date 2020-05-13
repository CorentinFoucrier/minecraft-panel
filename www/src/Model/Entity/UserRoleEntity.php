<?php

namespace App\Model\Entity;

use Core\Model\Entity;

class UserRoleEntity extends Entity
{
    private int $user_id;

    private int $role_id;

    public function getUserId(): ?int
    {
        return $this->user_id;
    }

    public function setUserId($user_id): self
    {
        $this->user_id = $user_id;
        return $this;
    }

    public function getRoleId(): ?int
    {
        return $this->role_id;
    }

    public function setRoleId($role_id): self
    {
        $this->role_id = $role_id;
        return $this;
    }
}
