<?php

namespace App\Controller;

use Core\Controller\Controller;
use Core\Controller\Helpers\ZipController;

class WorldsController extends Controller
{
    public function showWorlds()
    {
        $this->userOnly();
        $this->hasPermission('worldsManagement');

        /* Triggered if the client request a download */
        if (!empty($_POST['worldName'])) {
            $worldName = htmlspecialchars($_POST['worldName']);
            if (ZipController::make(BASE_PATH . 'minecraft_server/' . $worldName)) {
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
                $this->getFlash()->addAlert("Erreur interne, le fichier n'a pas pu être compressé");
            }
        }

        /* Triggered when the client upload a file */
        if (!empty($_FILES)) {
            $path = BASE_PATH . 'minecraft_server/';
            /* Upload the file */
            $file = $this->upload(
                $path,
                'world',
                ['zip'],
                [
                    'application/zip', 'application/x-zip-compressed',
                    'multipart/x-zip', 'application/x-compressed'
                ]
            );
            /* if the file uploaded correctly unzip it */
            if (is_string($file) && !is_null($file)) {
                if ($this->unZip($path, $file)) {
                    unlink($path . $_FILES['world']['name']);
                }
            }
        }
        $worlds = $this->getWorlds();
        $token = bin2hex(random_bytes(8));
        $_SESSION['token'] = $token;

        $this->render('worlds', [
            'title' => 'Gestion des mondes',
            'worlds' => $worlds,
            'token' => $token
        ]);
    }

    /**
     * AJAX request for deleting a minecraft World
     * Route: /worlds/delete/[*:worldName]/[*:token]
     *
     * @return void
     */
    public function deleteWorlds(string $worldName, string $token)
    {
        if ($_POST['deleteWorld'] && $_SESSION['token'] == $token) {
            $worldName = urldecode($worldName);
            $dir = BASE_PATH . "minecraft_server/" . $worldName;
            if ($this->rmDirectoryRecursivly($dir)) {
                echo "deleted";
            }
        }
    }

    /**
     * Get every minecraft wordls
     * A minecraft world has always a level.dat file
     * A world in app is defined by this rule
     *
     * @return array
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
            $zipHandle = $zip->open($path . $fileName);
            if ($zipHandle === TRUE) {
                /* If there is no level.dat delete the downloaded .zip */
                if ($zip->getFromName('level.dat') === false) {
                    unlink($path . $_FILES['world']['name']);
                    return false;
                }
                for ($i = 0; $i < $zip->numFiles; $i++) {
                    $nameI = $zip->getNameIndex($i);
                    if ($nameI != './' && $nameI != '../' && $nameI != '__MACOSX/_') {
                        $zip->extractTo($path . str_replace('.zip', '', $fileName), array($zip->getNameIndex($i)));
                    }
                }
                $zip->close();
                return true;
            } else {
                return false;
            }
        } catch (\Exception $e) {
            if (getenv("ENV_DEV") !== false) {
                throw new \Exception($e->getMessage());
            }
            $this->getFlash()->addAlert('Une erreur est survenue lors de la décompression');
            return false;
        }
    }
}
