<?php

namespace Core\Controller;

use App\App;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;
use Symfony\Component\Intl\Locales;
use Core\Twig\Filter\HumanizeFilter;
use Core\Twig\Extension\URIExtension;
use Core\Twig\Extension\LangExtension;
use Core\Twig\Extension\FlashExtension;
use Core\Controller\Session\FlashService;
use Core\Twig\Extension\CsrfTokenExtension;
use Core\Controller\Services\JsonDataService;
use Core\Controller\Services\CsrfTokenService;

abstract class Controller
{

    private Environment $twig;

    private CsrfTokenService $csrfToken;

    protected string $views = BASE_PATH . "www/views/";

    protected string $viewsExtention = ".html.twig";

    /**
     * Render the HTML view of a .twig file
     * This is the end of application from entrypoint index.php
     *
     * @param string $view
     * @param array $variables
     * @return void
     */
    protected function render(string $view, array $variables = []): void
    {
        echo $this->getTwig()->render(
            $view . $this->viewsExtention,
            $variables
        );
    }

    /**
     * Init and configure a new Twig\Environment object
     *
     * @see https://twig.symfony.com/doc/2.x/api.html
     * @return Environment
     */
    private function getTwig(): Environment
    {
        if (!isset($this->twig)) {
            $loader = new FilesystemLoader($this->views);
            $this->twig = new Environment($loader);
            //Global
            if ($_SESSION['username']) {
                $l = $_SESSION['lang'];
                $this->twig->addGlobal('currentUser', $_SESSION['username']);
                $this->twig->addGlobal('selectedLang', ucfirst(Locales::getName($l, $l)));
                $this->twig->addGlobal('htmlLang', substr($l, 0, strpos($l, '_', 0)));
            }
            $this->twig->addGlobal('route', $_SESSION['route']);
            $this->twig->addGlobal('ENV_DEV', getenv('ENV_DEV'));
            $this->twig->addGlobal('DEBUG_TIME', round(microtime(true) - START_DEBUG_TIME, 3));
            //Extension
            $this->twig->addExtension(new URIExtension());
            $this->twig->addExtension(new FlashExtension());
            $this->twig->addExtension(new CsrfTokenExtension($this->getCsrfTokenService()));
            $this->twig->addExtension(new LangExtension($this));
            //Filter
            $this->twig->addExtension(new HumanizeFilter());
        }
        return $this->twig;
    }

    /**
     * Used to instantiate a model class in src/Model/Table
     * Called by this->loadModel in any Controller who extents of Core\Controller
     *
     * @param string $tableName
     * @return void
     */
    protected function loadModel(string ...$tableNames): void
    {
        // Add properties dynamically to Core\Controller as many time as loadModel is called who contain object of tableName
        foreach ($tableNames as $tableName) {
            $this->$tableName = App::getInstance()->getTable(ucfirst($tableName));
        }
    }

    /**
     * Get a FlashService for put flash messages in $_SESSION
     * getFlash()->addAlert('Custom alert message')
     *
     * @return FlashService
     */
    protected function getFlash(): FlashService
    {
        return App::getInstance()->getFlash();
    }

    /**
     * Get the entire Uri eg. http://localhost/foo/bar/1
     *
     * @param string $routeName
     * @param array $params Assoc array ['paramName'=>'value']
     * @return string
     */
    protected function getUri(string $routeName, array $params = []): string
    {
        return URLController::getUri($routeName, $params);
    }

    /**
     * Redirect client to a specific route name.
     *
     * @param string $url
     * @param string $getParameter set get parameter eg. http://local/home?page=2 or an anchor eg. http://local/home#contact
     * @return void
     */
    protected function redirect(string $routeName, string $getParameter = "")
    {
        return header('Location: ' . $this->getUri($routeName) . $getParameter);
    }

    /**
     * Redirect with an error code
     *
     * @param integer $code
     * @return void
     */
    protected function error(int $code)
    {
        return header('Location: ' . $this->getUri('error', ['code' => $code]));
    }

    /**
     * Redirect the visitor if he is not logged in.
     *
     * @return void
     */
    protected function userOnly(): void
    {
        if (!isset($_SESSION['username']) || empty($_SESSION['username'])) {
            $this->redirect('login');
            exit();
        }
    }

    /**
     * Redirect the visitor if he is not logged as Admin.
     *
     * @return void
     */
    protected function adminOnly()
    {
        $this->loadModel('user');
        $user = $this->user->select(['username' => $_SESSION['username']]);
        if ($user && ($user->getRoleId() !== 1)) {
            if (!empty($_POST)) { // AJAX if $_POST
                return false;
            } else {
                $this->redirect('login');
            }
        }
    }

