<?php

namespace Core\Controller;

use App\App;

class URLController
{
    /**
     * Get the entire Uri eg. http://localhost/foo/bar/1
     *
     * @param string $routeName
     * @param array $params Assoc array ['paramName'=>'value']
     * @return string
     */
    public static function getUri(string $routeName, array $params = []): string
    {
        $protocol = $_SERVER["REQUEST_SCHEME"]; // "https"
        $domain = $_SERVER["HTTP_HOST"]; // domain.com || server IP
        $url = App::getInstance()->getRouter()->getUrl($routeName, $params); // Reversed routing

        return $protocol . "://" . $domain . $url;
    }
}
