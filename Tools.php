<?php
namespace Library\Core;

/**
 * Common Toolbox
 * @todo convert as a trait
 * @todo some method are ViewHelper related
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

    /**
     * Generate a random hex color
     * @return string
     */
    public function generateRandomHexColor()
    {
        $sOutput = '';
        for ($i = 0; $i < 2; $i++) {
            $sOutput .= str_pad( dechex( mt_rand( 0, 255 ) ), 2, '0', STR_PAD_LEFT);
        }
        return $sOutput;
    }

}