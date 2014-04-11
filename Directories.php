<?php
namespace Library\Core;

/**
 * Directories managment component
 *
 * @author Nicolas Bonnici <nicolasbonnici@gmail.com>
 */

class Directories extends Singleton
{
    /**
     * Delete a folder if it's not empty this method will recursively delete all sufolders and files
     * @param string $sPath
     *                          Absolute path
     * @param string $bRetry
     *                          Flag to retry once after chmod to 0777 folder
     * @throws ToolsException
     * @return boolean
     */
    public static function delete($sPath, $bRetry = false)
    {
        if (is_dir($sPath)) {
            foreach(glob($sPath . '/*') as $sDirItem) {
                // also check for symbolink link because is_dir() return TRUE on them but they are just file so rm_dir will throw a warning error
                if(is_dir($sDirItem) && !is_link($sDirItem)) {
                    self::delete($sDirItem);
                } else {
                    unlink($sDirItem);
                }
            }
            if (! rmdir($sPath)) {
                if ($bRetry === false) {
                    self::Tools($sPath, array(0,7,7,7), true);
                    return self::delete($sPath, true);
                } else {
                    throw new ToolsException('Unable to delete ' . $sPath . ' check the ' . App::getServerUsername() . ' user rights on your server.');
                }
            } else {
                return true;
            }
        } else {
            // That folder doesn't exists so since we want to delete it we return true anyway
            return true;
        }
    }

    public static function create($sPath)
    {
        return mkdir($sPath, 0777, true);
    }


}
