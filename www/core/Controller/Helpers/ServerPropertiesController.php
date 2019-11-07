<?php
namespace Core\Controller\Helpers;

use Core\Controller\Controller;

class ServerPropertiesController extends Controller
{

    public static $filePath = BASE_PATH . 'minecraft_server/server.properties';

    public static function getContent(): ?array
    {
        /* Path to server.properties */
        try {
            $fileContent = file_get_contents(self::$filePath);
        } catch (\Exception $e) {
            return null;
        }

        $regex = '/(.+)=(.*)/m'; //regex: search and split in 2 groups where is "=" eg.(key)=(value)

        /* Result of regex in an array of $matches */
        preg_match_all($regex, $fileContent, $matches, PREG_SET_ORDER, 0);

        /* Generate $config array as $key => $value */
        for ($i=0; $i < count($matches); $i++) {
            $config[$matches[$i][1]] = htmlspecialchars($matches[$i][2], ENT_QUOTES);
        }

        ksort($config);//Sort alphabeticly

        return $config;
    }
}
