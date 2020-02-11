<?php

namespace Core\Controller\Helpers;

use Core\Controller\Controller;

class LogsController extends Controller
{
    public static function getLog(): void
    {
        $file  = BASE_PATH . "minecraft_server/logs/latest.log";
        // retrieve the total of line in the file
        $total_lines = shell_exec('cat ' . escapeshellarg($file) . ' | wc -l');
        $total_lines = intval(str_replace("\n", "", $total_lines));
        // if _GET isn't empty and _GET currentLine is < of total lines get total lines without the current line.
        if (!empty($_GET['cl']) && ($_GET['cl'] < $total_lines)) {
            $lines = shell_exec('tail -n' . ($total_lines - $_GET['cl']) . ' ' . escapeshellarg($file));
            // else if _GEt is empty get the last 100 lines
        } elseif (empty($_GET['cl'])) {
            $lines = shell_exec('tail -n100 ' . escapeshellarg($file));
        }

        $lines_array = array_filter(preg_split('#[\r\n]+#', trim($lines)));
        $lines_array[] .= $total_lines;

        if (count($lines_array)) {
            echo json_encode($lines_array);
        }
    }
}
