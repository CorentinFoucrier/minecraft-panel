<?php
namespace App\Controller;

use Core\Controller\Controller;

class WorldsController extends Controller
{
    public function showWorlds()
    {
        $this->userOnly();
        $this->hasPermission('worldsManagement');
        if (!empty($_FILES)) {
            $path = BASE_PATH.'minecraft_server/';
            if (is_string($file = $this->upload(
                $path,
                'world', ['zip'],
                ['application/zip', 'application/x-zip-compressed',
                'multipart/x-zip', 'application/x-compressed']
            ))) {
                if ($this->unZip($path, $file)) {
                    unlink($path.$_FILES['world']['name']); 
                }
            }
        } else {
            $this->getFlash()->addAlert('Veuillez choisir un fichier.');
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
            $dir = BASE_PATH."minecraft_server/".$worldName;
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
    private function getWorlds(): array
    {
        $mc_serv_folder = glob(BASE_PATH.'minecraft_server/*', GLOB_ONLYDIR);
        foreach ($mc_serv_folder as $folderPath) {
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
        $x = $zip->open("/var/minecraft_server/".$fileName);
        if ($x === TRUE) {
            $zip->extractTo("/var/minecraft_server/"); // change this to the correct site path
            $zip->close();
            return true;
        } else {
            $this->getFlash()->addAlert('Une erreur est survenue lors de la décompression');
            return false;
        }
    }
}
