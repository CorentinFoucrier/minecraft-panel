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
        $serverEntity = $this->server->selectEverything();

        $maxPlayers = SERVER_PROPERTIES['max-players'];
        $token = bin2hex(random_bytes(8));
        $socketUrl = $_SERVER['REQUEST_SCHEME'] . "://" . $_SERVER['SERVER_NAME'] . ":" . getenv('SOCKETIO_PORT');
        $versions = $this->getAvailableVersions();

        $_SESSION['token'] = $token;

        return $this->render("dashboard", [
            'title' => "Tableau de bord",
            'ramMax' => $serverEntity->getRamMax(),
            'currentVersion' => $serverEntity->getVersion(),
            'maxPlayers' => $maxPlayers,
            'token' => $token,
            'socketUrl' => $socketUrl,
            'vanilla' => $versions['vanilla'],
            'otherVersions' => $versions['otherVersions']
        ]);
    }

    private function getAvailableVersions(): array
    {
        $versions = [
            "vanilla" => [
                "release" => [],
                "snapshot" => []
            ],
            "otherVersions" => json_decode(file_get_contents("https://pastebin.com/raw/LVdci0Ck"), true)
        ];
        $versionManifest = json_decode(file_get_contents("https://launchermeta.mojang.com/mc/game/version_manifest.json"), true);
        if (is_array($versionManifest)) {
            for ($i = 0; $i < count($versionManifest['versions']); $i++) {
                $version = $versionManifest['versions'][$i];
                if ($version['type'] === "release") {
                    array_push($versions['vanilla']['release'], $version['id']);
                } elseif ($version['type'] === "snapshot") {
                    array_push($versions['vanilla']['snapshot'], $version['id']);
                }
            }
        }
        return $versions;
    }
}
