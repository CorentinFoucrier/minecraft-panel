<?php
namespace Core\Controller;

use App\App;

class URLController
{
    public static function getInt(string $name, ?int $default = null): ?int
    {
        if (!isset($_GET[$name])) {
            return $default;
        }
        if ($_GET[$name] === '0') {
            return 0;
        }

        if (!filter_var($_GET[$name], FILTER_VALIDATE_INT)) {
            throw new \Exception("The '$name' param in the URL is not an integer!");
        }
        return (int) $_GET[$name];
    }

    public static function getPositiveInt(string $name, ?int $default = null): ?int
    {
        $param = self::getInt($name, $default);
        if ($param !== null && $param <= 0) {
            throw new \Exception("The '$name' param in the URL is not a positive integer!");
        }
        return $param;
    }

    /**
     * Get the entire Uri eg. http://localhost/foo/bar/1
     *
     * @param string $routeName
     * @param array $params Assoc array ['paramName'=>'value']
     * @return string
     */
    public static function getUri(string $routeName, array $params = []): string
    {
        $uri = $_SERVER["REQUEST_SCHEME"] . "://" . $_SERVER["HTTP_HOST"];
        $folder = App::getInstance()->getRouter()->url($routeName, $params);
        return $uri. $folder;
    }
}
