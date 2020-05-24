<?php

namespace App\Controller;

use Core\Controller\Controller;
use Symfony\Component\Intl\Locales;

class AccountController extends Controller
{

    public function __construct()
    {
        $this->loadModel('user');
    }

    public function show()
    {
        $this->userOnly();
        $this->render('account', [
            "title" => $this->lang('account.title'),
            "languages" => $this->availableLanguages(),
            "userLocale" => $_SESSION['lang'],
        ]);
    }

    /**
     * Methode: POST
     * Route: /account/change_password
     *
     * @return void
     */
    public function changePassword(): void
    {
        $oldPassword = $_POST['oldPassword'];
        $newPassword = $_POST['newPassword'];
        $passwordVerify = $_POST['passwordVerify'];
        $token = $_POST['token'];
        if ($token === $_SESSION['token']) {
            $user = $this->user->select(["username" => $_SESSION['username']]);
            if (password_verify($oldPassword, $user->getPassword())) {
                if (strlen($newPassword) >= 4) {
                    if ($newPassword === $passwordVerify) {
                        $passwordHash = password_hash($newPassword, PASSWORD_ARGON2ID);
                        if ($this->user->updateBy(['id' => $user->getId()], ['password' => $passwordHash])) {
                            $this->getFlash()->addSuccess($this->lang('account.changePassword.success'));
                        } else {
                            $this->getFlash()->addAlert($this->lang('general.error.database'));
                        }
                    } else {
                        $this->getFlash()->addAlert($this->lang('account.changePassword.error.same'));
                    }
                } else {
                    $this->getFlash()->addAlert($this->lang('account.changePassword.error.short'));
                }
            } else {
                $this->getFlash()->addAlert($this->lang('account.changePassword.error.old'));
            }
        } else {
            $this->getFlash()->addAlert($this->lang('general.error.badToken'));
        }
        $this->redirect('account');
    }

    /**
     * Methode: POST
     * Route: /account/change_language
     *
     * @return void
     */
    public function changeLanguage(): void
    {
        $locale = $_POST['locale'];
        $token = $_POST['token'];
        if ($token === $_SESSION['token']) {
            if (Locales::exists($locale)) {
                $user = $this->user->select(["username" => $_SESSION['username']]);
                if ($this->user->updateBy(['id' => $user->getId()], ['lang' => $locale])) {
                    $_SESSION['lang'] = $locale;
                    $this->getFlash()->addSuccess($this->lang('account.changeLanguage.success'));
                } else {
                    $this->getFlash()->addAlert($this->lang('general.error.database'));
                }
            } else {
                $this->getFlash()->addAlert($this->lang('general.error.internal'));
            }
        } else {
            $this->getFlash()->addAlert($this->lang('general.error.badToken'));
        }
        $this->redirect('account');
    }

    /**
     * Methode: POST
     * Route: /account/delete
     *
     * @return void
     */
    public function delete(): void
    {
        $token = $_POST['token'];
        if ($token === $_SESSION['token']) {
            $user = $this->user->select(["username" => $_SESSION['username']]);
            if ($user->getId() !== 1) {
                if ($this->user->deleteById($user->getId())) {
                    $this->getFlash()->addSuccess($this->lang('account.delete.success'));
                    unset($_SESSION);
                    session_unset();
                    session_destroy();
                    $this->redirect('login');
                } else {
                    $this->getFlash()->addAlert($this->lang('general.error.database'));
                    $this->redirect('account');
                }
            } else {
                $this->getFlash()->addAlert($this->lang('account.delete.error.owner'));
            }
        } else {
            $this->getFlash()->addAlert($this->lang('general.error.badToken'));
            $this->redirect('account');
        }
    }

    private function availableLanguages(): array
    {
        $langFiles = scandir(BASE_PATH . "www/lang");
        $languages = [];
        for ($i = 0; $i < count($langFiles); $i++) {
            if ($langFiles[$i] !== "." && $langFiles[$i] !== "..") {
                $locale = substr($langFiles[$i], 0, -5);
                $languages[$locale] = ucfirst(Locales::getName($locale, $locale));
            }
        }
        return $languages;
    }
}
