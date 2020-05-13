<?php

namespace App\Model\Entity;

use Core\Model\Entity;

class RolePermissionEntity extends Entity
{
    private int $role_id;

    private int $permission_id;

    public function getRoleId(): ?int
    {
        return $this->role_id;
    }

    public function setRoleId($role_id): self
    {
        $this->role_id = $role_id;
        return $this;
    }

    public function getPermissionId(): ?int
    {
        return $this->permission_id;
    }

    public function setPermissionId($permission_id): self
    {
        $this->permission_id = $permission_id;
        return $this;
    }
}
