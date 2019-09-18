<?php
$basePath = dirname(__dir__) . DIRECTORY_SEPARATOR;
define('BASE_PATH', $basePath, true);

require_once BASE_PATH . 'vendor/autoload.php';

$app = App\App::getInstance();
$app->setStartTime();
$app::load();

$app->getRouter($basePath)
    ->match('/', 'Dashbord#showDashboad', 'dashboard')
    ->run();