<?php

namespace App\Controller;

use phpseclib\Net\SSH2;
use Core\Controller\Controller;

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
        $req = $this->server->selectEverything(true);
        $nbPlayers = $this->getServerQuery()->getPlayers();
        // If we get a players number the minecraft server is up!
        if (is_int($nbPlayers['online']) && $nbPlayers['online'] >= 0) {
            $this->server->update($req->getId(), ['status' => 2]);
        }
        switch ($req->getStatus()) {
            case 0:
                echo "stopped";
                break;
            case 1:
                echo "loading";
                break;
            case 2:
                echo "started";
                break;
            case 3:
                echo "error";
                break;
        }
    }

    /**
     * AJAX: Send a Minecraft command to Minecraft server console
     *
     * @return void
     */
    public function sendCommand(): void
    {
        $server = $this->server->selectEverything(true);
        if (
            !empty($_POST['command'])
            && $server->getStatus() == 2
            && $_POST['token'] === $_SESSION['token']
        ) {
            $command = htmlspecialchars($_POST['command']);
            $this->sshCommand($command);
            echo "done";
        } else {
            echo "error";
        }
    }

    /**
     * AJAX: To select a version from an already downloaded one
     * or download the new requested by user
     * Route: /selectVersion
     *
     * @return void
     */
    public function selectVersion()
    {
        $status = $this->server->selectBy('status', ['id' => 1], true)->getStatus();
        if (!empty($_POST) && $status != 2) {
            if ($this->hasPermission('changeVersion', false)) {
                $version = $_POST['version']; // Version eg. Release_1.14.4
                $gameVersion = $_POST['gameVersion']; // Game Version eg. "vanilla"
                $versionNumber = explode('_', $version)[1];
                if ($gameVersion === "vanilla") {
                    if (file_exists(BASE_PATH . "minecraft_server/{$version}.jar")) {
                        if ($this->server->update(1, ['version' => $version])) {
                            echo "fromCache";
                        } else {
                            echo "error";
                        }
                    } else {
                        $json = file_get_contents("https://launchermeta.mojang.com/mc/game/version_manifest.json");
                        $mojangVersions = json_decode($json, true);
                        for ($i = 0; $i < count($mojangVersions['versions']); $i++) {
                            $index = in_array($versionNumber, $mojangVersions['versions'][$i]);
                            if ($index) {
                                $launchermetaLink = json_decode(file_get_contents($mojangVersions['versions'][$i]['url']), true);
                                $link = $launchermetaLink['downloads']['server']['url'];
                                break;
                            }
                        }
                        if (
                            $link != null
                            && !$this->downloadServer($version, $link)
                            && !$this->server->update(1, ['version' => $version])
                        ) {
                            echo "downloaded";
                        } else {
                            echo "error";
                        }
                    }
                } elseif ($gameVersion === "spigot" || $gameVersion === "forge") {
                    $versions = json_decode(file_get_contents('https://pastebin.com/raw/LVdci0Ck'), true);
                    $link = $versions[$gameVersion][explode('_', $version)[1]];
                    if ($link == null) {
                        echo "error";
                    } else {
                        if (file_exists(BASE_PATH . "minecraft_server/{$version}.jar")) {
                            echo "fromCache";
                        } else {
                            $this->downloadServer($version, $link);
                            echo "downloaded";
                        }
                        $this->server->update(1, ['version' => $version]);
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
     * Send the command to the Minecraft Console via SSH protocol
     *
     * @param string $command The Minecraft command to send
     * @return void
     */
    public function sshCommand(string $command): void
    {
        $command = str_replace(['\'', '"'], ['\\u0027', '\\u0022'], $command); // Replace quotes by thier respective unicodes.
        $ssh = new SSH2(getenv('IP'));
        try {
            $ssh->login(SHELL_USER, SELL_PWD);
        } catch (\Exception $e) {
            if (!empty($_POST['command'])) {
                echo "error";
            }
            exit();
        }
        /**
         * @see https://theterminallife.com/sending-commands-into-a-screen-session/
         */
        $ssh->exec("screen -S minecraft_server -X stuff '${command}'$(echo -ne '\\015')");
    }

    /**
     * Initiates the download of the Minecraft server.jar|spigot.jar|forge.jar
     *
     * @param string $version Version tag & number eg. MC_1.14.4
     * @param string $link Direct download link to server.jar
     * @return int|false the number of bytes that were written to the file, or false on failure
     */
    private function downloadServer(string $version, string $link)
    {
        return file_put_contents(BASE_PATH . "minecraft_server/$version.jar", fopen($link, 'r'));
    }
}
