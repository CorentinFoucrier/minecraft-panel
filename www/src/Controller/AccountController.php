<?php

namespace App\Controller;

use App\App;
use Core\Controller\Controller;
use Symfony\Component\Intl\Locales;

class AccountController extends Controller
{

    public function __construct()
    {
        $this->loadModel('user');
    }

    /**
     * Methode: POST
     * Route: /api/account/change_password
     *
     * @return void
     */
    public function changePassword(): void
    {
        $oldPassword = htmlspecialchars($_POST['oldPassword']);
        $newPassword = htmlspecialchars($_POST['newPassword']);
        $passwordVerify = htmlspecialchars($_POST['passwordVerify']);

        /** @var \App\Model\Table\UserTable */
        $userTable = $this->user;
        /** @var \App\Model\Entity\UserEntity */
        $user = $userTable->select(["username" => $_SESSION['username']]);
        if (password_verify($oldPassword, $user->getPassword())) {
            if (strlen($newPassword) >= 4) {
                if ($newPassword === $passwordVerify) {
                    $passwordHash = password_hash($newPassword, PASSWORD_ARGON2ID);
                    if ($this->user->updateBy(['id' => $user->getId()], ['password' => $passwordHash])) {
                        $this->toast("account.changePassword.success");
                    } else {
                        $this->toast("general.error.database", "general.error.occured", 500);
                    }
                } else {
                    $this->jsonResponse(["message" => $this->lang("account.changePassword.error.same"), "title" => "same"], 400);
                }
            } else {
                $this->jsonResponse(["message" => $this->lang("account.changePassword.error.short"), "title" => "short"], 400);
            }
        } else {
            $this->jsonResponse(["message" => $this->lang("account.changePassword.error.old"), "title" => "old"], 400);
        }
    }

    /**
     * Methode: POST
     * Route: /api/account/change_language
     *
     * @return void
     */
    public function changeLanguage(): void
    {
        $locale = htmlspecialchars($_POST['locale']);

        if (empty($locale)) {
            $this->toast("account.changeLanguage.success");
        };

        if (Locales::exists($locale)) {
            $user = $this->user->select(["username" => $_SESSION['username']]);
            if ($this->user->updateBy(['id' => $user->getId()], ['lang' => $locale])) {
                $_SESSION['lang'] = $locale;
                $this->toast("account.changeLanguage.success");
            } else {
                $this->toast("general.error.database", null, 500);
            }
        } else {
            $this->toast("general.error.internal", null, 500);
        }
    }

    /**
     * Delete the current logged in account
     * Methode: DELETE
     * Route: /api/account/{username}
     *
     * @return void
     */
    public function delete(string $username): void
    {
        $userEntity = $this->user->select(["username" => $username]);
        // Check if current user isn't the owner account
        if ($userEntity->getId() !== 1) {
            if ($this->user->deleteById($userEntity->getId())) {
                unset($_SESSION);
                session_unset();
                session_destroy();
                $this->toast("account.delete.success");
            } else {
                $this->toast("general.error.database", null, 500);
            }
        } else {
            $this->toast("account.delete.error.owner", null, 400);
        }
    }

    /**
     * Get all available languages based on language files that we have in /www/lang folder
     * ROUTE: GET /api/languages
     *
     * @return void
     */
    public function availableLanguages(): void
    {
        $langFiles = scandir(BASE_PATH . "www/lang");
        $languages = [];
        for ($i = 0; $i < count($langFiles); $i++) {
            if ($langFiles[$i] !== "." && $langFiles[$i] !== "..") {
                $locale = substr($langFiles[$i], 0, -5);
                $languages[$locale] = ucfirst(Locales::getName($locale, $locale));
            }
        }
        if (!empty($languages)) {
            $this->jsonResponse($languages);
        } else {
            $this->toast("general.error.internal", null, 500);
        }
    }
}
