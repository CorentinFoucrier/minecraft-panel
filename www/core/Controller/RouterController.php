<?php
namespace Core\Controller;

class RouterController
{

    private $router;

    private $viewPath;

    public function __construct(string $viewPath)
    {
        $this->viewPath = $viewPath;
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
        $match = $this->router->match();

        if (is_array($match)) {
            if (strpos($match['target'], "#")) {
                [$controller, $methode] = explode("#", $match['target']);
                $controller = "App\\Controller\\" . ucfirst($controller) . "Controller";
                //try{

                (new $controller())->$methode(...array_values($match['params']));
                //}catch(\Exception $e){
                //    header($_SERVER["SERVER_PROTOCOL"] . ' 404 Not Found');
                //    exit();
                //}
            }
        } else {
            // no route was matched
            header($_SERVER["SERVER_PROTOCOL"] . ' 404 Not Found');
            exit();
        }
    }
}
