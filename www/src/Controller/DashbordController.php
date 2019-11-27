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
        $ops = json_decode(file_get_contents(BASE_PATH.'minecraft_server/ops.json'), true);
        $token = bin2hex(random_bytes(8));
        $_SESSION['token'] = $token;
        $socketUrl = $_SERVER['REQUEST_SCHEME'] ."://". $_SERVER['SERVER_NAME'] . getenv('SOCKETIO_PORT');

        return $this->render("dashboard", [
            'title' => "Tableau de board",
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
