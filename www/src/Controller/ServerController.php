<?php

namespace App\Controller;

use App\App;
use Core\Controller\Controller;

/**
 * This class related to the minecraft
 * server itself.
 */
class ServerController extends Controller
{
    private string $eula = BASE_PATH . "minecraft_server/eula.txt";

    public function __construct()
    {
        $this->loadModel('server');
    }

    /**
     * Route: GET /api/server/status
     * Return the current Minecraft server status stored in database
     *
     * @return void
     */
    public function getStatus(): void
    {
        $status = $this->server->selectEverything()->getStatus();
        switch ($status) {
            case SERVER_STOPPED:
                $this->jsonResponse(["status" => [
                    "text" => $this->lang("dashboard.controls.button.start"),
                    "id" => SERVER_STOPPED
                ]]);
                break;
            case SERVER_LOADING:
                $this->jsonResponse(["status" => [
                    "text" => $this->lang("dashboard.controls.button.loading"),
                    "id" => SERVER_LOADING
                ]]);
                break;
            case SERVER_STARTED:
                $this->jsonResponse(["status" => [
                    "text" => $this->lang("dashboard.controls.button.stop"),
                    "id" => SERVER_STARTED
                ]]);
                break;
            case SERVER_ERROR:
                $this->jsonResponse(["status" => [
                    "text" => $this->lang("dashboard.controls.button.stop"),
                    "id" => SERVER_ERROR
                ]]);
                $this->toast("server.getStatus.error", "general.error.occured", 400);
                break;
        }
    }

    /**
     * Route: POST /api/server/status
     * Set the current Minecraft server status in database
     *
     * @return void
     */
    public function setStatus(): void
    {
        if (!empty($_POST)) {
            $status = intval(htmlspecialchars($_POST['status']));
            ($this->server->update(1, ['status' => $status])) ? $this->jsonOK() : $this->jsonBadRequest();
        } else {
            $this->jsonBadRequest('$_POST[] has no data.');
        }
    }

    /**
     * Route: POST /api/server/send_command
     * Send a Minecraft command to Minecraft server console
     *
     * @return void
     */
    public function sendCommand(): void
    {
        if (!empty($_POST['command']) && $this->hasPermission('send_command', false)) {
            $status = $this->server->selectEverything()->getStatus();
            if ($status === SERVER_STARTED) {
                $command = htmlspecialchars($_POST['command']);
                $this->sendMinecraftCommand($command);
                $this->jsonResponse(["state" => "OK"]);
            } elseif ($status === false) {
                $this->toast("general.error.database", "general.error.occured", 400);
            } else {
                $this->toast("server.sendCommand.error.stopped", "general.error.occured", 400);
            }
        } else {
            $this->toast("server.sendCommand.forbidden", "general.error.forbidden", 400);
        }
    }

    /**
     * Route: POST /api/server/select_version
     *
     * @return void
     */
    public function selectVersion(): void
    {
        $versionType = ucfirst(htmlspecialchars($_POST["versionType"])); // Release, Snapshot, Spigot...
        $versionNumber = htmlspecialchars($_POST["versionNumber"]); // 1.16.2, 1.15, etc.
        $status = $this->server->selectBy(['status'], ['id' => 1])->getStatus();

        if ($status === SERVER_STOPPED || $status === SERVER_ERROR) {
            if ($this->hasPermission('change_version', false)) {
                // Check if the specified version exist on the server, download it otherwise.
                if (file_exists(BASE_PATH . "minecraft_server/{$versionType}_{$versionNumber}.jar")) {
                    if ($this->server->update(1, ['version' => "{$versionType}_{$versionNumber}"])) {
                        $this->toast("server.selectVersion.fromCache.message", "server.selectVersion.fromCache.title");
                    } else {
                        $this->toast("general.error.database", "general.error.occured", 500);
                    }
                    exit;
                }

                // If $versionType is a release or a snapshot it's a vanilla version
                if ($versionType === "Release" || $versionType === "Snapshot") {
                    $minecraft_versions = json_decode(file_get_contents(BASE_PATH . "/www/assets/static/minecraft_versions.json"), true);
                    $this->downloadServer("{$versionType}_{$versionNumber}", $minecraft_versions[lcfirst($versionType)][$versionNumber]["url"]);
                } else {
                    $path = BASE_PATH . "/www/assets/static/$versionType.json";
                    if (is_file($path)) {
                        $other_versions = json_decode($path, true);
                        $this->downloadServer("{$versionType}_{$versionNumber}", $minecraft_versions[lcfirst($versionType)][$versionNumber]["url"]);
                    } else {
                        $this->jsonForbidden($this->lang("general.error.forbidden"));
                    }
                }
            } else {
                $this->jsonForbidden($this->lang("server.selectVersion.forbidden"));
            }
        } else {
            $this->toast("server.changeVersion.error.stopped", null, 500);
        }
    }

