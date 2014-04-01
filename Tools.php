<?php
namespace Library\Core;

/**
 * Toolbox
 */
class Tools
{

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

class CoreToolsException extends \Exception
{
}
