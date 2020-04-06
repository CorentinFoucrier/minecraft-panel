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
            }
            file_put_contents($eula, "eula=true");
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
                    $this->echoJsonData('eula')->addToast("Eula non accepté !")->echo();
                    exit();
                }
                $req = $this->server->selectEverything();
                if ($req->getStatus() === SERVER_STOPPED || $req->getStatus() === SERVER_ERROR) {
                    if ($this->server->update($req->getId(), ['status' => SERVER_LOADING])) {
                        $this->echoJsonData('loading')->addToast("Votre serveur vas démarrer", "Démmarage")->echo();
                        $version = $req->getVersion();
                        $ssh = App::getInstance()->getSsh();
                        $ssh->write("screen -R minecraft_server\n");
                        $ssh->write("cd /home/" . SHELL_USER . "/minecraft_server\n");
                        $cn = getenv('CONTAINER_NAME');
                        // When the java command is terminated the command following pipes is launched.
                        $ssh->write(
                            "java -Xms" . $req->getRamMin() . "M -Xmx" . $req->getRamMax() . "M -jar $version.jar -nogui || docker exec $cn php bin/ErrorsCheck\n"
                        );
                        // The default state is "loading" an AJAX script will send a request to know if the server is up.
                        $ssh->read();
                        sleep(1);
                        $status = $this->server->selectEverything()->getStatus();
                        if ($status === SERVER_STOPPED || $status === SERVER_ERROR) {
                            $this->echoJsonData('error')->addToast("Une erreur est survenu")->echo();
                        }
                    } else {
                        $this->echoJsonData('error')->addToast("Une erreur est survenu")->echo();
                    }
                }
            } else {
                $this->echoJsonData('eula')->addToast("Eula non accepté !")->echo();
            }
        } else {
            $this->echoJsonData('forbidden')->addToast("Eula non accepté !")->echo();
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
        if (!empty($_POST) && $_POST['token'] === $_SESSION['token']) {
            if ($this->hasPermission('startAndStop', false)) {
                $req = $this->server->selectEverything();
                /* If is start then stop it */
                if ($req->getStatus() === SERVER_STARTED) {
                    // Save server status in db
                    if ($this->server->update($req->getId(), ['status' => SERVER_STOPPED])) {
                        $this->sendMinecraftCommand('stop');
                        $this->echoJsonData('stopped')
                            ->addToast("Votre serveur à bien été arrêté !", "Arrêt")->echo();
                    } else {
                        $this->echoJsonData('error')
                            ->addToast("Erreur serveur !", "Internal server error")->echo();
                    }
                }
            } else {
                $this->echoJsonData('forbidden')
                    ->addToast('Vous n\'êtes pas autorisé à changer la version du serveur', 'Permission non accordée !')
                    ->echo();
            }
        } else {
            $this->echoJsonData('error')
                ->addToast('Une erreur est survenue !', 'Erreur interne')
                ->echo();
        }
    }
}
