<?php
namespace Library\Core;
/**
 * Files managment component
 *
 * @author Nicolas Bonnici <nicolasbonnici@gmail.com>
 */
class Files extends Singleton
{
    /**
     * Delete a file
     *
     * @todo Use the $bRetry flag if the unlink fail to try a chmod on the file
     *
     * @param string $sFilePath        Filename with absolute path
     * @param string $bRetry
     * @return boolean
     */
    public static function delete($sFilePath, $bRetry = false)
    {
        if (self::exists($sFilePath)) {
            return unlink($sFilePath);
        }
        return true;
    }

    /**
     * Create a new empty file then apply chmod
     *
     * @param string $sPath
     * @return boolean
     */
    public static function create($sFilePath, $sMode = 'wb')
    {
        fopen($sFilePath, $sMode);
        return self::exists($sPath);
    }

    /**
     * Open a file
     *
     * @param string $sFilePath         Absolute path to a file
     * @param string $sMode             fopen native php function modes
     * @return resource
     */
    public static function open($sFilePath, $sMode = 'a+')
    {
        return fopen($sFilePath, $sMode);
    }

    /**
     * Close a file
     * @param ressource $oHandle
     * @return boolean
     */
    public static function close($oHandle)
    {
        return fclose($oHandle);
    }

    /**
     * Write on a file
     *
     * @param string $sFilePath         Absolute path to a file
     * @param unknown $sContent
     * @return boolean
     */
    public static function write($sFilePath, $sContent)
    {
        return (file_put_contents($sFilePath, $sContent) !== false);
    }

    /**
     * Tell if a file or a symlink exists
     * @param string $sFilePath         Absolute path to a file
     * @return boolean
     */
    public static function exists($sFilePath)
    {
        return is_file($sFilePath);
    }

    /**
     * Grab file content
     * @param string $sFilePath         Absolute path to a file
     * @return string
     */
    public static function getContent($sFilePath)
    {
        return file_get_contents($sFilePath);
    }

    /**
     * Grab file size
     * @param string $sFilePath         Absolute path to a file
     * @return integer
     */
    public static function size($sFilePath)
    {
        return filesize($sFilePath);
    }

    /**
     * Empty file content
     *
     * @param unknown $sFilePath
     * @throws FilesException
     * @return boolean
     */
    public static function reset($sFilePath)
    {
        return file_put_contents($sFilePath, '');
    }
}

class FilesException extends \Exception
{}
