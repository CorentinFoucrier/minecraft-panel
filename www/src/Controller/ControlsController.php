<?php

namespace App\Controller;

use App\App;
use Core\Controller\Controller;

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
                $eulaTxt = file_get_contents($eula);
                preg_match_all('/(.+)=(.*)/m', $eulaTxt, $matches, PREG_SET_ORDER, 0);
                // If eula.txt exist but set to false.
                if (end($matches[0]) == "false") {
                    echo "eula";
                    exit();
                }
                $req = $this->server->selectEverything();
                if ($req->getStatus() === SERVER_STOPPED || $req->getStatus() === SERVER_ERROR) {
                    echo ($this->server->update($req->getId(), ['status' => SERVER_LOADING])) ? "loading" : "error";
                    $version = $req->getVersion();
                    $ssh = App::getInstance()->getSsh();
                    $ssh->write("screen -R minecraft_server\n");
                    $ssh->write("cd /home/" . SHELL_USER . "/minecraft_server\n");
                    $cn = getenv('CONTAINER_NAME');
                    // When the java command is terminated the command following pipes is launched.
                    $ssh->write(
                        "java -Xms" . $req->getRamMin() . "M -Xmx" . $req->getRamMax() . "M -jar $version.jar -nogui || docker exec $cn php bin/ErrorsCheck\n"
                    );
                    // The default state is "in loading" an AJAX script will send a request to know if the server is up.
                    $ssh->read();
                    if ($this->server->selectEverything()->getStatus() === SERVER_STOPPED) {
                        echo "error";
                    }
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
            $req = $this->server->selectEverything();
            /* If is start then stop it */
            if ($req->getStatus() === SERVER_STARTED) {
                // Save server status in db
                if ($this->server->update($req->getId(), ['status' => SERVER_STOPPED])) {
                    $this->sendMinecraftCommand('stop');
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
