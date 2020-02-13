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
        $currentVersion = $this->getVersion();
        $token = bin2hex(random_bytes(8));
        $_SESSION['token'] = $token;
        $socketUrl = $_SERVER['REQUEST_SCHEME'] . "://" . $_SERVER['SERVER_NAME'] . ":" . getenv('SOCKETIO_PORT');
        try {
            $ops = json_decode(file_get_contents(BASE_PATH . 'minecraft_server/ops.json'), true);
        } catch (\Exception $e) {
            (getenv("ENV_DEV")) ? $this->getFlash()->addWarning('[Dev only]|"ops.json" n\'existe pas !') : $ops = [];
        }

        $versions = json_decode(file_get_contents("https://launchermeta.mojang.com/mc/game/version_manifest.json"), true);
        if (is_array($versions)) {
            $vanilla = [
                "release" => [],
                "snapshot" => []
            ];

            for ($i = 0; $i < count($versions['versions']); $i++) {
                $version = $versions['versions'][$i];
                if ($version['type'] == "release") {
                    array_push($vanilla['release'], $version['id']);
                } elseif ($version['type'] == "snapshot") {
                    array_push($vanilla['snapshot'], $version['id']);
                }
            }
        }

        $otherVersions = json_decode(file_get_contents("https://pastebin.com/raw/LVdci0Ck"), true);

        return $this->render("dashboard", [
            'title' => "Tableau de bord",
            'maxPlayers' => $maxPlayers,
            'currentVersion' => $currentVersion,
            'vanilla' => $vanilla,
            'otherVersions' => $otherVersions,
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
        $version = ucfirst(str_replace('_', ' ', $req));
        if (!empty($_GET)) {
            echo $version;
        }
        return $version;
    }
}
