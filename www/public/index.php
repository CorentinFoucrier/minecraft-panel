<?php
$basePath = dirname(dirname(__DIR__)) . DIRECTORY_SEPARATOR;
define('BASE_PATH', $basePath, true);

require_once BASE_PATH . 'www/vendor/autoload.php';

$app = App\App::getInstance();
$app::load();

$app->getRouter($basePath)
    ->match('/', 'Dashbord#showDashboad', 'dashboard')
    ->match('/config', 'Config#showForm', 'config')
    ->run();