<?php
namespace Library\Core\FileSystem;

use Library\Core\Pattern\Singleton;

/**
 * Class FileSystem
 *
 * File system common couch
 *
 * @package Library\Core\FileSystem
 */
class FileSystem extends Singleton {

    /**
     * File types that can be base64 encoded
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
            return (in_array(true, $aChmods) === true && in_array(false, $aChmods) === false);
        }
    }
}