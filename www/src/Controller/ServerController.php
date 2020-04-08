<?php

namespace App\Controller;

use App\App;
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
            if (!is_null($nbPlayers) && $nbPlayers >= 0) {
                if ($this->server->update($req->getId(), ['status' => SERVER_STARTED])) {
                    $this->echoJsonData('started')->echo();
                    exit(0);
                }
            }
            switch ($req->getStatus()) {
                case SERVER_STOPPED:
                    $this->echoJsonData('stopped')->echo();
                    break;
                case SERVER_LOADING:
                    $this->echoJsonData('loading')->echo();
                    break;
                case SERVER_STARTED:
                    $this->echoJsonData('started')->echo();
                    break;
                case SERVER_ERROR:
                    $this->echoJsonData('error')
                        ->addToast('Veuillez vérifier votre installation et relancer le serveur.', 'Une erreur est survenue !')
                        ->echo();
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
        if (!empty($_POST['command']) && $this->hasPermission('sendCommand', false)) {
            if ($_POST['token'] === $_SESSION['token']) {
                $status = $this->server->selectEverything()->getStatus();
                if ($status === SERVER_STARTED) {
                    $command = htmlspecialchars($_POST['command']);
                    $this->sendMinecraftCommand($command);
                    $this->echoJsonData('done')->echo();
                } else if ($status === false) {
                    $this->echoJsonData('error')
                        ->addToast('Erreur de base de données', 'Une erreur est survenue !')
                        ->echo();
                } else {
                    $this->echoJsonData('stopped')
                        ->addToast('Le serveur doit être démarrer', 'Une erreur est survenue !')
                        ->echo();
                }
            } else {
                $this->echoJsonData('error')
                    ->addToast('Requête non permise', 'Bad token')
                    ->echo();
            }
        } else {
            $this->echoJsonData('forbidden')
                ->addToast('Vous ne pouvez pas envoyer de commandes', 'Non accordé !')
                ->echo();
        }
    }

    /**
     * AJAX: To select a version from an already downloaded one
     * or download the new requested by user
     * Route: /selectVersion
     * POST datas: version, serverType, token
     *
     * @return void
     */
    public function selectVersion(?string $version = null)
    {
        if (empty($version) && $_POST['token'] === $_SESSION['token']) {
            $version = htmlspecialchars($_POST['version']); // Version eg. Release_1.14.4
        }

        [$versionType, $versionNumber] = explode('_', $version); // From "Release_1.14.4" to ["0" => "Release", "1" => "1.14.4"]
        $status = $this->server->selectBy(['status'], ['id' => 1])->getStatus();

        if ($status === SERVER_STOPPED || $status === SERVER_ERROR) {
            if ($this->hasPermission('changeVersion', false)) {
                // Check if the specified version exist on the server, download it otherwise.
                if (file_exists(BASE_PATH . "minecraft_server/{$version}.jar")) {
                    $res = ($this->server->update(1, ['version' => $version])) ? "fromCache" : "error";
                    $json = $this->echoJsonData($res);
                    if ($res === "fromCache") {
                        $json->addToast('Votre version a bien été changée.', 'Chargée depuis le cache !');
                    } else {
                        $json->addToast('Erreur base de donnée.', 'Une erreur est survenue !');
                    }
                    $json->echo();
                    exit(0);
                }
                if ($versionType === "Release" || $versionType === "Snapshot") {
                    $json = file_get_contents("https://launchermeta.mojang.com/mc/game/version_manifest.json");
                    $mojangVersions = json_decode($json);
                    if ($mojangVersions) {
                        for ($i = 0; $i < count($mojangVersions->versions); $i++) {
                            if ($mojangVersions->versions[$i]->id === $versionNumber) {
                                $launchermetaLink = json_decode(file_get_contents($mojangVersions->versions[$i]->url));
                                $link = $launchermetaLink->downloads->server->url;
                                break;
                            }
                        }
                        if ($link && filter_var($link, FILTER_VALIDATE_URL)) {
                            $this->downloadServer($version, $link);
                        } else {
                            $this->echoJsonData('error')
                                ->addToast('"launchermeta.mojang.com" ressource hors ligne', 'Mojang error')->echo();
                        }
                    } else {
                        $this->echoJsonData('error')->echo();
                    }
                } elseif ($versionType === "Spigot" || $versionType === "Forge") {
                    $versions = json_decode(file_get_contents('https://pastebin.com/raw/LVdci0Ck'));
                    for ($i = 0; $i < count($versions->$serverType); $i++) {
                        if ($versions->$serverType[$i]->id === $versionNumber) {
                            $link = $versions->$serverType[$i]->url;
                            break;
                        }
                    }
                    if ($link && filter_var($link, FILTER_VALIDATE_URL)) {
                        $this->downloadServer($version, $link);
                    }
                } else {
                    $this->echoJsonData('error')->echo();
                }
            } else {
                $this->echoJsonData('forbidden')
                    ->addToast('Vous n\'êtes pas autorisé à changer la version du serveur', 'Permission non accordée !')
                    ->echo();
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
        $ajax = (!empty($_POST) && $_POST['token'] === $_SESSION['token']) ? true : false;
        $req = $this->server->selectEverything();
        if ($req->getStatus() != 0) {
            try {
                $Query = new MinecraftPing(getenv('IP'), QUERY_PORT);
                if ($ajax) {
                    $result = $Query->Query();
                    $this->echoJsonData('success')->add('online', $result['players']['online'])->echo();
                } else {
                    $result = $Query->Query();
                    return $result['players']['online'];
                }
            } catch (MinecraftPingException $e) {
                if ($ajax) {
                    $this->echoJsonData('error')->echo();
                } else {
                    return null;
                }
            } finally {
                if ($Query) {
                    $Query->Close();
                }
            }
        }
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
                    $version = $req->getVersion();
                    if (file_exists(BASE_PATH . "/minecraft_server/{$version}.jar")) {
                        if ($this->server->update($req->getId(), ['status' => SERVER_LOADING])) {
                            $ssh = App::getInstance()->getSsh();
                            $ssh->write("screen -R minecraft_server\n");
                            $ssh->write("cd /home/" . SHELL_USER . "/minecraft_server\n");
                            $cn = getenv('CONTAINER_NAME');
                            // When the java command is terminated the command following pipes is launched.
                            $ssh->write(
                                "java -Xms" . $req->getRamMin() . "M -Xmx" . $req->getRamMax() . "M -jar $version.jar -nogui || docker exec $cn php bin/ErrorsCheck\n"
                            );
                            $ssh->read();
                            sleep(1);
                            $status = $this->server->selectEverything()->getStatus();
                            // The default state is "loading" an other AJAX script will send a request to know if the server is up.
                            if ($status === SERVER_LOADING || $status === SERVER_STARTED) {
                                $this->echoJsonData('loading')->addToast("Votre serveur vas démarrer", "Démmarage")->echo();
                            } // Else isn't needed if an error occurs checkStatus() will send the error message
                        } else {
                            $this->echoJsonData('error')->addToast("Une erreur est survenu")->echo();
                        }
                    } else {
                        $this->echoJsonData('error')->addToast("La version selectionné n'est pas présente sur le serveur.", "Une erreur est survenu")->echo();
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

    /**
     * Initiates the download of the Minecraft server.jar|spigot.jar|forge.jar
     *
     * @param string $version Version tag & number eg. MC_1.14.4
     * @param string $link Direct download link to server.jar
     * @return void
     */
    private function downloadServer(string $version, string $link): void
    {
        $jarPath = BASE_PATH . "minecraft_server/$version.jar";
        if (file_put_contents($jarPath, fopen($link, 'r'))) {
            if ($this->server->update(1, ['version' => $version])) {
                $this->echoJsonData("downloaded")
                    ->addToast('Votre version a bien été téléchargé et changée', 'Téléchargé !')
                    ->echo();
            } else {
                $this->echoJsonData("error")
                    ->addToast('Erreur base de donnée.', 'Une erreur est survenue !')
                    ->echo();
            }
        } else {
            $this->echoJsonData("error")
                ->addToast('Impossible de télécharger la version demmandé, veuillez réessayez !', 'Erreur !')
                ->echo();
        }
    }
}
