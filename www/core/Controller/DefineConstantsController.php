<?php
namespace Core\Controller;

use Core\Controller\Controller;
use Core\Controller\Helpers\ServerPropertiesController;

class DefineConstantsController extends Controller {

    public static function userDefine(): void
    {
        define("SERVER_PROPERTIES", ServerPropertiesController::getContent());
        /**
         * Not define any const if the server.properties file is not found or empty
         * Waiting the server generate the file first.
         */
        if (!is_null(SERVER_PROPERTIES)) {
            define("ENABLE_QUERY", "true");
            define("ENABLE_RCON", "true");
            define("QUERY_PORT", "25565");
            define("RCON_PORT", "25595");
            define("RCON_PASSWORD", self::rconPasword());
        }
    }

    private static function rconPasword(): string
    {
        $config = SERVER_PROPERTIES; //Retrieve server.properties file
        /* If the file not contain an existing rcon password it will be created... */
        if (empty($config['rcon.password'])) {
            $factory = new \RandomLib\Factory;
            $generator = $factory->getGenerator(new \SecurityLib\Strength(\SecurityLib\Strength::MEDIUM));
            return $generator->generateString(32);
        } else {
            /* ...else return the registered password  */
            return $config['rcon.password'];
        }
    }
}
