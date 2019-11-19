<?php
namespace App;

use Core\Controller\URLController;
use App\Controller\ServerController;
use Core\Controller\RouterController;
use Core\Controller\Session\PhpSession;
use App\Controller\ServerQueryController;
use Core\Controller\Session\FlashService;
use Core\Controller\Helpers\LogsController;
use Core\Controller\DefineConstantsController;
use Core\Controller\Database\DatabaseController;
use Core\Controller\Database\DatabaseMysqlController;
use Core\Controller\UploadController;

class App
{

    private static $INSTANCE;

    public $title;

    private $router;

    private $db_instance;

    public static function getInstance()
    {
        if (is_null(self::$INSTANCE)) {
            self::$INSTANCE = new App();
        }
        return self::$INSTANCE;
    }

    public static function load()
    {
        if (getenv("ENV_DEV")) {
            $whoops = new \Whoops\Run;
            $whoops->pushHandler(new \Whoops\Handler\PrettyPageHandler);
            $whoops->register();
        }

        DefineConstantsController::define();

        if (session_status() != PHP_SESSION_ACTIVE) {
            session_start();
        }

    }

    public function getRouter($basePath = "/var/www"): RouterController
    {
        if (is_null($this->router)) {
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
    public function getTable(string $tableName)
    {
        $tableName = "\\App\\Model\\Table\\" . ucfirst($tableName) . "Table";
        return new $tableName($this->getDb());
    }

    public function getDb(): DatabaseController
    {
        if (is_null($this->db_instance)) {
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
