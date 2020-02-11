<?php

namespace App\Controller;

use phpseclib\Net\SSH2;
use Core\Controller\Controller;
use Core\Controller\Helpers\LogsController;

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
     * Get the latest.log file
     * for an AJAX call and return
     * n last line of this file to display
     * minecraft console
     */
    public function getLog()
    {
        return LogsController::getLog();
    }

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

    public function sendCommand(?string $p_command = null): void
    {
        $server = $this->server->selectEverything(true);
        /* If sendCommand() reached by AJAX post methode => $_POST['command'] */
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
            if ($this->hasPermission('sendCommand', false)) {
                $version = $_POST['version']; // Version number
                $gameVersion = $_POST['gameVersion']; // Game Version eg. "Vanilla"
                $json = file_get_contents('https://pastebin.com/raw/LVdci0Ck');
                $versions = json_decode($json, true);
                if ($gameVersion === "vanilla") {
                    /**
                     * expected values:
                     * array("0" => "MC", "1" => "1.14.4") OR
                     * array("0" => "SNAP", "1" => "19w42a")
                     * @var array $vanilla
                     */
                    $vanilla = explode('_', $version);
                    switch ($vanilla[0]) {
                        case 'MC':
                            $link = $versions[$gameVersion]['stable'][$vanilla[1]];
                            if ($link == null) {
                                goto error;
                            } else {
                                if (file_exists(BASE_PATH . "minecraft_server/{$version}.jar")) {
                                    echo "fromCache";
                                } else {
                                    $this->downloadServer($version, $link);
                                    echo "downloaded";
                                }
                                $this->server->update(1, ['version' => $version]);
                            }
                            break;
                        case 'SNAP':
                            $link = $versions[$gameVersion]['snapshot'][$vanilla[1]];
                            if ($link == null) {
                                goto error;
                            } else {
                                if (file_exists(BASE_PATH . "minecraft_server/{$version}.jar")) {
                                    echo "fromCache";
                                } else {
                                    $this->downloadServer($version, $link);
                                    echo "downloaded";
                                }
                                $this->server->update(1, ['version' => $version]);
                            }
                            break;
                        default:
                            error: echo "error";
                            break;
                    }
                } elseif ($gameVersion === "spigot" || $gameVersion === "forge") {
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

    public function sshCommand(string $command): void
    {
        $command = str_replace(['\'', '"'], ['\\u0027', '\\u0022'], $command);
        $ssh = new SSH2(getenv('IP'));
        try {
            $ssh->login(SHELL_USER, SELL_PWD);
        } catch (\Exception $e) {
            if (!empty($_POST['command'])) {
                echo "error";
                exit();
            } else {
                exit();
            }
        }
        /**
         * @see https://theterminallife.com/sending-commands-into-a-screen-session/
         */
        $ssh->exec("screen -S minecraft_server -X stuff '${command}'$(echo -ne '\\015')");
    }

    private function downloadServer(string $version, string $link): void
    {
        file_put_contents("/var/minecraft_server/$version.jar", fopen($link, 'r'));
    }
}
