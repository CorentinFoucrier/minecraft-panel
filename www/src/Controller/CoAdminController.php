<?php

namespace App\Controller;

use Core\Controller\Controller;
use Core\Controller\Helpers\GettersController;

class CoAdminController extends Controller
{
    public function __construct()
    {
        $this->loadModel('user', 'permissions');
    }

    public function showCoAdmin()
    {
        $this->userOnly();
        $this->adminOnly();
        /* From ajax addCoAdmin */
        if (!empty(htmlspecialchars($_POST['coAdminInput']))) {
            $pwd = $this->addCoAdmin();
        }
        $coAdmins = $this->user->select(['role_id' => 2], true); // Every coAdmins
        $token = bin2hex(random_bytes(8));
        $_SESSION['token'] = $token;
        for ($i = 0; $i < count($coAdmins); $i++) {
            $permissionEntity = $this->permissions->select(['user_id' => $coAdmins[$i]->getId()]);
            $coAdminsPerms[$coAdmins[$i]->getUsername()] = GettersController::gettersToAssocArray($permissionEntity);
        }

        return $this->render('coAdmin', [
            'title' => 'Co-Administration',
            'token' => $token,
            'coAdmins' => $coAdmins,
            'password' => $pwd,
            'coAdminsPerms' => $coAdminsPerms
        ]);
    }

    /**
     * From AJAX for delete a co-admin
     * Route: /coAdmin/delete/[i:id]/[*:token]
     *
     * @param int $id
     * @param string $token
     * @return void
     */
    public function deleteCoAdmin(int $id, string $token)
    {
        if ($this->adminOnly() === FALSE) {
            echo 'error';
        } else {
            if (!empty(htmlspecialchars($_POST['deleteCoAdmin'])) && $token === $_SESSION['token']) {
                if ($this->user->deleteById($id)) {
                    echo 'deleted';
                }
            }
        }
    }

    /**
     * From AJAX edit permissions
     * Route: /coAdmin/edit
     * 
     * @return void
     */
    public function editPermissions()
    {
        if (!empty($_POST)) {
            $userId = htmlspecialchars($_POST['id']);
            $checked = htmlspecialchars($_POST['checked']);
            $token = htmlspecialchars($_POST['token']);
            $p_name = htmlspecialchars($_POST['name']);
            // Split at each uppercase letter
            $explodedStr = preg_split('/(?=[A-Z])/', $p_name, -1, PREG_SPLIT_NO_EMPTY);
            // Then join the array with an underscore
            $name = strtolower(join("_", $explodedStr));

            if ($token === $_SESSION['token']) {
                $checked = ($checked === "true") ? 1 : 0; // Replace boolean value by 1 or 0 (bdd accepted values)
                if ($this->permissions->update($userId, [$name => $checked])) {
                    echo 'ok';
                } else {
                    echo 'error';
                }
            } else {
                echo 'error';
            }
        }
    }

    private function addCoAdmin(): ?string
    {
        $coAdminName = htmlspecialchars($_POST['coAdminInput']);
        $generatedPwd = bin2hex(random_bytes(4));
        $generatedPwdHash = password_hash($generatedPwd, PASSWORD_ARGON2ID);
        $fields = [
            'username' => $coAdminName,
            'password' => $generatedPwdHash,
            'role_id' => 2
        ];
        if ($this->user->create($fields)) {
            $userId = $this->user->select(['username' => $coAdminName]);
            if ($this->permissions->create(['user_id' => $userId->getId()])) {
                $this->getFlash()->addSuccess('L\'utilisateur a bien été ajouté');
            }
            return $generatedPwd;
        } else {
            $this->getFlash()->addAlert('Une erreur est survenue.');
            return NULL;
        }
    }
}
