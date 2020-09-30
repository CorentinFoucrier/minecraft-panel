<?php

namespace App\Controller;

use Core\Controller\Controller;

class AppController extends Controller
{
    public function index()
    {
        $this->userOnly();
        $this->render("layout/base", []);
    }

    public function redirectToDashboard()
    {
        $this->userOnly();
        $this->redirect('dashboard');
    }
}
