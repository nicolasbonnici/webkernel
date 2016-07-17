<?php
namespace Library\Core\Traits;


/**
 * Gravatar Trait tool to retrieve user Gravatar profile icon
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
        $sDefault = 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAAAXNSR0IArs4c6QAAAAZiS0dEAP8A/wD/oL2nkwAAAAlwSFlzAAALEwAACxMBAJqcGAAAAAd0SU1FB9oLAwQpIow7fcUAAAAZdEVYdENvbW1lbnQAQ3JlYXRlZCB3aXRoIEdJTVBXgQ4XAAAADUlEQVQI12NQUVHZDAAB/AEg09nrXQAAAABJRU5ErkJggg==';
        $sEmail = md5($sEmail);
        // Création de l'url
        return sprintf(
            'https://www.gravatar.com/avatar.php?gravatar_id=%s&amp;size=%d&amp;default=%s',
            $sEmail,
            intval($iSize),
            $sDefault
        );
    }
}