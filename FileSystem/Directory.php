<?php
namespace Library\Core\FileSystem;
use Library\Core\Pattern\Singleton;

/**
 * Directory managment component
 *
 * @author Nicolas Bonnici <nicolasbonnici@gmail.com>
 */

class Directory extends Singleton
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
                    throw new ToolsException('Unable to delete ' . $sPath . ' check the ' . Bootstrap::getServerUsername() . ' user rights on your server.');
                }
            } else {
                return true;
            }
        }
        return true;
    }

    public static function create($sPath)
    {
        return mkdir($sPath, 0777, true);
    }

    /**
     * Tell if a folder exists and assert that the element is a correct directory element
     *
     * @param string $sAbsoluteFolderPath
     * @return boolean
     */
    public static function exists($sAbsoluteFolderPath)
    {
        return is_dir($sAbsoluteFolderPath);
    }

    /**
     * Recursve scanning method for user's workspace
     * @return array
     */
    public static function scan($sPathToScan = null, array $aItems = array())
    {
        if (is_null($sPathToScan)) {
            $sPathToScan = $sPathToScan;
        }
        if(file_exists($sPathToScan)) {
            foreach(scandir($sPathToScan) as $f) {

                if(!$f || $f[0] == '.') {
                    continue;
                }

                if(is_dir($sPathToScan . '/' . $f)) {
                    // @todo Class object here
                    // Folder
                    $aItems[] = array(
                        "name" => $f,
                        "type" => "folder",
                        "path" => $sPathToScan . $f,
                        "items" => self::scan($sPathToScan . '/' . $f)
                    );
                } else {
                    // @todo Class object here
                    // File
                    $aItems[] = array(
                        "name" => $f,
                        "type" => "file",
                        "path" => $sPathToScan . $f,
                        "size" => filesize($sPathToScan . '/' . $f)
                    );
                }
            }

        }

        return $aItems;
    }

}
