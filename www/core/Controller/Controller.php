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

    protected function render(string $view, array $variables = [])
    {
        echo $this->getTwig()->render(
            $view . '.html.twig',
            $variables
        );
    }

    private function getTwig()
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

    protected function getApp(): App
    {
        if (is_null($this->app)) {
            $this->app = App::getInstance();
        }
        return $this->app;
    }

    protected function generateUrl(string $routeName, array $params = []): String
    {
        return $this->getApp()->getRouter()->url($routeName, $params);
    }

    protected function loadModel(string $nameTable): void
    {
        $this->$nameTable = $this->getApp()->getTable($nameTable);
    }

    protected function getFlash(): FlashService
    {
        return $this->getApp()->getFlash();
    }

    protected function getUri(string $name, array $params = []): string
    {
        return URLController::getUri($name, $params);
    }

    protected function redirect(string $url, ?int $httpResponseCode = null)
    {
        if ($httpResponseCode) {
            http_response_code($httpResponseCode);
        }
        return header('Location: '.$url);
    }

    protected function getServer(): ServerController
    {
        return $this->getApp()->getServer();
    }

    protected function getLogs(): LogsController
    {
        return $this->getApp()->getLogs();
    }

    protected function getServerQuery(): ServerQueryController
    {
        return $this->getApp()->getServerQuery();
    }
}
