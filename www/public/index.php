<?php
define('START_DEBUG_TIME', microtime(true));

$basePath = dirname(dirname(__DIR__)) . DIRECTORY_SEPARATOR;
define('BASE_PATH', $basePath, true);

require_once BASE_PATH . 'www/vendor/autoload.php';

$app = App\App::getInstance();
$app::load();

$app->getRouter()
    // '/url', 'Controller#Methode', 'route_name'

    // Access POST or GET
    ->match('/getLog', 'Server#getLog', 'getLog')
    ->match('/worlds', 'Worlds#showWorlds', 'worlds')
    ->match('/players', 'Players#showPlayers', 'players')

    // Access GET only
    ->get('/', 'Dashbord#showDashboad', 'dashboard')
    ->get('/', 'ONLY_FOR_#_URL_BASE', 'base') // Used for get base url (do not move this up)
    ->get('/logout', 'User#logout', 'logout')
    ->get('/error/[i:code]', 'Error#show', 'error')
    ->get('/config', 'Config#showForm', 'config')
    ->get('/login', 'User#showLogin', 'login')
    ->get('/settings', 'Settings#show', 'settings')

    // Access POST only
    // User
    ->post('/change_default_password', 'User#changeDefaultPassword', 'change_default_password')
    ->post('/login_check', 'User#loginCheck', 'login_check')
    // Config
    ->post('/config/send', 'Config#send', 'send_config')
    // Server
    ->post('/start', 'Server#start', 'server_start')
    ->post('/stop', 'Server#stop', 'server_stop')
    ->post('/checkStatus', 'Server#checkStatus', 'check_status')
    ->post('/getOnlinePlayers', 'Server#getOnlinePlayers', 'getOnlinePlayers')
    ->post('/sendCommand', 'Server#sendCommand', 'command_send')
    ->post('/selectVersion', 'Server#selectVersion', 'select_version')
    // Settings
    ->post('/settings/edit_user_role', 'Settings#editUserRole', 'edit_user_role')
    ->post('/settings/add_new_user', 'Settings#addNewUser', 'add_new_user')
    ->post('/settings/delete_user', 'Settings#deleteUser', 'delete_user')
    ->post('/settings/save_roles_order', 'Settings#saveRolesOrder', 'save_roles_order')
    ->post('/settings/add_new_role', 'Settings#addNewRole', 'add_new_role')
    ->post('/settings/get_role_permission', 'Settings#getRolePermission', 'get_role_permission')
    ->post('/settings/edit_role_permission', 'Settings#editRolePermission', 'edit_role_permission')
    ->post('/settings/delete_role', 'Settings#deleteRole', 'delete_role')
    // Worlds
    ->post('/worlds/delete', 'Worlds#deleteWorlds', 'worldsDelete')
    ->post('/worlds/download', 'Worlds#downloadWorld', 'downloadWorld')
    ->post('/worlds/upload', 'Worlds#uploadWorld', 'uploadWorld')
    // Players
    ->post('/players/deleteFromList', 'Players#deleteFromList', 'deleteFromList')
    ->run();
