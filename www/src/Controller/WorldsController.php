<?php

namespace App\Controller;

use Core\Controller\Controller;
use Core\Controller\Services\ZipService;

class WorldsController extends Controller
{
    public function showWorlds()
    {
        $this->userOnly();
        $this->hasPermission('worlds');

        $worlds = $this->getWorlds();

        $this->render('worlds', [
            'title' => $this->lang('worlds.title'),
            'worlds' => $worlds
        ]);
    }

    /**
     * Download the specified world name
     * Route: /worlds/download
     *
     * @return void
     */
    public function downloadWorld(): void
    {
        if (!empty($_POST['worldName']) && $_POST['token'] === $_SESSION['token']) {
            $worldName = htmlspecialchars($_POST['worldName']);
            if (ZipService::make(BASE_PATH . 'minecraft_server/' . $worldName)) {
                ignore_user_abort(true); // If the client disconnect the script will stop
                $fileName = BASE_PATH . 'minecraft_server/' . $worldName . '.zip';
                header('Content-type: application/zip');
                header('Content-Length: ' . filesize($fileName));
                header('Content-Disposition: attachment; filename="' . $worldName . '.zip"');
                readfile($fileName);
                if (connection_aborted()) {
                    unlink($fileName);
                }
                unlink($fileName);
            } else {
                $this->getFlash()->addAlert($this->lang('worlds.error.notCompressed'));
            }
        } else {
            $this->error(404);
        }
    }

    /**
     * Upload a Minecraft world
     *
     * @return void
     */
    public function uploadWorld(): void
    {
        if (!empty($_FILES)) {
            $path = BASE_PATH . 'minecraft_server/';
            $file = $this->upload($path, 'world', ['zip'], [
                'application/zip', 'application/x-zip-compressed',
                'multipart/x-zip', 'application/x-compressed'
            ]);
            if (is_string($file)) {
                if ($this->unZip($path, $file)) {
                    unlink($path . $file);
                }
            }
            $this->redirect('worlds');
        }
    }

    /**
     * AJAX request for deleting a minecraft World
     * Route: /worlds/delete
     *
     * @return void
     */
    public function deleteWorlds()
    {
        if (!empty($_POST)) {
            $token = htmlspecialchars($_POST['token']);
            $worldName = htmlspecialchars($_POST['worldName']);
            if ($token === $_SESSION['token']) {
                if ($this->rmDirectoryRecursivly(BASE_PATH . "minecraft_server/$worldName")) {
                    $this->echoJsonData('success')->addToast($this->lang('worlds.toastr.deleted'))->echo();
                } else {
                    $this->echoJsonData('error')->addToast($this->lang('general.error.retry'), $this->lang('general.error.occured'))->echo();
                }
            }
        }
    }

    /**
     * Get every minecraft wordls
     * A minecraft world has always a level.dat file
     * A world in app is defined by this rule
     *
     * @return null|array
     */
    private function getWorlds(): ?array
    {
        $mcServerFolder = glob(BASE_PATH . 'minecraft_server/*', GLOB_ONLYDIR);
        foreach ($mcServerFolder as $folderPath) {
            foreach (scandir($folderPath) as $val) {
                if ($val === "level.dat") {
                    $worlds[] = end(explode('/', $folderPath));
                }
            }
        }
        return $worlds;
    }

    /**
     * Extract the downloaded zip
     *
     * @see https://www.php.net/manual/en/ziparchive.extractto.php
     * @param string $path
     * @param string $fileName
     * @return bool
     */
    private function unZip(string $path, string $fileName): bool
    {
        $zip = new \ZipArchive;
        try {
            if ($zip->open($path . $fileName) === true) {
                /* If there is no level.dat delete the downloaded .zip */
                if ($zip->getFromName('level.dat') === false) {
                    unlink($path . $fileName);
                    return false;
                }
                for ($i = 0; $i < $zip->numFiles; $i++) {
                    $nameI = $zip->getNameIndex($i);
                    if ($nameI != './' && $nameI != '../' && $nameI != '__MACOSX/_') {
                        $zip->extractTo($path . str_replace('.zip', '', $fileName), array($zip->getNameIndex($i)));
                    }
                }
                return $zip->close();
            } else {
                return false;
            }
        } catch (\Exception $e) {
            if (getenv("ENV_DEV") === "true") {
                throw new \Exception($e->getMessage());
            }
            $this->getFlash()->addAlert($this->lang('worlds.error.uncompressed'));
            return false;
        }
    }
}
