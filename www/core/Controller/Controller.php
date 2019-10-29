<?php

namespace Core\Controller;

use App\App;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;
use App\Controller\ServerController;
use Core\Extension\Twig\URIExtension;
use Core\Extension\Twig\FlashExtension;
use App\Controller\ServerQueryController;
use Core\Controller\Session\FlashService;
use Core\Controller\Helpers\LogsController;

abstract class Controller
{

    private $twig;

    private $app;

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
            $view . '.html.twig',
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
        if (is_null($this->twig)) {
            $loader = new FilesystemLoader(BASE_PATH . 'www/views/');
            $this->twig = new Environment($loader);
            //Global
            $this->twig->addGlobal('constant', get_defined_constants());
            //Extension
            $this->twig->addExtension(new FlashExtension());
            $this->twig->addExtension(new URIExtension());
        }
        return $this->twig;
    }

    /**
     * Get an the current instance of App or
     * create a new App if not exist
     *
     * @return App
     */
    protected function getApp(): App
    {
        if (is_null($this->app)) {
            $this->app = App::getInstance();
        }
        return $this->app;
    }

    /**
     * Generate the url of a route name eg. /foo/bar/1
     * without the domain name
     *
     * @param string $routeName
     * @param array $params Assoc array ['paramName'=>'value']
     * @return string
     */
    protected function generateUrl(string $routeName, array $params = []): string
    {
        return $this->getApp()->getRouter()->url($routeName, $params);
    }

    /**
     * Used to instantiate a model class in src/Model/Table
     * Called by this->loadModel in any Controller that extents of Core\Controller
     *
     * @param string $tableName
     * @return void
     */
    protected function loadModel(string $tableName): void
    {
        $this->$tableName = $this->getApp()->getTable($tableName);
    }

    /**
     * Get a FlashService for put flash messages in $_SESSION
     * getFlash()->addAlert('Custom alert message')
     *
     * @return FlashService
     */
    protected function getFlash(): FlashService
    {
        return $this->getApp()->getFlash();
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
     * Redirect a client with an optionnal http code.
     *
     * @param string $url
     * @param integer|null $httpResponseCode
     * @return void
     */
    protected function redirect(string $url, ?int $httpResponseCode = null)
    {
        if ($httpResponseCode) {
            http_response_code($httpResponseCode);
        }
        return header('Location: '.$url);
    }

    /**
     * Get Server Controller
     *
     * @return ServerController
     */
    protected function getServer(): ServerController
    {
        return $this->getApp()->getServer();
    }

    /**
     * Get Logs Controller
     *
     * @return LogsController
     */
    protected function getLogs(): LogsController
    {
        return $this->getApp()->getLogs();
    }

    /**
     * Get SeverQuery Controller
     *
     * @return ServerQueryController
     */
    protected function getServerQuery(): ServerQueryController
    {
        return $this->getApp()->getServerQuery();
    }

    /**
     * Redirect the visitor if he is not logged in.
     *
     * @return void
     */
    protected function userOnly(): void
    {
        if (!isset($_SESSION['username']) || empty($_SESSION['username'])) {
            $this->redirect($this->getUri('login'));
        }
    }

    /**
     * Redirect the visitor if he is not logged as Admin.
     *
     * @return void
     */
    protected function adminOnly(): void
    {
        $this->loadModel('user');
        $user = $this->user->select(['username' => $_SESSION['username']]);
        if ($user && ($user->getRoleId() !== 1)) {
            $this->redirect($this->getUri('login'));
        }
    }

    /**
     * Redirect connected users.
     *
     * @return void
     */
    protected function notForLoggedIn()
    {
        if (isset($_SESSION['username']) && !empty($_SESSION['username'])) {
            $this->redirect($this->getUri('dashboard'));
        }
    }
}
