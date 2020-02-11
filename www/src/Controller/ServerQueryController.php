<?php

namespace App\Controller;

use xPaw\MinecraftPing;
use Core\Controller\Controller;
use xPaw\MinecraftPingException;

class ServerQueryController extends Controller
{
    public function __construct()
    {
        $this->loadModel('server');
    }
    /**
     * Get the actual player count on the server
     * via xPaw\MinecraftPing if the server is not stopped
     *
     * @return void
     */
    public function getPlayers()
    {
        $req = $this->server->selectEverything(true);
        if ($req->getStatus() != 0) {
            try {
                $Query = new MinecraftPing(getenv('IP'), QUERY_PORT);
                if ($_POST['getPlayer']) {
                    $result = $Query->Query();
                    echo json_encode($result['players']);
                } else {
                    $result = $Query->Query();
                    return $result['players'];
                }
            } catch (MinecraftPingException $e) {
                // TODO: Find a way to display an error when the Minecraft server
                // can not be reached by xPaw\MinecraftPing
            } finally {
                if ($Query) {
                    $Query->Close();
                }
            }
        }
    }
}
