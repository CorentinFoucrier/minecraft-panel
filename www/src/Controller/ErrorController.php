<?php

namespace App\Controller;

use Core\Controller\Controller;

class ErrorController extends Controller
{
    public function show(int $code)
    {

        $description = [
            404 => " 404 Not Found",
            500 => " 500 Internal Server Error"
        ];

        header($_SERVER["SERVER_PROTOCOL"] . $description[$code]);

        $this->render('error', [
            "title" => "Error " . $code,
            "description" => $description[$code],
            "code" => $code
        ]);
    }
}
