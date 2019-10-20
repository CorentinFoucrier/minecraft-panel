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

    public function checkStatus()
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
        /* If sendCommand reached by AJAX post methode => $_POST['command']... */
        if (!empty($_POST['command'])) {
            $command = htmlspecialchars($_POST['command']);
            $this->sshCommand($command);
            echo "done";
        /* ...else send the command recived by p_command */
        } else {
            $this->sshCommand($p_command);
        }
    }

    private function sshCommand(string $command): void
    {
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
}
