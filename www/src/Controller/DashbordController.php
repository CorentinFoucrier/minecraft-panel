<?php
namespace App\Controller;

use \Core\Controller\Controller;

class DashbordController extends Controller
{

    public function showDashboad()
    {
        return $this->render("index", [
            'title' => "Tableau de board"
        ]);
    }
}
