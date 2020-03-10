<?php
define('START_DEBUG_TIME', microtime(true));

$basePath = dirname(dirname(__DIR__)) . DIRECTORY_SEPARATOR;
define('BASE_PATH', $basePath, true);

require_once BASE_PATH . 'www/vendor/autoload.php';

$app = App\App::getInstance();
$app::load();

$app->getRouter(BASE_PATH)
    // Access POST or GET
    ->match('/getLog', 'Server#getLog', 'getLog')
    ->match('/config', 'Config#showForm', 'config')
    ->match('/login', 'User#login', 'login')
    ->match('/co-administrators', 'CoAdmin#showCoAdmin', 'coAdmin')
    ->match('/worlds', 'Worlds#showWorlds', 'worlds')
    ->match('/players', 'Players#showPlayers', 'players')
    // Access GET only
    ->get('/', 'Dashbord#showDashboad', 'dashboard')
    ->get('/logout', 'User#logout', 'logout')
    ->get('/error/[i:code]', 'Error#show', 'error')
    // Access POST only
    ->post('/start', 'Controls#start', 'server_start')
    ->post('/restart', 'Controls#restart', 'server_restart')
    ->post('/stop', 'Controls#stop', 'server_stop')
    ->post('/checkStatus', 'Server#checkStatus', 'check_status')
    ->post('/getOnlinePlayers', 'Server#getOnlinePlayers', 'getOnlinePlayers')
    ->post('/sendCommand', 'Server#sendCommand', 'command_send')
    ->post('/selectVersion', 'Server#selectVersion', 'select_version')
    ->post('/coAdmin/delete/[i:id]/[*:token]', 'CoAdmin#deleteCoAdmin', 'coAdminDelete')
    ->post('/coAdmin/edit', 'CoAdmin#editPermissions', 'editPermissions')
    ->post('/worlds/delete', 'Worlds#deleteWorlds', 'worldsDelete')
    ->post('/players/deleteFromList', 'Players#deleteFromList', 'deleteFromList')
    ->run();
