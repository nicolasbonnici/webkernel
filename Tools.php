<?php
namespace Library\Core;

/**
 * Toolbox
 *
 * @todo retrouver la methode file_force_content()
 *
 */
class Tools
{

    /**
     * Delete a folder if it's not empty it will recursively delete all sufolders and files
     * @param string $sPath
     *                          Absolute path
     * @return boolean
     */
    public static function deleteDirectory($sPath)
    {
        foreach(glob($sPath . '/*') as $sDirItem) {
            // also check for symbolink link because is_dir() return TRUE on them but they are just file so rm_dir will throw an Exception
            if(is_dir($sDirItem) && !is_link($sDirItem)) {
                self::deleteDirectory($sDirItem);
            } else {
                unlink($sDirItem);
            }
        }
        return rmdir($sPath);
    }

    /**
     * Retrieve gravatar url
     *
     * @param string $sEmail
     * @param string $iSize
     */
    public static function getGravatar($sEmail, $iSize)
    {
        // Définition des paramètres utiles
        $sDefault = urlencode('http://use.perl.org/images/pix.gif');
        $sEmail = md5($sEmail);
        // Création de l'url
        return sprintf('http://www.gravatar.com/avatar.php?gravatar_id=%s&amp;size=%d&amp;default=%s', $sEmail, intval($iSize), $sDefault);
    }

}

class ToolsException extends \Exception
{
}
