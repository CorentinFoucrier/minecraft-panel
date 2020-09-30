<?php

namespace App\Controller;

use Core\Controller\Controller;
use Core\Controller\Services\GitHubAPI;

class DashboardController extends Controller
{
    public function __construct()
    {
        $this->loadModel("server");
    }

    /**
     * Updates each static version if there are differences.
     * Route: GET /api/dashboard/check_update
     *
     * @return void
     */
    public function checkUpdate(): void
    {
        $this->server->update(1, ["last_update" => time()]);
        $mcVersionJsonPath = BASE_PATH . "www/assets/static/minecraft_versions.json";
        $minecraft_gist = GitHubAPI::get("minecraft_versions");

        if ($mcVersionJsonPath) {
            $minecraft_static = json_decode(file_get_contents($mcVersionJsonPath), true);
            $diff1 = array_diff_assoc(json_decode($minecraft_gist, true)["release"], $minecraft_static["release"]);
            $diff2 = array_diff_assoc(json_decode($minecraft_gist, true)["snapshot"], $minecraft_static["snapshot"]);
            if (!empty($diff1) || !empty($diff2)) {
                // They are different replace static with github version
                $h = fopen($mcVersionJsonPath, "w");
                fwrite($h, $minecraft_gist);
                fclose($h);
                $this->jsonResponse(["status" => true, "message" => $this->lang("dashboard.modal.selectVersion.sync.updated")]);
            } else {
                $this->jsonResponse(["status" => false, "message" => $this->lang("dashboard.modal.selectVersion.sync.notUpdated")]);
            }
        } else {
            $h = fopen($mcVersionJsonPath, "x+");
            fwrite($h, $minecraft_gist);
            fclose($h);
        }
    }

    /**
     * Route: GET /api/dashboard/minecraft_versions
     *
     * @return void
     */
    public function minecraftVersions(): void
    {
        $this->jsonResponse(json_decode(file_get_contents(BASE_PATH . "www/assets/static/minecraft_versions.json"), true));
    }

    /**
     * Route: GET /api/dashboard/spigot_versions
     *
     * @return void
     */
    public function spigotVersions(): void
    {
        $this->jsonResponse(json_decode(file_get_contents(BASE_PATH . "www/assets/static/spigot_versions.json"), true));
    }

    /**
     * Route: GET /api/dashboard/forge_versions
     *
     * @return void
     */
    public function forgeVersions(): void
    {
        $this->jsonResponse(json_decode(file_get_contents(BASE_PATH . "www/assets/static/forge_versions.json"), true));
    }
}
