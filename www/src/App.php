<?php

namespace App;

use phpseclib\Net\SSH2;
use Core\Controller\RouterController;
use Core\Controller\Session\PhpSession;
use Core\Controller\Session\FlashService;
use Core\Controller\Services\PropertiesService;
use Core\Controller\Database\DatabaseController;
use Core\Controller\Database\DatabaseMysqlController;

class App
{

    private static App $app_instance;

    private SSH2 $ssh2_instance;

    private DatabaseController $db_instance;

    private RouterController $router;

    /**
     * App instance singleton
     *
     * @return App
     */
    public static function getInstance(): App
    {
        if (!isset(self::$app_instance)) {
            self::$app_instance = new App();
        }
        return self::$app_instance;
    }

    /**
     * Load everything you need for this instance
     *
     * @return void
     */
    public static function load(): void
    {
        if (getenv("ENV_DEV") === "true") {
            // Whoops debug init
            $whoops = new \Whoops\Run;
            $whoops->pushHandler(new \Whoops\Handler\PrettyPageHandler);
            $whoops->register();
        } else {
            // Turn off all error reporting
            error_reporting(0);
        }

        self::defineConstants();

        // Session arn't compatible with CLI
        if (session_status() !== PHP_SESSION_ACTIVE && PHP_SAPI !== "cli") {
            session_start();
        }
    }

    private static function defineConstants(): void
    {
        define("SERVER_STOPPED", 0);
        define("SERVER_LOADING", 1);
        define("SERVER_STARTED", 2);
        define("SERVER_ERROR", 3);
        define("PREFIX", getenv('PREFIX'));
        define("SHELL_USER", getenv("SHELL_USER"));
        define("SHELL_PWD", getenv("SHELL_PWD"));
        define("SERVER_PROPERTIES", PropertiesService::get());
        define("ENABLE_QUERY", "true");
        define("QUERY_PORT", SERVER_PROPERTIES['server-port']);
    }

    /**
     * Used for instantiate any table passed by Core\Controller\loadModel($tableName) method
     *
     * @param string $tableName
     * @return object
     */
    public function getTable(string $tableName): object
    {
        $tableNameSpace = "\\App\\Model\\Table\\${tableName}Table";
        return new $tableNameSpace($this->getDb(), $tableName);
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

    public function getRouter(): RouterController
    {
        if (!isset($this->router)) {
            $this->router = new RouterController();
        }
        return $this->router;
    }

    public function getSsh(): SSH2
    {
        if (!isset($this->ssh2_instance)) {
            try {
                $ssh = new SSH2(getenv('IP'));
                $ssh->login(SHELL_USER, SHELL_PWD);
                $this->ssh2_instance = $ssh;
            } catch (\Exception $e) {
                echo 'error';
            }
        }
        return $this->ssh2_instance;
    }

    public function getFlash(): FlashService
    {
        return new FlashService(new PhpSession());
    }

    /**
     * Returns the translated string if possible, otherwise English.
     * 
     * @return string
     */
    public function getLang(string $key, array $vars = []): string
    {
        $lang = $_SESSION['lang'];
        if (!isset($this->currentLanguage)) {
            $current_json = BASE_PATH . "www/lang/{$lang}.json";
            $h = fopen($current_json, 'r');
            ${$lang} = json_decode(fread($h, filesize($current_json)), true);
            $this->currentLanguage = ${$lang};
        }

        if (empty($this->currentLanguage[$key])) {
            if (!isset($this->defaultLanguage)) {
                $en_json = BASE_PATH . "www/lang/en_US.json";
                $h = fopen($en_json, 'r');
                $en_US = json_decode(fread($h, filesize($en_json)), true);
                $this->defaultLanguage = $en_US;
            }
            return $this->langReplaceVars($this->defaultLanguage[$key], $vars);
        } else {
            return $this->langReplaceVars($this->currentLanguage[$key], $vars);
        }
    }

    /**
     * Replace "$n" by value with the same index of "n" in $str
     *
     * @return string
     */
    private function langReplaceVars(string $str, array $vars): string
    {
        if (!empty($vars)) {
            for ($i = 0; $i < count($vars); $i++) {
                $re = '/\$' . $i . '/';
                $str = preg_replace($re, $vars[$i], $str, 1);
            }
        }
        return $str;
    }
}
