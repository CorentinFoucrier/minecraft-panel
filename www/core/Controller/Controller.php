<?php

namespace Core\Controller;

use Core\Controller\Session\FlashService;
use Core\Extension\Twig\FlashExtension;
use Core\Extension\Twig\URIExtension;

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
            $loader = new \Twig\Loader\FilesystemLoader(BASE_PATH . 'www/views/');
            $this->twig = new \Twig\Environment($loader);
            //Global
            $this->twig->addGlobal('constant', get_defined_constants());
            //Extension
            $this->twig->addExtension(new FlashExtension());
            $this->twig->addExtension(new URIExtension());
        }
        return $this->twig;
    }

    protected function getApp()
    {
        if (is_null($this->app)) {
            $this->app = \App\App::getInstance();
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

    protected function flash(): FlashService
    {
        return $this->getApp()->flash();
    }

    protected function getUri(string $name, array $params = []): string
    {
        return URLController::getUri($name, $params);
    }

    protected function redirect(string $routeName, ?int $httpResponseCode = null)
    {
        if ($httpResponseCode) {
            http_response_code($httpResponseCode);
        }
        return header('Location: '.$routeName);
    }
}
