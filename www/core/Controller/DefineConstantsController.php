<?php
namespace Core\Controller;

use Core\Controller\Controller;
use Core\Controller\Helpers\ServerPropertiesController;

class DefineConstantsController extends Controller {

    public static function define(): void
    {
        define("PREFIX", getenv('PREFIX'));
        define("RAM_MIN", "1024M");
        define("RAM_MAX", "2048M");
        define("SHELL_USER", getenv("SHELL_USER"));
        define("SELL_PWD", getenv("SHELL_PWD"));
        define("SERVER_PROPERTIES", ServerPropertiesController::getContent());
        /**
         * Not define any const if the server.properties file is not found or empty
         * Waiting the server generate the file first.
         */
        if (!is_null(SERVER_PROPERTIES)) {
            define("ENABLE_QUERY", "true");
            define("QUERY_PORT", "25565");
        }
    }
}
