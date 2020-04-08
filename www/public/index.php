<?php
define('START_DEBUG_TIME', microtime(true));

$basePath = dirname(dirname(__DIR__)) . DIRECTORY_SEPARATOR;
define('BASE_PATH', $basePath, true);

require_once BASE_PATH . 'www/vendor/autoload.php';

$app = App\App::getInstance();
$app::load();

$app->getRouter()
    // '/url', 'Controller#Methode', 'routeName'
    // Access POST or GET
    ->match('/getLog', 'Server#getLog', 'getLog')
    ->match('/login', 'User#login', 'login')
    ->match('/co-administrators', 'CoAdmin#showCoAdmin', 'coAdmin')
    ->match('/worlds', 'Worlds#showWorlds', 'worlds')
    ->match('/players', 'Players#showPlayers', 'players')
    // Access GET only
    ->get('/', 'Dashbord#showDashboad', 'dashboard')
    ->get('/logout', 'User#logout', 'logout')
    ->get('/error/[i:code]', 'Error#show', 'error')
    ->get('/config', 'Config#showForm', 'config')
    // Access POST only
    ->post('/config/send', 'Config#send', 'send_config')
    ->post('/start', 'Server#start', 'server_start')
    ->post('/stop', 'Server#stop', 'server_stop')
    ->post('/checkStatus', 'Server#checkStatus', 'check_status')
    ->post('/getOnlinePlayers', 'Server#getOnlinePlayers', 'getOnlinePlayers')
    ->post('/sendCommand', 'Server#sendCommand', 'command_send')
    ->post('/selectVersion', 'Server#selectVersion', 'select_version')
    ->post('/coAdmin/delete/[i:id]/[*:token]', 'CoAdmin#deleteCoAdmin', 'coAdminDelete')
    ->post('/coAdmin/edit', 'CoAdmin#editPermissions', 'editPermissions')
    ->post('/worlds/delete', 'Worlds#deleteWorlds', 'worldsDelete')
    ->post('/worlds/download', 'Worlds#downloadWorld', 'downloadWorld')
    ->post('/worlds/upload', 'Worlds#uploadWorld', 'uploadWorld')
    ->post('/players/deleteFromList', 'Players#deleteFromList', 'deleteFromList')
    ->run();
