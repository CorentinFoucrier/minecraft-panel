<?php
namespace App\Controller;

use Core\Controller\Controller;

class PlayersController extends Controller
{

    private $ops;

    private $bannedPlayers;

    private $whitelist;

    public function __construct()
    {
        $this->loadModel('server');
        $this->ops = json_decode(file_get_contents(BASE_PATH.'minecraft_server/ops.json'), true);
        $this->bannedPlayers = json_decode(file_get_contents(BASE_PATH."minecraft_server/banned-players.json"), true);
        $this->whitelist = json_decode(file_get_contents(BASE_PATH."minecraft_server/whitelist.json"), true);
    }

    public function showPlayers()
    {
        $token = htmlspecialchars($_POST['token']);
        if (!empty($_POST) && $token === $_SESSION['token']) {
            $type =   htmlspecialchars( end(explode('_', array_key_first($_POST))) );
            $name =   htmlspecialchars( $_POST['add_'.$type] );
            $reason = htmlspecialchars( $_POST['reason'] );

            /* If server is not started do the php script else send the appropiate commande the the server */
            if ($this->server->selectEverything(true)->getStatus() != 2) {
                $client = new \GuzzleHttp\Client();
                $response = $client->request('GET', 'https://api.mojang.com/users/profiles/minecraft/'.$name);
                $uuid = json_decode($response->getBody(), true)['id'];
                /**
                 * Regex for valid UUID format expected by JSON server files.
                 * Like from this "7125ba8b1c864508b92bb5c042ccfe2b" to this 7125ba8b-1c86-4508-b92bb-5c042ccfe2b
                 * @var string $regex
                 */
                $regex = "/^([a-f0-9]{8})\-?([a-f0-9]{4})\-?([a-f0-9]{4})\-?([a-f0-9]{4})\-?([a-f0-9]{12})$/";

                preg_match($regex, $uuid, $matches);
                array_shift($matches); // To remove the first key
                $uuid = implode('-', $matches);
                /* If uuid is good we can it to the list. */
                if (!empty($uuid)) {
                    $this->addToList($type, $name, $uuid, $reason);
                } else {
                    $this->getFlash()->addAlert("Le joueur $name n'existe pas !");
                }
            } else {
                switch ($type) {
                    case 'ops':
                        $this->getServer()->sendCommand("op $name");
                        break;
                    case 'whitelist':
                        $this->getServer()->sendCommand("whitelist add $name");
                        break;
                    case 'banned-players':
                        $this->getServer()->sendCommand("ban $name");
                        break;
                }
            }
        }

        $_SESSION['token'] = bin2hex(random_bytes(8));
        $this->render('players', [
            'title' => "Gestion des joueurs",
            'token' => $_SESSION['token'],
            'tab' => $type,
            'ops' => $this->ops,
            'bannedPlayers' => $this->bannedPlayers,
            'whitelist' => $this->whitelist
        ]);
    }

    /**
     * Add a player to one of JSON lists
     *
     * @param string $type
     * @return void
     */
    private function addToList(string $type, string $name, string $uuid, string $reason): bool
    {
        /* Check if player already exist. */
        foreach ($this->$type as $value) {
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
            $infos['reason']    = (empty($reason)) ? 'No reason :(' : $reason;
        }
        $this->$type[] = $infos;
        if (!is_int(file_put_contents(
            BASE_PATH."minecraft_server/{$type}.json",
            json_encode($this->$type, JSON_PRETTY_PRINT)
        ))) {
            $this->getFlash()->addAlert("Erreur d'écriture !");
            return FALSE;
        }
        return TRUE;
    }

    /**
     * From AJAX delete a player of one of JSON lists
     * Route: /players/ /[*:type]
     *
     * @param string $type
     * @return void
     */
    public function deleteFromList(string $type): void
    {
        if ($name = $_POST['name']) {
            if ($_POST['token'] === $_SESSION['token']) {
                $resultArray = [];
                /* Rebuild a new array from actual json file without the name we want to delete */
                foreach ($this->$type as $value) {
                    if ($value['name'] !== $name) {
                        $resultArray[] = $value;
                    }
                }
                /* Transform this new array into json format and write it into .json */
                if (is_int(file_put_contents(
                    BASE_PATH."minecraft_server/{$type}.json",
                    json_encode($resultArray, JSON_PRETTY_PRINT)
                ))) {
                    echo "done";
                }
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
        $timeArray = array_replace( $matches, [2 => str_replace(':', '', $matches[2])] );
        $timeStr = implode(' ', $timeArray);
        return $timeStr;
    }
}