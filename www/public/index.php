<?php
$basePath = dirname(dirname(__DIR__)) . DIRECTORY_SEPARATOR;
define('BASE_PATH', $basePath, true);

require_once BASE_PATH . 'www/vendor/autoload.php';

$app = App\App::getInstance();
$app::load();

$app->getRouter($basePath)
    ->match('/getLog', 'Server#getLog', 'getLog')
    ->match('/config', 'Config#showForm', 'config')
    ->match('/login', 'User#login', 'login')
    ->match('/co-administrators', 'CoAdmin#showCoAdmin', 'coAdmin')
    
    ->get('/', 'Dashbord#showDashboad', 'dashboard')
    ->get('/checkStatus', 'Server#checkStatus', 'check_status')
    ->get('/getOnlinePlayers', 'Dashbord#getOnlinePlayers', 'getOnlinePlayers')
    ->get('/getVersion', 'Dashbord#getVersion', 'getVersion')
    
    ->post('/start', 'Controls#start', 'server_start')
    ->post('/restart', 'Controls#restart', 'server_restart')
    ->post('/stop', 'Controls#stop', 'server_stop')
    ->post('/sendCommand', 'Server#sendCommand', 'command_send')
    ->post('/selectVersion', 'Server#selectVersion', 'select_version')
    ->post('/coAdmin/delete/[i:id]/[*:token]', 'CoAdmin#deleteCoAdmin', 'coAdminDelete')
    ->run();