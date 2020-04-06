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
     * 
     * @return void
     */
    public function login()
    {
        $this->anonymousOnly();
        $username = $this->loginVerify();
        if (!empty($_POST['changePwd'])) {
            $this->changeFirstPassword(htmlspecialchars($_POST['username']));
        }

        return $this->render("login", [
            'title' => 'Panel | Connexion',
            'username' => $username
        ]);
    }

    /**
     * Login logic
     * 
     * @return string
     */
    public function loginVerify()
    {
        if (!empty($_POST) && isset($_POST['login'])) {
            $username = htmlspecialchars($_POST['username']);
            $password = htmlspecialchars($_POST['password']);
            $token = htmlspecialchars($_POST['token']);
            /* Check if user exist */
            if ($userEntity = $this->user->select(['username' => $username])) {
                // Set userEntity with hydrated roleEntity
                $userEntity->setRoleEntity($this->role->findById($userEntity->getRoleId()));
                /* Check the password */
                if (password_verify($password, $userEntity->getPassword())) {
                    $calc = hash_hmac('sha256', $_SESSION['route'], $_SESSION['token2']);
                    if (hash_equals($calc, $token)) {
                        /*  Check if its the first connection */
                        if ($userEntity->getDefaultPassword() == 0) {
                            /* Logged! */
                            $_SESSION['username'] = $userEntity->getUsername();
                            $this->redirect('dashboard');
                            exit();
                        } else {
                            $this->changeFirstPassword($userEntity->getUsername());
                        }
                    } else {
                        $this->getFlash()->addAlert('Le post n\'a pas été émis par le bon formulaire.');
                    }
                } else {
                    $this->getFlash()->addAlert('L\'utilisateur ou le mot de passe est incorrect !');
                }
            } else {
                $this->getFlash()->addAlert('L\'utilisateur ou le mot de passe est incorrect !');
            }
            return $username;
        }
    }

    /**
     * Logout the connected user
     * Route: /logout
     *
     * @return void
     */
    public function logout()
    {
        unset($_SESSION['username']);
        session_unset();
        session_destroy();
        $this->redirect('login');
    }

    /**
     * changeFirstPassword
     *
     * @return void
     */
    private function changeFirstPassword(string $username)
    {
        /* Condition is true when called by form on changeFirstPassword view */
        if (!empty($_POST) && isset($_POST['changePwd'])) {
            $password = htmlspecialchars($_POST['password']);
            $password_verify = htmlspecialchars($_POST['password_verify']);
            if ($password === $password_verify) {
                if (strlen($password) >= 8) {
                    $passwordHash = password_hash($password, PASSWORD_ARGON2ID);
                    if ($this->user->updateBy(['username' => $username], [
                        'default_password' => '0',
                        'password' => $passwordHash
                    ])) {
                        /* Logged! */
                        $_SESSION['username'] = $username;
                        unset($_SESSION['token']);
                        $this->redirect('dashboard');
                        exit();
                    }
                } else {
                    $this->getFlash()->addAlert('Votre mot de passe doit être de minimum de 8 caractères');
                }
            }
        }

        /* Render this page when called by loginVerify() */
        return $this->render('changeFirstPassword', [
            'title' => 'Changer votre mot de passe',
            'username' => $username
        ]);
    }
}
