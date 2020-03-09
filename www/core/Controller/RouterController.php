<?php

namespace Core\Controller;

class RouterController
{

    private \AltoRouter $router;

    public function __construct()
    {
        $this->router = new \AltoRouter();
    }

    public function get(string $uri, string $file, string $name): self
    {
        $this->router->map('GET', $uri, $file, $name);
        return $this;
    }

    public function post(string $uri, string $file, string $name): self
    {
        $this->router->map('POST', $uri, $file, $name);
        return $this;
    }

    public function match(string $uri, string $file, string $name): self
    {
        $this->router->map('GET|POST', $uri, $file, $name);
        return $this;
    }

    public function url(string $name, array $params = []): string
    {
        return $this->router->generate($name, $params);
    }

    public function run(): void
    {
        // Array with route information on success, false on failure (no match).
        $match = $this->router->match();
        if (is_array($match)) {
            $_SESSION['route'] = $match['name'];
            if (strpos($match['target'], "#")) {
                // Init 2 variables -> $controller and $mehode with two parts that return the explode function
                [$controller, $methode] = explode("#", $match['target']);
                // $controller = App\Controller\MyController
                $controller = "App\\Controller\\" . ucfirst($controller) . "Controller";
                (new $controller())->$methode(...array_values($match['params']));
                // Put all values of $match['params'] in $methode parameters
                // Like this: (new $controller())->$methode('id', 'slug', 'page', 'etc'))
            }
        } else {
            // no route was matched
            header("Location: " . $this->url('error', ["code" => 404]));
            exit();
        }
    }
}
