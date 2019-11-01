<?php

namespace Core\Controller\Helpers;

class ZipController  
{
    static public function make($path)
    {
        if (!extension_loaded('zip') || !file_exists($path)) {
            return false;
        }
    
        $zip = new \ZipArchive();
        // Create an empty zip file
        if (!$zip->open($path.".zip", \ZipArchive::CREATE)) {
            return false;
        }
    
        if (is_dir($path)) {
            /**
             * @var \RecursiveIteratorIterator
             * @see https://www.php.net/manual/en/recursiveiteratoriterator.construct.php
             * @see https://www.php.net/manual/en/recursivedirectoryiterator.construct.php
             */
            $files = new \RecursiveIteratorIterator (
                new \RecursiveDirectoryIterator($path),
                \RecursiveIteratorIterator::SELF_FIRST
            );
    
            foreach ($files as $file) {
                // If is not "." and ".." folders
                if ($file != "." && $file != "..") {
                    //$file = realpath($file);
                    if (is_dir($file)) {
                        $zip->addEmptyDir(str_replace($path.'/', '', $file.'/'));
                    } else if (is_file($file)) {
                        $zip->addFromString(str_replace($path.'/', '', $file), file_get_contents($file));
                    }
                }
            }
        } else if (is_file($path)) {
            $zip->addFromString(basename($path), file_get_contents($path));
        }
        return $zip->close();
    }
}
