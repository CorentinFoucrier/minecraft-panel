<?php
namespace App\Controller;

use Core\Controller\Controller;

class DashbordController extends Controller
{
    public function showDashboad()
    {
        $config = SERVER_PROPERTIES;
        $maxPlayers = $config['max-players'];
        return $this->render("index", [
            'title' => "Tableau de board",
            'maxPlayers' => $maxPlayers
        ]);
    }

    /**
     * Get online players through AJAX
     *
     * @return void
     */
    public function getOnlinePlayers(): void
    {
        $players = $this->getServerQuery()->getPlayers();
        echo $players['online'];
    }
}
