<?php
namespace Library\Core;

/**
 * Common Toolbox
 *
 *
 */
class Tools
{

    /**
     * php chmod native function enhancement to add a recursive option
     *
     * @param string $sAbsolutePath
     * @param array $aMode
     * @param boolean $bRecursive               The $sAbsolutePath point to a folder and you want to apply chmod recursively
     * @return boolean
     */
    public static function chmod($sAbsolutePath, $aMode, $bRecursive = false)
    {
        if ($bRecursive === false ) {
            return chmod($sAbsolutePath, $aMode[0] . $aMode[1] . $aMode[2] . $aMode[3]);
        } else {
            $oRecursiveIterator = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($sAbsolutePath), \RecursiveIteratorIterator::SELF_FIRST);
            foreach($oRecursiveIterator as $sDirectoryItem) {
                chmod($sDirectoryItem, $aMode[0] . $aMode[1] . $aMode[2] . $aMode[3]);
            }
        }
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
