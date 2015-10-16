<?php
namespace Library\Core\FileSystem;
use Library\Core\Bootstrap;
use Library\Core\Cli;

/**
 * Directory managment component
 *
 * @author Nicolas Bonnici <nicolasbonnici@gmail.com>
 */

class Directory extends FileSystem
{

    const DIRECTORY_SCAN_KEY_NAME  = 'name';
    const DIRECTORY_SCAN_KEY_TYPE  = 'type';
    const DIRECTORY_SCAN_KEY_PATH  = 'path';
    const DIRECTORY_SCAN_KEY_SIZE  = 'size';
    const DIRECTORY_SCAN_KEY_ITEMS = 'items';

    /**
     * Delete a folder if it's not empty this method will recursively delete all sufolders and files
     * @param string $sPath     Absolute path
     * @param string $bRetry    Flag to retry once after chmod to 0777 folder
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
                    self::chmod($sPath, 755, true);
                    return self::delete($sPath, true);
                } else {
                    $oCli = new Cli();
                    throw new FileSystemException(
                        sprintf(
                            FileSystemException::getError(
                                FileSystemException::ERROR_UNABLE_TO_DELETE
                            ),
                            array($sPath, $oCli->getUser())
                        ),
                        FileSystemException::ERROR_UNABLE_TO_DELETE
                    );
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
     * Recursive scanning method on file system
     * @return array
     */
    public static function scan($sPathToScan = null, array $aItems = array())
    {
        if(file_exists($sPathToScan)) {
            foreach(scandir($sPathToScan) as $f) {

                if(!$f || $f[0] == '.') {
                    continue;
                }

                $sItemPath = $sPathToScan . DIRECTORY_SEPARATOR . $f;
                if(is_dir($sItemPath)) {
                    // @todo Abstract factory here Element et FolderElement
                    // Folder
                    $aItems[] = array(
                        "name" => $f,
                        "type" => "folder",
                        "path" => $sItemPath . DIRECTORY_SEPARATOR,
                        "size" => filesize($sItemPath),
                        "items" => self::scan($sItemPath . DIRECTORY_SEPARATOR)
                    );
                } else {
                    // File
                    $aItems[] = array(
                        "name" => $f,
                        "type" => "file",
                        "path" => $sPathToScan,
                        "size" => filesize($sItemPath)
                    );
                }
            }

        }

        return $aItems;
    }

}
