<?php

namespace App;

use App\Controller\ServerController;
use Core\Controller\RouterController;
use Core\Controller\UploadController;
use Core\Controller\Session\PhpSession;
use App\Controller\ServerQueryController;
use Core\Controller\Session\FlashService;
use Core\Controller\Helpers\LogsController;
use Core\Controller\Database\DatabaseController;
use Core\Controller\Database\DatabaseMysqlController;
use Core\Controller\Helpers\ServerPropertiesController;

class App
{

    private static App $INSTANCE;

    private RouterController $router;

    private DatabaseController $db_instance;

    public static function getInstance()
    {
        if (!isset(self::$INSTANCE)) {
            self::$INSTANCE = new App();
        }
        return self::$INSTANCE;
    }

    public static function load()
    {
        if (getenv("ENV_DEV") === "true") {
            $whoops = new \Whoops\Run;
            $whoops->pushHandler(new \Whoops\Handler\PrettyPageHandler);
            $whoops->register();
        }

        self::defineConstants();

        if (session_status() != PHP_SESSION_ACTIVE) {
            session_start();
        }
    }

    private function defineConstants(): void
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

    public function getRouter($basePath = "/var/www"): RouterController
    {
        if (!isset($this->router)) {
            $this->router = new RouterController($basePath . 'views');
        }
        return $this->router;
    }

    /**
     * Used for instantiate any table passed by Core\Controller\loadModel($tableName) method
     *
     * @param string $tableName
     * @return object
     */
    public function getTable(string $tableName): object
    {
        $nameSpaceTable = "\\App\\Model\\Table\\" . ucfirst($tableName) . "Table";
        return new $nameSpaceTable($this->getDb(), $tableName);
    }

    public function getDb(): DatabaseController
    {
        if (!isset($this->db_instance)) {
            $this->db_instance = new DatabaseMysqlController(
                getenv('MYSQL_DATABASE'),
                getenv('MYSQL_USER'),
                getenv('MYSQL_PASSWORD'),
                getenv('MYSQL_HOST')
            );
        }
        return $this->db_instance;
    }

    public function getFlash(): FlashService
    {
        return new FlashService(new PhpSession());
    }

    public function getServer(): ServerController
    {
        return new ServerController();
    }

    public function getLogs(): LogsController
    {
        return new LogsController();
    }

    public function getServerQuery(): ServerQueryController
    {
        return new ServerQueryController();
    }

    public function getUpload(): UploadController
    {
        return new UploadController();
    }
}
