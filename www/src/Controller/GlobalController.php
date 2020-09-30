<?php

namespace App\Controller;

use Core\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;

class GlobalController extends Controller
{
    function __construct()
    {
        $this->loadModel('server');
    }

    public function getLang(): void
    {
        $lang = $_SESSION['lang'];
        $response = JsonResponse::fromJsonString(
            file_get_contents(BASE_PATH . "www/lang/$lang.json")
        );
        $response->send();
    }

    public function getServerInfos(): void
    {
        $serverEntity = $this->server->selectEverything();
        [$type, $number] = explode("_", $serverEntity->getVersion());
        $this->jsonResponse(
            [
                "ramMax" => $serverEntity->getRamMax(),
                "ramMin" => $serverEntity->getRamMin(),
                "type" => $type, // eg. Release
                "number" => $number // eg. 1.15.2
            ]
        );
    }

    public function getProperties(): void
    {
        $this->jsonResponse(SERVER_PROPERTIES);
    }

    /**
     * Debug only
     *
     * @return void
     */
    public function apiError()
    {
        $this->jsonResponse([
            "error" => [
                "message" => $this->lang('general.error.database'),
                "title" => $this->lang('general.error.occured')
            ]
        ], JsonResponse::HTTP_BAD_REQUEST);
    }
}
