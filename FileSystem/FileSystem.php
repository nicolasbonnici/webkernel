<?php
namespace Library\Core\FileSystem;


use Library\Core\Pattern\Singleton;

class FileSystem extends Singleton {

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