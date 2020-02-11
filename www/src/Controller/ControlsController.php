<?php

namespace App\Controller;

use Core\Controller\Controller;
use phpseclib\Net\SSH2;

class ControlsController extends Controller
{
    public function __construct()
    {
        $this->loadModel('server');
    }

    /**
     * Execute ssh command for starting
     * the server.jar with AJAX POST
     *
     * @return void
     */
    public function start(): void
    {
        $eula = BASE_PATH . "minecraft_server/eula.txt";

        if ($_POST['accept'] === "true") {
            if (!file_exists($eula)) {
                $h = fopen($eula, "-w");
                fclose($h); // create eula.txt if not exist
                file_put_contents($eula, "eula=true");
            } else {
                file_put_contents($eula, "eula=true");
            }
        }

        if (
            !empty($_POST['token'])
            && $this->hasPermission('startAndStop', false)
            && $_POST['token'] === $_SESSION['token']
        ) {
            if (file_exists($eula)) {
                try {
                    $eulaTxt = file_get_contents($eula);
                    $regex = '/(.+)=(.*)/m';
                    preg_match_all($regex, $eulaTxt, $matches, PREG_SET_ORDER, 0);
                    if (end($matches[0]) == "false") {
                        echo "eula";
                        exit();
                    }
                } catch (\Exception $e) {
                    echo "error";
                }
                /* If isn't start or has an error then start it */
                $req = $this->server->selectEverything(true);
                /* If the server is in stopped or error state */
                if ($req->getStatus() === 0 || $req->getStatus() === 3) {
                    $ssh = new SSH2(getenv('IP'));
                    try {
                        $ssh->login(SHELL_USER, SELL_PWD);
                    } catch (\Exception $e) {
                        exit('Login failed!');
                    }
                    $ssh->write("screen -R minecraft_server\n");
                    $ssh->write("cd /home/" . SHELL_USER . "/minecraft_server\n");
                    $cn = getenv('CONTAINER_NAME');
                    // If java command failed the command following pipes is launch.
                    $version = $this->server->selectEverything(true)->getVersion();
                    $ssh->write(
                        "java -Xms" . RAM_MIN . " -Xmx" . RAM_MAX . " -jar $version.jar -nogui && docker exec $cn php commands/jarError\n"
                    );
                    // The default state is "in loading" an AJAX script will send a request to know if the server is up.
                    $ssh->read();
                    $this->server->update($req->getId(), ['status' => 1]);
                    echo "loading";
                }
            } else {
                echo "eula";
            }
        } else {
            echo "not allowed";
        }
    }

    /**
     * Execute ssh command for stopping
     * the server.jar with AJAX POST
     *
     * @return void
     */
    public function stop(): void
    {
        if (
            !empty($_POST['token'])
            && $this->hasPermission('startAndStop', false)
            && $_POST['token'] === $_SESSION['token']
        ) {
            $req = $this->server->selectEverything(true);
            /* If is start then stop it */
            if ($req->getStatus() == 2) {
                // Save server status in db
                if ($this->server->update($req->getId(), ['status' => 0])) {
                    $this->getServer()->sshCommand('stop');
                    echo "stopped"; // Send confirmation to JavaScript client
                } else {
                    echo "error";
                }
            }
        } else {
            echo "not allowed";
        }
    }
}
