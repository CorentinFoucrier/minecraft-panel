<?php

namespace App\Controller;

use Core\Controller\Controller;
use Core\Controller\RouterController as Router;
use Symfony\Component\HttpFoundation\JsonResponse;

class ApiController extends Controller
{
    public function __construct()
    {
        // Token send by NodeJS in the HTTP Authorization header
        if (!empty($auth = getallheaders()["Authorization"])) {
            // JSON shared file between PHP and NodeJS
            $json = json_decode(file_get_contents(BASE_PATH . "www/nodejs/config/auth.json"), true);
            if (substr($auth, 6) === $json["API_KEY"]) return;
        }

        if (
            (empty($_COOKIE['token']) || empty($_SESSION['token']))
            ||
            $_COOKIE['token'] !== $_SESSION['token']
        ) {
            $response = new JsonResponse(
                [
                    "error" => [
                        "status" => JsonResponse::HTTP_UNAUTHORIZED,
                        "description" => "Access unauthorized. You must to be logged in."
                    ]
                ],
                JsonResponse::HTTP_UNAUTHORIZED
            );
            $response->send();
            exit();
        };
    }

    public function manager()
    {
        $router = new Router();
        $router
            ->get("/api/error", "Global#apiError")

            ->get("/api/server_infos", "Global#getServerInfos")
            ->get("/api/properties", "Global#getProperties")
            ->get("/api/lang", "Global#getLang")
            ->get("/api/get_user_infos", "Global#getUserInfos")
            //
            ->get("/api/dashboard/check_update", "Dashboard#checkUpdate", "check_update")
            ->get("/api/dashboard/minecraft_versions", "Dashboard#minecraftVersions", "minecraft_versions")
            ->get("/api/dashboard/spigot_versions", "Dashboard#spigotVersions", "spigot_versions")
            ->get("/api/dashboard/forge_versions", "Dashboard#forgeVersions", "forge_versions")
            ->post("/api/server/send_command", "Server#sendCommand", "send_command")
            ->post("/api/server/select_version", "Server#selectVersion", "select_version")
            ->get("/api/server/status", "Server#getStatus", "get_server_status")
            ->post("/api/server/status", "Server#setStatus", "set_server_status")
            ->get("/api/server/eula", "Server#getEula", "get_eula")
            ->post("/api/server/eula", "Server#acceptEula", "accept_eula")
            ->post("/api/server/start", "Server#start", "server_start")
            ->post("/api/server/stop", "Server#stop", "server_stop")
            ->post("/api/players/whitelist", "Players#whitelistAdd", "whitelist_add")
            ->delete("/api/players/whitelist/[a:username]", "Players#WhitelistRemove", "whitelist_remove")
            ->post("/api/players/ban", "Players#ban", "ban")
            ->delete("/api/players/ban/[a:username]", "Players#pardon", "pardon")
            ->post("/api/players/op", "Players#op", "op")
            ->delete("/api/players/op/[a:username]", "Players#deop", "deop")
            ->get("/api/worlds", "Worlds#get", "worlds")
            ->post("/api/worlds", "Worlds#add", "worlds_add")
            ->patch("/api/worlds/[a:world_name]", "Worlds#update", "worlds_update")
            ->delete("/api/worlds/[a:world_name]", "Worlds#delete", "worlds_delete")
            ->delete("/api/account/[a:username]", "Account#delete", "delete_account")
            ->post("/api/account/change_password", "Account#changePassword", "change_password")
            ->post("/api/account/change_language", "Account#changeLanguage", "change_language")
            ->get("/api/account/languages", "Account#availableLanguages", "available_languages");

        $match = $router->match();

        if (is_array($match)) {
            [$controller, $method] = explode("#", $match['target']);
            $controller = "App\\Controller\\" . ucfirst($controller) . "Controller";
            (new $controller())->$method(...array_values($match['params']));
        } elseif ($match === Router::METHOD_ERROR) {
            $response = new JsonResponse(
                [
                    "description" => "Requested URI can not be reached with HTTP " . $_SERVER['REQUEST_METHOD'] . " method."
                ],
                JsonResponse::HTTP_BAD_REQUEST
            );
            $response->send();
            exit();
        } elseif ($match === Router::NO_MATCH) {
            $response = new JsonResponse(
                [
                    "description" => "Requested URI does't exist."
                ],
                JsonResponse::HTTP_NOT_FOUND
            );
            $response->send();
            exit();
        } else {
            $response = new JsonResponse(
                [
                    "description" => "Internal server error"
                ],
                JsonResponse::HTTP_INTERNAL_SERVER_ERROR
            );
            $response->send();
            exit();
        }
    }
}
