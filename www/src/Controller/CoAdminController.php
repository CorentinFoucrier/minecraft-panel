<?php
namespace App\Controller;

use Core\Controller\Controller;

class CoAdminController extends Controller
{
    public function __construct()
    {
        $this->loadModel('user');
    }

    public function showCoAdmin()
    {
        $this->userOnly();
        $this->adminOnly();
        /* From ajax addCoAdmin */
        if (!empty($_POST['coAdminInput'])) {
            $pwd = $this->addCoAdmin();
        }
        $coAdmins = $this->user->select(['role_id' => 2], false); // Every coAdmins
        $token = bin2hex(random_bytes(8));
        $_SESSION['token'] = $token;

        return $this->render('coAdmin', [
            'title' => 'Co-Administration',
            'token' => $token,
            'coAdmins' => $coAdmins,
            'password' => $pwd
        ]);
    }

    /**
     * From AJAX for delete a co-admin
     *
     * @param int $id
     * @param string $token
     * @return void
     */
    public function deleteCoAdmin(int $id, string $token)
    {
        if (!empty($_POST['deleteCoAdmin']) && $token === $_SESSION['token']) {
            if ($this->user->delete($id)) {
                echo 'deleted';
            }
        }
    }

    private function addCoAdmin()
    {
        $coAdminName = $_POST['coAdminInput'];
        $generatedPwd = bin2hex(random_bytes(4));
        $generatedPwdHash = password_hash($generatedPwd, PASSWORD_ARGON2ID);
        $fields = [
            'username' => $coAdminName,
            'password' => $generatedPwdHash,
            'role_id' => 2
        ];
        if ($this->user->create($fields)) {
            $this->getFlash()->addSuccess('L\'utilisateur a bien été ajouté');
            return $generatedPwd;
        } else {
            $this->getFlash()->addAlert('Une erreur est survenue.');
            return false;
        }
    }
}
