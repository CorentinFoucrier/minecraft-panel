<?php

namespace Core\Controller;

use AltoRouter;

class RouterController extends AltoRouter
{
    private bool $methodError = false;

    const METHOD_ERROR = 1;

    const NO_MATCH = 2;

    public function __construct()
    {
        $this->addMatchTypes(["ep" => "[0-9A-Za-z_-]++"]);
    }

    /**
     * @param string $target "controller#method"
     */
    public function get(string $uri, string $target, string $name = null): self
    {
        $this->map('GET', $uri, $target, $name);
        return $this;
    }

    /**
     * @param string $target "controller#method"
     */
    public function post(string $uri, string $target, string $name = null): self
    {
        $this->map('POST', $uri, $target, $name);
        return $this;
    }

    /**
     * @param string $target "controller#method"
     */
    public function patch(string $uri, string $target, string $name = null): self
    {
        $this->map('PATCH', $uri, $target, $name);
        return $this;
    }

    /**
     * @param string $target "controller#method"
     */
    public function put(string $uri, string $target, string $name = null): self
    {
        $this->map('PUT', $uri, $target, $name);
        return $this;
    }

    /**
     * @param string $target "controller#method"
     */
    public function delete(string $uri, string $target, string $name = null): self
    {
        $this->map('DELETE', $uri, $target, $name);
        return $this;
    }

    public function all(string $uri, string $target, string $name = null): self
    {
        $this->map('GET|POST|PATCH|PUT|DELETE', $uri, $target, $name);
        return $this;
    }

    public function getUrl(string $name, array $params = []): string
    {
        return $this->generate($name, $params);
    }

    public function run(): void
    {
        // Array with route information on success, false on failure (no match).
        $match = $this->match();
        if (is_array($match)) {
            $_SESSION['route'] = $match['name'];
            if (strpos($match['target'], "#")) {
                // Init 2 variables -> $controller and $mehode with two parts that return the explode function
                [$controller, $method] = explode("#", $match['target']);
                // $controller = App\Controller\MyController
                $controller = "App\\Controller\\" . ucfirst($controller) . "Controller";
                (new $controller())->$method(...array_values($match['params']));
                // Put all values of $match['params'] in $method parameters
                // Like this: (new $controller())->$method('id', 'slug', 'page', 'etc'))
            }
        } else {
            // no route was matched
            header("Location: " . $this->getUrl('error', ["code" => 404]));
            exit();
        }
    }

    ### REWRITE AltoRouter match() for return HTTP bad request ###

    /**
     * Match a given Request Url against stored routes
     * @param string $requestUrl
     * @param string $requestMethod
     * @return array|int Array with route information on success false (no match).
     */
    public function match($requestUrl = null, $requestMethod = null)
    {

        $params = [];

        // set Request Url if it isn't passed as parameter
        if ($requestUrl === null) {
            $requestUrl = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '/';
        }

        // strip base path from request url
        $requestUrl = substr($requestUrl, strlen($this->basePath));

        // Strip query string (?a=b) from Request Url
        if (($strpos = strpos($requestUrl, '?')) !== false) {
            $requestUrl = substr($requestUrl, 0, $strpos);
        }

        $lastRequestUrlChar = $requestUrl[strlen($requestUrl) - 1];

        // set Request Method if it isn't passed as a parameter
        if ($requestMethod === null) {
            $requestMethod = isset($_SERVER['REQUEST_METHOD']) ? $_SERVER['REQUEST_METHOD'] : 'GET';
        }

        foreach ($this->routes as $handler) {
            list($methods, $route, $target, $name) = $handler;

            $method_match = (stripos($methods, $requestMethod) !== false);

            if ($route === '*') {
                // * wildcard (matches all)
                $match = true;
            } elseif (isset($route[0]) && $route[0] === '@') {
                // @ regex delimiter
                $pattern = '`' . substr($route, 1) . '`u';
                $match = preg_match($pattern, $requestUrl, $params) === 1;
            } elseif (($position = strpos($route, '[')) === false) {
                // No params in url, do string comparison
                $match = strcmp($requestUrl, $route) === 0;
            } else {
                // Compare longest non-param string with url before moving on to regex
                // Check if last character before param is a slash, because it could be optional if param is optional too (see https://github.com/dannyvankooten/AltoRouter/issues/241)
                if (strncmp($requestUrl, $route, $position) !== 0 && ($lastRequestUrlChar === '/' || $route[$position - 1] !== '/')) {
                    continue;
                }

                $regex = $this->compileRoute($route);
                $match = preg_match($regex, $requestUrl, $params) === 1;
            }

            if ($match) {
                if ($params) {
                    foreach ($params as $key => $value) {
                        if (is_numeric($key)) {
                            unset($params[$key]);
                        }
                    }
                }

                // Method did not match, continue to next route.
                if (!$method_match) {
                    $this->methodError = true;
                    continue;
                } else {
                    $this->methodError = false;
                }

                return [
                    'target' => $target,
                    'params' => $params,
                    'name' => $name
                ];
            }
        }

        if ($this->methodError) {
            return self::METHOD_ERROR;
        } else {
            return self::NO_MATCH;
        }
    }
}
