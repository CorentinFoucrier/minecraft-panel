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
                        // Check if its the first connection
                        if ($userEntity->getDefaultPassword() === 0) {
                            // Logged!
                            $_SESSION['lang'] = $userEntity->getLang();
                            $_SESSION['username'] = $userEntity->getUsername();
                            $this->redirect('dashboard');
                        } else {
                            $_SESSION['temp']['username'] = $username;
                            $_SESSION['temp']['changeYourPassword'] = true;
                            $this->redirect('login');
                        }
                    } else {
                        $this->getFlash()->addAlert($this->lang('general.error.badToken'));
                        $this->redirect('login');
                    }
                } else {
                    $this->getFlash()->addAlert($this->lang('user.loginCheck.error.incorrect'));
                    $this->redirect('login');
                }
            } else {
                $this->getFlash()->addAlert($this->lang('user.loginCheck.error.incorrect'));
                $this->redirect('login');
            }
        } else {
            $this->getFlash()->addAlert($this->lang('user.loginCheck.error.incorrect'));
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
            $pwdMinLength = 4;
            if (hash_equals($calc, $token)) {
                if ($password === $password_verify) {
                    if (strlen($password) >= $pwdMinLength) {
                        $passwordHash = password_hash($password, PASSWORD_ARGON2ID);
                        if ($this->user->updateBy(['username' => $username], [
                            'default_password' => '0',
                            'password' => $passwordHash
                        ])) {
                            // Logged!
                            $id = $this->user->lastId();
                            $user = $this->user->findById($id);
                            $_SESSION['lang'] = $user->getLang();
                            $_SESSION['username'] = $user->getUsername();
                            unset($_SESSION['temp']);
                            $this->redirect('dashboard');
                        }
                    } else {
                        $this->getFlash()->addAlert($this->lang('user.changeDefaultPassword.error.minLength', $pwdMinLength));
                        $this->redirect('login');
                    }
                } else {
                    $this->getFlash()->addAlert($this->lang("user.changeDefaultPassword.error.identical"));
                    $this->redirect('login');
                }
            } else {
                $this->getFlash()->addAlert($this->lang('general.error.badToken'));
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
