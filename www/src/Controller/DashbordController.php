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

        [$versionType, $versionNumber] = explode("_", $serverEntity->getVersion());
        $maxPlayers = SERVER_PROPERTIES['max-players'];
        $whitelist = SERVER_PROPERTIES['white-list'];
        $serverPort = SERVER_PROPERTIES['server-port'];
        $socketUrl = $_SERVER['REQUEST_SCHEME'] . "://" . $_SERVER['SERVER_NAME'] . ":" . getenv('SOCKETIO_PORT');
        $versions = $this->getAvailableVersions();

        return $this->render("dashboard", [
            'title' => $this->lang('dashboard.title'),
            'ramMax' => $serverEntity->getRamMax(),
            'versionType' => $versionType,
            'versionNumber' => $versionNumber,
            'maxPlayers' => $maxPlayers,
            'whitelist' => $whitelist,
            'serverPort' => $serverPort,
            'socketUrl' => $socketUrl,
            'vanilla' => $versions['vanilla'],
            'otherVersions' => $versions['otherVersions']
        ]);
    }

    private function getAvailableVersions(): array
    {
        $releases = [];
        $versions = [
            "vanilla" => [
                "release" => [],
                "snapshot" => []
            ],
            "otherVersions" => json_decode(file_get_contents("https://pastebin.com/raw/LVdci0Ck"), true)
        ];
        $versionManifest = json_decode(file_get_contents("https://launchermeta.mojang.com/mc/game/version_manifest.json"));
        if ($versionManifest) {
            for ($i = 0; $i < count($versionManifest->versions); $i++) {
                $version = $versionManifest->versions[$i];
                if ($version->type === "release") {
                    array_push($releases, $version->id);
                } elseif ($version->type === "snapshot") {
                    array_push($versions['vanilla']['snapshot'], $version->id);
                }
            }
        }
        $versions['vanilla']['release'] = array_splice($releases, 0, -7); // Delete older versions who doesn't have server.jar

        return $versions;
    }
}
