<?php
namespace App\Controller;

use xPaw\MinecraftPing;
use Core\Controller\Controller;
use xPaw\MinecraftPingException;

class ServerQueryController extends Controller
{
    public function getPlayers()
    {
        try {
            $Query = new MinecraftPing(getenv('IP'), QUERY_PORT);
            if ($_POST['getPlayer']) {
                $result = $Query->Query();
                echo json_encode($result['players']);
            } else {
                $result = $Query->Query();
                return $result['players'];
            }
        } catch(MinecraftPingException $e) {
            $this->getFlash()->addAlert($e->getMessage());
        } finally {
            if($Query) {
                $Query->Close();
            }
        }
    }
}
