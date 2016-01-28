<?php
namespace Library\Core\FileSystem;

/**
 * Files managment component
 *
 *
 * @author Nicolas Bonnici <nicolasbonnici@gmail.com>
 */
class File extends FileSystem
{

    const TYPE_IMAGE = 'img';
    const TYPE_AUDIO = 'audio';
    const TYPE_VIDEO = 'video';

    public static $aSupportedTypes = array(
        self::TYPE_IMAGE,
        self::TYPE_AUDIO,
        self::TYPE_VIDEO
    );

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
        $handle = fopen($sFilePath, $sMode);
        fclose($handle);
        return self::exists($sFilePath);
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
     * @param string $sContent
     * @return boolean
     */
    public static function write($sFilePath, $sContent)
    {
        return (file_put_contents($sFilePath, $sContent) !== 0);
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
     * @return mixed string|bool        The content otherwise FALSE
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

    /**
     * Get the file extension
     *
     * @param $sFileName
     * @return string
     */
    public static function getExtension($sFileName)
    {
        $aExplodedFilename = explode('.', $sFileName);
        return array_pop($aExplodedFilename);
    }

    /**
     * Get the file type
     *
     * @param $sFileName
     * @return string
     */
    public static function getType($sFileExtension)
    {
        $sType = '';
        switch($sFileExtension) {
            case 'jpg' :
            case 'jpeg' :
            case 'JPG' :
            case 'JPEG' :
            case 'bmp' :
            case 'BMG' :
            case 'png' :
            case 'PNG' :
            case 'gif' :
            case 'GIF' :
                $sType = self::TYPE_IMAGE;
                break;
            case 'ogg' :
            case 'mp3' :
            case 'wma' :
            case 'wav' :
            case 'aac' :
                $sType = self::TYPE_AUDIO;
                break;
            case 'mp4' :
            case 'avi' :
            case 'oga' :
            case 'mpeg' :
                $sType = self::TYPE_VIDEO;
                break;
        }

        return $sType;
    }
}

class FilesException extends \Exception
{}
