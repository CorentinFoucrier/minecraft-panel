<?php
namespace App\Controller;

use Core\Controller\Controller;

class DashbordController extends Controller
{
    public function __construct()
    {
        $this->loadModel('server');
    }
    public function showDashboad()
    {
        $this->userOnly();
        $config = SERVER_PROPERTIES;
        $maxPlayers = $config['max-players'];
        $version = $this->getVersion();
        $token = bin2hex(random_bytes(8));
        $_SESSION['token'] = $token;
        $socketUrl = $_SERVER['REQUEST_SCHEME'] ."://". $_SERVER['SERVER_NAME'] .":". getenv('SOCKETIO_PORT');
        try {
            $ops = json_decode(file_get_contents(BASE_PATH.'minecraft_server/ops.json'), true);
        } catch (\Exception $e) {
            (getenv("ENV_DEV")) ? $this->getFlash()->addWarning("[Dev only]|'ops.json' n'existe pas !") : $ops = [];
        }
        
        return $this->render("dashboard", [
            'title' => "Tableau de bord",
            'maxPlayers' => $maxPlayers,
            'version' => $version,
            'ops' => $ops,
            'token' => $token,
            'socketUrl' => $socketUrl
        ]);
    }
    /**
     * Get online players through AJAX
     * Route: /getOnlinePlayers
     * @return void
     */
    public function getOnlinePlayers(): void
    {
        $players = $this->getServerQuery()->getPlayers();
        echo $players['online'];
    }
    /**
     * Get active minecraft version
     * Route: /getVersion
     * @return string
     */
    public function getVersion(): string
    {
        $req = $this->server->selectEverything(true)->getVersion();
        $version = explode('_', $req);
        if ($version[0] == "MC") {
            $v = $version = "Vanilla ".$version[1];
            if (!empty($_GET)) {
                echo $v;
            }
            return $v;
        } elseif ($version[0] == "SNAP") {
            $v = $version = "Snapshot ".$version[1];
            if (!empty($_GET)) {
                echo $v;
            }
            return $v;
        } else {
            return $version = ucfirst(str_replace('_', ' ', $req));
        }
    }
}
