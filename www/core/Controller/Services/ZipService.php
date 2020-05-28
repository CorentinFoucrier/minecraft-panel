<?php

namespace Core\Controller\Services;

class ZipService
{
    public static function make($path): bool
    {
        // To avoid mistconfigured server
        if (!extension_loaded('zip') || !file_exists($path)) {
            return false;
        }

        $zip = new \ZipArchive();
        // Create an empty zip file
        if (!$zip->open($path . ".zip", \ZipArchive::CREATE | \ZipArchive::OVERWRITE)) {
            return false;
        }

        if (is_dir($path)) {
            /**
             * @var \RecursiveIteratorIterator
             * @see https://www.php.net/manual/en/recursiveiteratoriterator.construct.php
             * @see https://www.php.net/manual/en/recursivedirectoryiterator.construct.php
             */
            $dir = new \RecursiveDirectoryIterator($path);
            $files = new \RecursiveIteratorIterator($dir, \RecursiveIteratorIterator::SELF_FIRST);

            /**
             * We can use foreach because the first param of \RecursiveIteratorIterator
             * have a type hint of the Traversable interface that allow us to use foreach.
             * @see https://www.php.net/manual/en/class.traversable.php
             */
            foreach ($files as $file) {
                // $file is a SplFileInfo[] see: https://www.php.net/manual/en/class.splfileinfo.php#splfileinfo.synopsis
                // Skip directories they would be added automatically
                if (!$file->isDir()) {
                    $zip->addFromString(str_replace($path . '/', '', $file), file_get_contents($file));
                }
            }
        } elseif (is_file($path)) {
            /* Add the file to the ZIP archive */
            $zip->addFromString(basename($path), file_get_contents($path));
        } else {
            return false;
        }

        return $zip->close();
    }
}
