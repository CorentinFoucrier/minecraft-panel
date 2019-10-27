<?php
namespace App\Controller;

use Core\Controller\Controller;

class UserController extends Controller
{
    public function __construct()
    {
        $this->loadModel('user');
        $this->loadModel('role');
    }
    /**
     * Login form
     * Route: /login
     */
    public function login()
    {
        if (isset($_SESSION['user'])) {
            // Already logged in!
            $this->redirect('/', 200);
            exit();
        }
        if (!empty($_POST)) {
            $username = $_POST['username'];
            $password = $_POST['password'];
            $token = $_POST['token'];
            /* Check if user exist */
            if ($userEntity = $this->user->select(['username' => $username])) {
                // Set userEntity with hydrated roleEntity
                $userEntity->setRoleEntity($this->role->findById($userEntity->getRoleId()));
                /* Check the password */
                if (password_verify($password, $userEntity->getPassword())) {
                    if ($_SESSION['token'] === $token) {
                        /* Logged! */
                        $_SESSION['user'] = $userEntity->getUsername();
                        $this->redirect("/", 200);
                        exit();
                    } else {
                        $this->getFlash()->addAlert('Le post n\'a pas été émis pas le bon formulaire.');
                    }
                } else {
                    $this->getFlash()->addAlert('L\'utilisateur ou le mot de passe est incorrect !');
                }
            } else {
                $this->getFlash()->addAlert('L\'utilisateur ou le mot de passe est incorrect !');
            }
        }
        $token = bin2hex(random_bytes(32));
        $_SESSION['token'] = $token;
        return $this->render("login", [
            'title' => 'Panel | Connexion',
            'username' => $username,
            'token' => $token
        ]);
    }
}