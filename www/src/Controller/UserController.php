<?php

namespace App\Controller;

use Core\Controller\Controller;

class UserController extends Controller
{

    public function __construct()
    {
        $this->loadModel('user');
    }

    /**
     * Login form
     * Route: /login
     * 
     * @return void
     */
    public function showLogin()
    {
        $this->anonymousOnly();
        $changePassword = false;

        if ($_SESSION['temp']['changeYourPassword']) {
            $changePassword = true;
        }

        return $this->render("login/form", [
            "changePassword" => $changePassword
        ]);
    }

    /**
     * Route: /login_check
     *
     * @return void
     */
    public function loginCheck()
    {
        if (!empty($_POST)) {
            $username = htmlspecialchars($_POST['username']);
            $password = htmlspecialchars($_POST['password']);
            $token = htmlspecialchars($_POST['token']);
            // Check if user exist
            if ($userEntity = $this->user->select(['username' => $username])) {
                // Check the password
                if (password_verify($password, $userEntity->getPassword())) {
                    $calc = hash_hmac('sha256', 'login', $_SESSION['token2']);
                    // Check token
                    if (hash_equals($calc, $token)) {
                        //  Check if its the first connection
                        if ($userEntity->getDefaultPassword() === 0) {
                            // Logged!
                            $_SESSION['username'] = $userEntity->getUsername();
                            $this->redirect('dashboard');
                        } else {
                            $_SESSION['temp']['username'] = $username;
                            $_SESSION['temp']['changeYourPassword'] = true;
                            $this->redirect('login');
                        }
                    } else {
                        $this->getFlash()->addAlert('Internal server error - Bad token');
                        $this->redirect('login');
                    }
                } else {
                    $this->getFlash()->addAlert('Incorrect username or password.');
                    $this->redirect('login');
                }
            } else {
                $this->getFlash()->addAlert('Incorrect username or password.');
                $this->redirect('login');
            }
        } else {
            $this->getFlash()->addAlert('Incorrect username or password.');
            $this->redirect('login');
        }
    }

    /**
     * Route: /change_default_password
     *
     * @return void
     */
    public function changeDefaultPassword()
    {
        if (!empty($_POST)) {
            $username = $_SESSION['temp']['username'];
            $password = htmlspecialchars($_POST['new_password']);
            $password_verify = htmlspecialchars($_POST['password_verify']);
            $token = htmlspecialchars($_POST['token']);
            $calc = hash_hmac('sha256', 'login', $_SESSION['token2']);
            if (hash_equals($calc, $token)) {
                if ($password === $password_verify) {
                    if (strlen($password) >= 4) {
                        $passwordHash = password_hash($password, PASSWORD_ARGON2ID);
                        if ($this->user->updateBy(['username' => $username], [
                            'default_password' => '0',
                            'password' => $passwordHash
                        ])) {
                            /* Logged! */
                            $_SESSION['username'] = $username;
                            unset($_SESSION['temp']);
                            $this->redirect('dashboard');
                        }
                    } else {
                        $this->getFlash()->addAlert('Password must have at least 4 characters!');
                        $this->redirect('login');
                    }
                } else {
                    $this->getFlash()->addAlert('Passwords are not identical!');
                    $this->redirect('login');
                }
            } else {
                $this->getFlash()->addAlert('Internal server error - Bad token');
                $this->redirect('login');
            }
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
        unset($_SESSION);
        session_unset();
        session_destroy();
        $this->redirect('login');
    }
}