    /**
     * Check if eula is accepted or not
     * Route: GET /api/server/eula
     *
     * @return void
     */
    public function getEula(): void
    {
        if (!file_exists($this->eula)) {
            $h = fopen($this->eula, "x");
            fclose($h); // create eula.txt if not exist
        }

        $h = fopen($this->eula, "r+");
        $content = fread($h, filesize($this->eula));
        $re = '/(eula)=(.+)/m';
        preg_match($re, $content, $matches);
        if (end($matches) === "true") {
            $this->jsonResponse(["status" => true]);
        } else if (end($matches) === "false") {
            $this->jsonResponse(["status" => false]);
        } else {
            $this->toast("general.error.occured", null, 500);
        }
        fclose($h);
    }

    /**
     * Route: POST /api/server/eula
     *
     * @return void
     */
    public function acceptEula()
    {
        if ($_POST['accept'] === "true") {
            if (!file_exists($this->eula)) {
                $h = fopen($this->eula, "x");
                fclose($h); // create eula.txt if not exist
            }
            if (!file_put_contents($this->eula, "eula=true")) {
                $this->toast("general.error.internal", "general.error.occured", 500);
            }
        }
    }

    /**
     * Route: POST /api/server/start
     *
     * @return void
     */
    public function start(): void
    {
        if ($this->hasPermission('start_and_stop', false)) {
            if (file_exists($this->eula)) {
                $eulaTxt = file_get_contents($this->eula);
                preg_match('/(eula)=(.+)/m', $eulaTxt, $matches);

                if (end($matches) === "false") {
                    $this->toast('server.start.eula', null, 400);
                    exit();
                }

                $req = $this->server->selectEverything();
                if ($req->getStatus() === SERVER_STOPPED || $req->getStatus() === SERVER_ERROR) {
                    $version = $req->getVersion();
                    if (file_exists(BASE_PATH . "/minecraft_server/{$version}.jar")) {
                        // Start the "loading" mode. NodeJS script will run a loop to know if the state 
                        // has changed, based on if NodeJS can retrieve the player count
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
                            if ($status === SERVER_LOADING || $status === SERVER_STARTED) {
                                $this->toast("server.start.loading.message", "server.start.loading.title");
                            }
                        } else {
                            $this->toast("general.error.internal", "general.error.occured", 500);
                        }
                    } else {
                        $this->toast("general.error.internal", "general.error.occured", 500);
                    }
                }
            } else {
                $this->toast("server.start.eula", null, 400);
            }
        } else {
            $this->toast("server.start.error.forbidden", null, 403);
        }
    }

    /**
     * Route: POST /api/server/stop
     *
     * @return void
     */
    public function stop(): void
    {
        if ($this->hasPermission('start_and_stop', false)) {
            $req = $this->server->selectEverything();
            // If is start then stop it
            if ($req->getStatus() === SERVER_STARTED) {
                // Send stop command to the Minecraft server.
                $this->sendMinecraftCommand('stop');
                // Save server status in db
                $this->server->update($req->getId(), ['status' => SERVER_STOPPED]);
                $this->toast("server.stop.stopped.message", "server.stop.stopped.title");
            }
        } else {
            $this->toast("server.stop.error.forbidden", "general.error.forbidden", 403);
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
                $this->toast('server.downloadServer.downloaded.message', 'server.downloadServer.downloaded.title');
            } else {
                $this->jsonInternal($this->lang('general.error.database'));
            }
        } else {
            $this->jsonInternal($this->lang('server.downloadServer.error.cantDownload'));
        }
    }
}
