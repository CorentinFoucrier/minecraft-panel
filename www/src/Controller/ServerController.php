<?php

namespace App\Controller;

use xPaw\MinecraftPing;
use Core\Controller\Controller;
use xPaw\MinecraftPingException;

/**
 * This class related to the minecraft
 * server itself.
 */
class ServerController extends Controller
{
    public function __construct()
    {
        $this->loadModel('server');
    }

    /**
     * AJAX: Check the BDD stored server status and return it to the client.
     *
     * @return void
     */
    public function checkStatus(): void
    {
        if (!empty($_POST) && $_POST['token'] === $_SESSION['token']) {
            unset($_POST);
            $req = $this->server->selectEverything();
            $nbPlayers = $this->getOnlinePlayers();
            // If we get a players number the minecraft server is up!
            if (!is_null($nbPlayers['online']) && $nbPlayers['online'] >= 0) {
                if ($this->server->update($req->getId(), ['status' => SERVER_STARTED])) {
                    echo "started";
                    exit(0);
                }
            }
            switch ($req->getStatus()) {
                case SERVER_STOPPED:
                    echo "stopped";
                    break;
                case SERVER_LOADING:
                    echo "loading";
                    break;
                case SERVER_STARTED:
                    echo "started";
                    break;
                case SERVER_ERROR:
                    echo "error";
                    break;
            }
        }
    }

    /**
     * AJAX: Send a Minecraft command to Minecraft server console
     *
     * @return void
     */
    public function sendCommand(): void
    {
        $server = $this->server->selectEverything();
        if (
            !empty($_POST['command'])
            && $server->getStatus() == SERVER_STARTED
            && $_POST['token'] === $_SESSION['token']
        ) {
            $command = htmlspecialchars($_POST['command']);
            $this->sendMinecraftCommand($command);
            echo "done";
        } else {
            echo "error";
        }
    }

    /**
     * AJAX: To select a version from an already downloaded one
     * or download the new requested by user
     * Route: /selectVersion
     * POST datas: version, gameVersion, token
     *
     * @return void
     */
    public function selectVersion()
    {
        $version = htmlspecialchars($_POST['version']); // Version eg. Release_1.14.4
        $gameVersion = htmlspecialchars($_POST['gameVersion']); // Game Version eg. "vanilla"
        $versionNumber = explode('_', $version)[1]; // From "Release_1.14.4" to ["0" => "Release", "1" => "1.14.4"]
        $status = $this->server->selectBy(['status'], ['id' => 1])->getStatus();

        if (!empty($_POST) && $status !== SERVER_STARTED) {
            if (
                $this->hasPermission('changeVersion', false) &&
                $_POST['token'] === $_SESSION['token']
            ) {
                if ($gameVersion === "vanilla") {
                    // Check if the specified version exist on the server, download it otherwise.
                    if (file_exists(BASE_PATH . "minecraft_server/{$version}.jar")) {
                        echo ($this->server->update(1, ['version' => $version])) ? "fromCache" : "error";
                    } else {
                        $json = file_get_contents("https://launchermeta.mojang.com/mc/game/version_manifest.json");
                        $mojangVersions = json_decode($json, true);
                        if ($mojangVersions) {
                            for ($i = 0; $i < count($mojangVersions['versions']); $i++) {
                                if (in_array($versionNumber, $mojangVersions['versions'][$i])) {
                                    $launchermetaLink = json_decode(file_get_contents($mojangVersions['versions'][$i]['url']), true);
                                    $link = $launchermetaLink['downloads']['server']['url'];
                                    break;
                                }
                            }
                            echo (is_string($link)
                                && $this->downloadServer($version, $link)
                                && $this->server->update(1, ['version' => $version])) ? "downloaded" : "error";
                        } else {
                            echo "error";
                        }
                    }
                } elseif ($gameVersion === "spigot" || $gameVersion === "forge") {
                    // Check if the specified version exist on the server, download it otherwise.
                    if (file_exists(BASE_PATH . "minecraft_server/{$version}.jar")) {
                        echo ($this->server->update(1, ['version' => $version])) ? "fromCache" : "error";
                    } else {
                        $versions = json_decode(file_get_contents('https://pastebin.com/raw/LVdci0Ck'), true);
                        $link = $versions[$gameVersion][$versionNumber];
                        echo ($link
                            && $this->downloadServer($version, $link)
                            && $this->server->update(1, ['version' => $version])) ? "downloaded" : "error";
                    }
                } else {
                    echo "error";
                }
            } else {
                echo "not allowed";
            }
        }
    }

    /**
     * Get the actual player count on the server
     * via xPaw\MinecraftPing if the server is not stopped
     *
     */
    public function getOnlinePlayers()
    {
        $req = $this->server->selectEverything();
        if ($req->getStatus() != 0) {
            try {
                $Query = new MinecraftPing(getenv('IP'), QUERY_PORT);
                if (!empty($_POST) && $_POST['token'] === $_SESSION['token']) {
                    $result = $Query->Query();
                    echo json_encode($result['players']);
                } else {
                    $result = $Query->Query();
                    return $result['players'];
                }
            } catch (MinecraftPingException $e) {
                // TODO: Find a way to display an error when the Minecraft server
                // can not be reached by xPaw\MinecraftPing
            } finally {
                if ($Query) {
                    $Query->Close();
                }
            }
        }
    }


    /**
     * Initiates the download of the Minecraft server.jar|spigot.jar|forge.jar
     *
     * @param string $version Version tag & number eg. MC_1.14.4
     * @param string $link Direct download link to server.jar
     * @return boolean
     */
    private function downloadServer(string $version, string $link): bool
    {
        $jarPath = BASE_PATH . "minecraft_server/$version.jar";
        if (file_put_contents($jarPath, fopen($link, 'r'))) {
            chmod($jarPath, 0777);
            return TRUE;
        } else {
            return FALSE;
        }
    }
}
