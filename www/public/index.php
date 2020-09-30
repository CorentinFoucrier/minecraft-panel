<?php
define('START_DEBUG_TIME', microtime(true));

$basePath = dirname(dirname(__DIR__)) . DIRECTORY_SEPARATOR;
define('BASE_PATH', $basePath, true);

require_once BASE_PATH . 'www/vendor/autoload.php';
$app = App\App::getInstance();
$app::load();

$app->getRouter()
    ->all('/api/[*]?/[*]?', 'Api#manager', 'api')
    // UserController
    ->post('/change_default_password', 'User#changeDefaultPassword', 'change_default_password')
    ->post('/login_check', 'User#loginCheck', 'login_check')
    ->get('/login', 'User#showLogin', 'login')
    ->get('/logout', 'User#logout', 'logout')

    ->get('/', "App#redirectToDashboard", "redirect_to_dashboad")
    ->get('/dashboard', 'App#index', 'dashboard')
    ->get('*', 'App#index', 'app') // If no match before it's for react
    ->get('/', 'ONLY_FOR_#_URL_BASE', 'base') // Used for get base url (do not move this up)
    ->run();
