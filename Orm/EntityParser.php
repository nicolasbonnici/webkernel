<?php
namespace Library\Core\Orm;

use Library\Core\CoreException;
use Library\Core\Directory;

/**
 * EntityParser component
 *
 *
 * @ŧodo directly return instance not only string values
 */
class EntityParser
{

    /**
     * Bundles available entities
     * @var array
     */
    protected $aEntities = array();

    /**
     * Absolute path to parse
     * @var string
     */
    protected $sPath;

    public function __construct($sAbsolutePath)
    {
        if (empty($sAbsolutePath) || Directory::exists($sAbsolutePath) === false) {
            throw new EntityParserException(
                sprintf(EntityParserException::$aErrors[EntityParserException::ERROR_INVALID_PATH], $sAbsolutePath),
                EntityParserException::ERROR_INVALID_PATH
            );
        }

        $this->sPath = $sAbsolutePath;
        $this->build();

    }

    /**
     * Build available entities
     *
     * @return array An array on Entities classnames found
     */
    protected function build()
    {
        // Scan bundles entities
        $this->parseEntities($this->sPath);

    }

    /**
     * Parse entites for a given absolute path
     *
     * @param string $sAbsolutePath
     */
    protected function parseEntities($sAbsolutePath)
    {
        $aFolderContent = Directory::scan($sAbsolutePath);
        // @todo protected property
        $aExcludedPath = array(
            '',
            'Deploy'
        );

        foreach ($aFolderContent as $aFolderItem) {
            if (
                $aFolderItem['type'] === 'file' &&
                is_null($aFolderItem['name']) === false
            ) {
                $sFilename = substr($aFolderItem['name'], 0, strlen($aFolderItem['name']) - strlen('.php'));
                if (in_array($sFilename, $this->aEntities) === false) {
                    $this->aEntities[] = $sFilename;
                }
            } elseif (
                $aFolderItem['type'] === 'folder' &&
                in_array($aFolderItem['name'], $aExcludedPath) === false
            ) {
                $this->parseEntities($sAbsolutePath . $aFolderItem['name']);
            }
        }
    }

    public function getEntities()
    {
        return $this->aEntities;
    }

    public function getPath()
    {
        return $this->sPath;
    }
}

class EntityParserException extends CoreException
{
    const ERROR_INVALID_PATH = 2;

    public static $aErrors = array(
        self::ERROR_INVALID_PATH => 'Empty or invalid path provided at instance %s'
    );

}

