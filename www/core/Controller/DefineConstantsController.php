<?php
namespace Core\Controller;

use Core\Controller\Controller;
use Core\Controller\Helpers\ServerPropertiesController;

class DefineConstantsController extends Controller {

    public static function define(): void
    {
        define("PREFIX", getenv('PREFIX'));
        define("RAM_MIN", "512M");
        define("RAM_MAX", "1024M");
        define("SHELL_USER", getenv("SHELL_USER"));
        define("SELL_PWD", getenv("SHELL_PWD"));
        define("SERVER_PROPERTIES", ServerPropertiesController::getContent());
        define("ENABLE_QUERY", "true");
        define("QUERY_PORT", "25565");
    }
}
