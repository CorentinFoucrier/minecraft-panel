<?php

namespace Core\Controller;

class UploadController
{
    /**
     * Upload a file
     *
     * @param string $path Path of the file to upload
     * @param string $attrName Name of HTML attribute 'name'
     * @param array $exentions Array of extentions
     * @param string $mimeTypes The MIME types
     * @return null|string
     */
    public static function upload(
        string $path,
        string $attrName,
        array $exentions,
        array $mimeTypes
    ): ?string {
        $file = $_FILES[$attrName];
        $fileName = str_replace(' ', '_', htmlspecialchars($file['name']));
        // If file is not empty
        if (!empty($fileName)) {
            $fileExt  = pathinfo($fileName, PATHINFO_EXTENSION);
            // Check with wanted exentions
            if (in_array(strtolower($fileExt), $exentions)) {
                $fileInfos = mime_content_type($file['tmp_name']);
                // Now check mime type
                if (in_array($fileInfos, $mimeTypes)) {
                    // Check if there is no errors
                    if (isset($file['error']) && UPLOAD_ERR_OK === $file['error']) {
                        // If there are no errors, then testing the upload
                        if (move_uploaded_file($file['tmp_name'], $path . $fileName)) {
                            // Success
                            chmod($path . $fileName, 0777);
                            return $fileName;
                        } else {
                            $this->getFlash()->addAlert('SQL Error!');
                            return null;
                        }
                    } else {
                        $this->getFlash()->addAlert('Internal server error!');
                        return null;
                    }
                } else {
                    $this->getFlash()->addAlert('Veuillez envoyer un fichier avec l\'extention .zip');
                    return null;
                }
            } else {
                $this->getFlash()->addAlert('Veuillez envoyer un fichier avec l\'extention .zip');
                return null;
            }
        } else {
            $this->getFlash()->addAlert('Veuillez selectionnez un fichier');
            return null;
        }
    }
}
