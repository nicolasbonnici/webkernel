<?php
namespace Library\Core\FileSystem;

use Library\Core\Exception\CoreException;
use Library\Core\Pattern\Singleton;

/**
 * Class FileSystem
 *
 * File system common couch
 *
 * @package Library\Core\FileSystem
 */
class FileSystem extends Singleton
{

    /**
     * Default file system directory separator
     */
    const DS = DIRECTORY_SEPARATOR;

    /**
     * @todo list all handled mime file types
     * @var array
     */
    protected $aFileTypes = array(
        "js"	=> "text/javascript",
        "css"	=> "text/css",
        "htm"	=> "text/html",
        "html"	=> "text/html",
        "xml"	=> "text/xml",
        "txt"	=> "text/plain",
        "jpg"	=> "image/jpeg",
        "jpeg"	=> "image/jpeg",
        "png"	=> "image/png",
        "gif"	=> "image/gif",
        "swf"	=> "application/x-shockwave-flash",
        "ico"	=> "image/x-icon"
    );

    /**
     * php chmod native function enhancement to add a recursive option
     *
     * @param string $sAbsolutePath             Absolute path
     * @param int $iMode
     * @param boolean $bRecursive               Flag if you want to apply chmod recursively
     * @return boolean
     */
    public static function chmod($sAbsolutePath, $iMode, $bRecursive = false)
    {
        if ($bRecursive === false ) {
            return chmod($sAbsolutePath, $iMode);
        } else {
            $oRecursiveIterator = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($sAbsolutePath), \RecursiveIteratorIterator::SELF_FIRST);
            $aChmods = array();
            foreach($oRecursiveIterator as $sDirectoryItem) {
                $aChmods[] = chmod($sDirectoryItem, $iMode);
            }
            return (in_array(false, $aChmods) === false);
        }
    }

    /**
     * Create a symlink
     *
     * @param $sTarget
     * @param $sLinkName
     * @return bool
     */
    public static function ln($sTarget, $sLinkName)
    {
        return symlink($sTarget, $sLinkName);
    }
}

class FileSystemException extends CoreException
{

    const ERROR_UNABLE_TO_DELETE = 2;

    public static $aErrors = array(
        self::ERROR_UNABLE_TO_DELETE => 'Unable to delete %s, check the "%s" user rights on your server.'
    );

}