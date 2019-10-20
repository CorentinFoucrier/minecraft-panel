<?php
namespace Core\Controller\Helpers;

use Core\Controller\Controller;

class LogsController extends Controller
{
    static public function getLog(): void
    {
        $file  = BASE_PATH."minecraft_server/logs/latest.log";
        // récupère le nombre total de ligne dans le fichier
        $total_lines = shell_exec('cat ' . escapeshellarg($file) . ' | wc -l');
        $total_lines = intval(str_replace("\n", "", $total_lines));
        // si _GET pas vide et que _GET currentLine est inférieur aux lines total recup les lignes total moins la ligne courrante
        if (!empty($_GET['cl']) && ($_GET['cl'] < $total_lines)) {
            $lines = shell_exec('tail -n' . ($total_lines - $_GET['cl']) . ' ' . escapeshellarg($file));
        // sinon si _GET vide recup moi les 100 dernière lignes
        } else if (empty($_GET['cl'])) {
            $lines = shell_exec('tail -n100 ' . escapeshellarg($file));
        }

        $lines_array = array_filter(preg_split('#[\r\n]+#', trim($lines)));
        $lines_array[] .= $total_lines;

        if (count($lines_array)) {
            echo json_encode($lines_array);
        }
    }
}
