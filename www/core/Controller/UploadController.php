<?php

namespace Core\Controller;

class UploadController extends Controller
{
    /**
     * Undocumented function
     *
     * @param string $path
     * @param string $attrName
     * @param array $exentions
     * @param string $mimeTypes
     * @return null|string
     */
    public function upload(
        string $path,
        string $attrName,
        array $exentions,
        array $mimeTypes
    ): ?string
    {
        $file = $_FILES[$attrName];
        $fileName = htmlspecialchars($file['name']);
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
                            //upload réussi
                            chmod($path . $fileName, 0777);
                            $this->getFlash()->addSuccess("Téléversement réussi.");
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
