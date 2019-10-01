<?php
namespace App;

use Core\Controller\RouterController;
use Core\Controller\URLController;
use Core\Controller\Database\DatabaseMysqlController;
use Core\Controller\Database\DatabaseController;
use Core\Controller\DefineConstantsController;
use Core\Controller\Session\FlashService;
use Core\Controller\Session\PhpSession;


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

        DefineConstantsController::userDefine();

        if (session_status() != PHP_SESSION_ACTIVE){
            session_start();
        }

        $numPage = URLController::getPositiveInt('page');

        if ($numPage !== null) {
            // url /categories?page=1&parm2=pomme
            if ($numPage == 1) {
                $uri = explode('?', $_SERVER["REQUEST_URI"])[0];
                $get = $_GET;
                unset($get["page"]);
                $query = http_build_query($get);
                if (!empty($query)) {
                    $uri = $uri . '?' . $query;
                }
                http_response_code(301);
                header('location: ' . $uri);
                exit();
            }
        }
    }

    public function getRouter($basePath = "/var/www"): RouterController
    {
        if (is_null($this->router)) {
            $this->router = new RouterController($basePath . 'views');
        }
        return $this->router;
    }

    public function getTable(string $nameTable)
    {
        $nameTable = "\\App\\Model\\Table\\" . ucfirst($nameTable) . "Table";
        return new $nameTable($this->getDb());
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

    public function flash()
    {
        return new FlashService(new PhpSession());
    }
}
