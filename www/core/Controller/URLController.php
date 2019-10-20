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
            throw new \Exception("Le paramètre '$name' dans l'url n'est pas un entier");
        }
        return (int) $_GET[$name];
    }

    public static function getPositiveInt(string $name, ?int $default = null): ?int
    {
        $param = self::getInt($name, $default);
        if ($param !== null && $param <= 0) {
            throw new \Exception("Le paramètre '$name' dans l'url n'est pas un entier positif");
        }
        return $param;
    }

    /**
     * génère une uri entière :) avec http et tout et tout
     *
     * @param string $cible
     * @return string
     */
    public static function getUri(string $name, array $params = []): string
    {
        
        $uri = $_SERVER["REQUEST_SCHEME"] . "://" . $_SERVER["HTTP_HOST"];
        //$folder = $this->generateUrl($name, $params);
        $folder = App::getInstance()->getRouter()->url($name, $params);

        return $uri. $folder;
    }
}
