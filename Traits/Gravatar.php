<?php
namespace Library\Core\Traits;


/**
 * Gravatar API usage
 *
 * Class Gravatar
 * @package Library\Core\Traits
 */
trait Gravatar
{
    /**
     * Retrieve gravatar url
     *
     * @param string $sEmail
     * @param string $iSize
     */
    public static function getGravatar($sEmail, $iSize)
    {
        $sDefault = urlencode('http://use.perl.org/images/pix.gif');
        $sEmail = md5($sEmail);
        // Création de l'url
        return sprintf(
            'http://www.gravatar.com/avatar.php?gravatar_id=%s&amp;size=%d&amp;default=%s',
            $sEmail,
            intval($iSize),
            $sDefault
        );
    }
}