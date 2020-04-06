<?php

namespace App\Controller;

use Core\Controller\Controller;
use GuzzleHttp\Client;

class PlayersController extends Controller
{
    public function __construct()
    {
        $this->loadModel('server');
    }

    /**
     * Render function
     *
     * @return void
     */
    public function showPlayers()
    {
        $this->userOnly();
        $this->hasPermission('playersManagement');
        // If $_POST and a valid token ->addToList
        if (
            !empty($_POST)
            && htmlspecialchars($_POST['token']) === $_SESSION['token']
        ) {
            $type =   htmlspecialchars(end(explode('_', array_key_first($_POST))));
            $name =   htmlspecialchars($_POST['add_' . $type]);
            $reason = htmlspecialchars($_POST['reason']);

            // If server is not started run the php script...
            if ($this->server->selectEverything()->getStatus() != SERVER_STARTED) {
                $this->addToList($type, $name, $reason);
            } else {
                // ...else send the appropiate commande the the server
                $this->sendCommand($type, $name, false, $reason);
            }
            sleep(2);
            $this->redirect('players', "#$type");
        }

        $this->render('players', [
            'title' => "Gestion des joueurs",
            'tab' => $type,
            'ops' => $this->getJson('ops'),
            'bannedPlayers' => $this->getJson('banned-players'),
            'whitelist' => $this->getJson('whitelist')
        ]);
    }

    /**
     * Add a player to one of JSON lists
     *
     * @param string $type
     * @return void
     */
    private function addToList(string $type, string $name, string $reason): bool
    {
        $client = new Client();
        try {
            $response = $client->request('GET', 'https://api.mojang.com/users/profiles/minecraft/' . $name);
        } catch (\Exception $e) {
            if (getenv("ENV_DEV") === "true") {
                throw $e;
            } else {
                $this->getFlash()->addAlert(
                    "Erreur: nous n'avons pas pu verifier votre uuid\n
                    Veuillez réessayer plus tard."
                );
            }
        }
        $uuid = json_decode($response->getBody(), true)['id'];
        /**
         * Regex for valid UUID format expected by JSON server files.
         * From "069a79f444e94726a5befca90e38aaf5" to "069a79f4-44e9-4726-a5be-fca90e38aaf5"
         * @var string $regex
         */
        $regex = "/^([a-f0-9]{8})\-?([a-f0-9]{4})\-?([a-f0-9]{4})\-?([a-f0-9]{4})\-?([a-f0-9]{12})$/";
        preg_match($regex, $uuid, $matches);
        array_shift($matches); // To remove the first key
        $uuid = implode('-', $matches);
        if (empty($uuid)) {
            $this->getFlash()->addAlert("Le joueur $name n'existe pas !");
            return FALSE;
        }

        // Check if player already exist.
        foreach ($this->getJson($type) as $value) {
            if ($value['name'] == $name) {
                $this->getFlash()->addAlert("Le joueur {$name} est déjà dans la liste !");
                return FALSE;
            }
        }
        $infos = [
            'uuid' => $uuid,
            'name' => $name,
        ];
        if ($type === 'ops') {
            $infos['level'] = 4;
            $infos['bypassesPlayerLimit'] = false;
        } elseif ($type === 'banned-players') {
            $infos['created']   = $this->formatAtomDate();
            $infos['source']    = "Panel";
            $infos['expires']   = "forever";
            $infos['reason']    = (empty($reason)) ? 'N/A' : $reason;
        }
        $jsonPhp = $this->getJson($type);
        $jsonPhp[] = $infos;
        if (!is_int(file_put_contents(
            BASE_PATH . "minecraft_server/{$type}.json",
            json_encode($jsonPhp, JSON_PRETTY_PRINT)
        ))) {
            $this->getFlash()->addAlert("Erreur d'écriture !");
            return FALSE;
        }
        return TRUE;
    }

    /**
     * From AJAX delete a player of one of JSON lists
     * Route: /players/deleteFromList
     *
     * @return void
     */
    public function deleteFromList(): void
    {
        if (!empty($_POST)) {
            $username = htmlspecialchars($_POST['username']);
            $token = htmlspecialchars($_POST['token']);
            $listName = htmlspecialchars($_POST['listName']);
            if (
                $this->server->selectEverything()->getStatus() != SERVER_STARTED &&
                !empty($username) &&
                $token === $_SESSION['token']
            ) {
                $resultArray = [];
                // Rebuild a new array from actual json file without the name we want to delete
                foreach ($this->getJson($listName) as $value) {
                    if ($value['name'] !== $username) {
                        $resultArray[] = $value;
                    }
                }
                // Transform this new array into json format and write it into .json
                if (is_int(file_put_contents(
                    BASE_PATH . "minecraft_server/{$listName}.json",
                    json_encode($resultArray, JSON_PRETTY_PRINT)
                ))) {
                    echo "done";
                }
            } else {
                $this->sendCommand($listName, $name, true);
                echo "done";
            }
        }
    }

    /**
     * Format the PHP datetime string to the needed by Minecraft.
     *
     * @return string
     */
    private function formatAtomDate(): string
    {
        $time = new \DateTime;
        $regex = "/^([0-9-]+)\T([0-9:]+)(.*)/";
        preg_match($regex, $time->format(\DateTime::ATOM), $matches);
        array_shift($matches);
        $timeArray = array_replace($matches, [2 => str_replace(':', '', $matches[2])]);
        $timeStr = implode(' ', $timeArray);
        return $timeStr;
    }

    private function sendCommand(string $listName, string $name, bool $ajax = false, string $reason = ""): void
    {
        $reason = ($reason === "") ? "N/A" : $reason;
        switch ($listName) {
            case 'ops':
                if ($ajax) {
                    $this->sendMinecraftCommand("deop $name");
                } else {
                    $this->sendMinecraftCommand("op $name");
                }
                break;
            case 'whitelist':
                if ($ajax) {
                    $this->sendMinecraftCommand("whitelist remove $name");
                } else {
                    $this->sendMinecraftCommand("whitelist add $name");
                }
                break;
            case 'banned-players':
                if ($ajax) {
                    $this->sendMinecraftCommand("pardon $name");
                } else {
                    $this->sendMinecraftCommand("ban $name $reason");
                }
                break;
        }
    }

    private function getJson(string $type): ?array
    {
        try {
            return json_decode(file_get_contents(BASE_PATH . "minecraft_server/$type.json"), true);
        } catch (\Throwable $e) {
            $this->getFlash()->addWarning("Le fichier \"$type.json\" n'a pas été trouvé! \nLancer le serveur une première fois et revenez ici.");
            return null;
        }
    }
}