    /**
     * Redirect connected users.
     *
     * @return void
     */
    protected function anonymousOnly()
    {
        if (isset($_SESSION['username']) && !empty($_SESSION['username'])) {
            $this->redirect('dashboard');
        }
    }

    /**
     * Remove a directory in recursive mode.
     *
     * @see https://www.php.net/manual/fr/function.rmdir.php#98622
     * @param string $dirPath Directory path you want to remove recursivly.
     * @return bool
     */
    protected function rmDirectoryRecursivly(string $dirPath): bool
    {
        if (is_dir($dirPath)) {
            $objects = scandir($dirPath);
            foreach ($objects as $object) {
                if ($object != "." && $object != "..") {
                    if (is_dir($dirPath . "/" . $object) && !is_link($dirPath . "/" . $object)) {
                        $this->rmDirectoryRecursivly($dirPath . "/" . $object);
                    } else {
                        unlink($dirPath . "/" . $object);
                    }
                }
            }
            return rmdir($dirPath);
        }
    }

    /**
     * Check if the connected user has the given permission.
     *
     * @param string $permissionName Permission name in snake_case
     * @param bool $redirect Turn to false to get a boolean return of permission
     * @return void|bool
     */
    protected function hasPermission(string $permissionName, bool $redirect = true)
    {
        $this->loadModel('user');
        $tables = ["user_role", "role_permission", "permission"];
        $on = [
            "id" => "user_id",
            "role_id" => "role_id",
            "permission_id" => "id"
        ];
        $where = [
            "username" => $_SESSION['username'],
            "name" => $permissionName
        ];
        $perm = ($this->user->join($tables, $on, $where)) ? true : false;
        if ($redirect && $perm === false) {
            $this->error(403);
        } else {
            return $perm;
        }
    }

    /**
     * Get the role entity of every users
     *
     * @return mixed
     */
    protected function getUsersRole()
    {
        $tables = ["user_role", "role"];
        $on = [
            "id" => "user_id",
            "role_id" => "id"
        ];

        return $this->user->join($tables, $on, null, true);
    }

    /**
     * Get the role entity of specific user
     *
     * @return mixed
     */
    protected function getUserRole(string $username)
    {
        $tables = ["user_role", "user"];
        $on = [
            "id" => "role_id",
            "user_id" => "id"
        ];
        $where = ["username" => $username];

        return $this->role->join($tables, $on, $where);
    }

    /**
     * Get role entity of current logged user
     *
     * @return mixed
     */
    protected function currentRole(string $roleGetter)
    {
        $getter = "get" . ucfirst(strtolower($roleGetter));
        return $this->getUserRole($_SESSION['username'])->$getter();
    }

    /**
     * Send the command to the Minecraft Console via SSH protocol
     *
     * @see https://theterminallife.com/sending-commands-into-a-screen-session/
     * @param string $command The Minecraft command to send
     * @return void
     */
    protected function sendMinecraftCommand(string $command): void
    {
        $command = str_replace(['\'', '"'], ['\\u0027', '\\u0022'], $command); // Replace quotes by thier respective unicodes.
        $ssh = App::getInstance()->getSsh();
        $ssh->exec("screen -S minecraft_server -X stuff '${command}'$(echo -ne '\\015')");
    }

    /**
     * Send the command in sudo mode
     *
     * @param string $command
     * @return boolean TRUE on success FALSE on failure
     */
    protected function sendSudoCommand(string $command): bool
    {
        $ssh = App::getInstance()->getSsh();
        $ssh->read('/.*@.*[$|#]/', $ssh::READ_REGEX);
        $ssh->write("sudo $command\n");
        $ssh->setTimeout(10);
        $output = $ssh->read('/.*@.*[$|#]|.*[pP]assword.*/', $ssh::READ_REGEX);
        if (preg_match('/.*[pP]assword.*/', $output)) {
            $ssh->write(SHELL_PWD . PHP_EOL);
            $ssh->read('/.*@.*[$|#]/', $ssh::READ_REGEX);
            return true;
        } else {
            return false;
        }
    }

    protected function echoJsonData(string $state): JsonDataService
    {
        return new JsonDataService($state);
    }

    protected function getCsrfTokenService(): CsrfTokenService
    {
        if (!isset($this->csrfToken)) {
            $this->csrfToken = new CsrfTokenService();
        }
        return $this->csrfToken;
    }

    protected function upload(string $path, string $attrName, array $exentions, array $mimeTypes): ?string
    {
        return (new UploadController())->upload($path, $attrName, $exentions, $mimeTypes);
    }

    /**
     * Returns the translated string if possible, otherwise English.
     *
     * @return string
     */
    protected function lang(string $key, ...$vars): string
    {
        return App::getInstance()->getLang($key, $vars);
    }
}
