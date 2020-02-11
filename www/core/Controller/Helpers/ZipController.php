<?php

namespace Core\Controller\Helpers;

class ZipController
{
    static public function make($path): bool
    {
        // To avoid mistconfigured server
        if (!extension_loaded('zip') || !file_exists($path)) {
            return false;
        }

        $zip = new \ZipArchive();
        // Create an empty zip file
        if (!$zip->open($path . ".zip", \ZipArchive::CREATE)) {
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
             * @see https://www.php.net/manual/fr/class.traversable.php
             */
            foreach ($files as $file) {
                /* If is not "." and ".." folders */
                if ($file != "." && $file != "..") {
                    /* If $file is a directory then add an empty dir else add the file to the ZIP*/
                    if (is_dir($file)) {
                        $zip->addEmptyDir(str_replace($path . '/', '', $file . '/'));
                    } else if (is_file($file)) {
                        $zip->addFromString(str_replace($path . '/', '', $file), file_get_contents($file));
                    }
                }
            }
        } else if (is_file($path)) {
            /* Add the file to the ZIP archive */
            $zip->addFromString(basename($path), file_get_contents($path));
        }
        return $zip->close();
    }
}
