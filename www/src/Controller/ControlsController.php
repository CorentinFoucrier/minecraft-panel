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
        if (!empty($_POST['start'])) {
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
                $ssh->write("cd /home/mcserver/minecraft_server\n");
                $cn = getenv('CONTAINER_NAME');
                // If java command failed the command following pipes is launch.
                $version = $this->server->selectEverything()->getVersion;
                $ssh->write(
                    "java ".RAM_MIN." ".RAM_MAX." -jar $version -nogui && docker exec $cn php commands/jarError\n"
                );
                // The default state is "in loading" an AJAX script will send a request to know if the server is up.
                $ssh->read();
                $this->server->update($req->getId(), ['status' => 1]);
                echo "loading";
            }
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
        if (!empty($_POST['stop'])) {
            $req = $this->server->selectEverything(true);
            /* If is start then stop it */
            if ($req->getStatus() === 2) {
                // Save server status in db
                if ($this->server->update($req->getId(), ['status' => 0])) {
                    $this->getServer()->sendCommand('stop'); //Send "stop" through Rcon protocol
                    echo 'stopped'; //send confirmation to JavaScript client
                }
            }
        }
    }
}
