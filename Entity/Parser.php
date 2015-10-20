<?php
namespace Library\Core\Entity;

use Library\Core\Bootstrap;
use Library\Core\Exception\CoreException;
use Library\Core\FileSystem\Directory;

/**
 * Parser component
 *
 */
class Parser
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
        // @todo protected property for reserved key name
        $aExcludedPath = array(
            '',
            'Deploy',
            'Collection',
            'Mapping',
        );
        $aExcludedFile = array(
            'Foo.php'
        );

        foreach ($aFolderContent as $aFolderItem) {
            if (
                $aFolderItem['type'] === 'file' &&
                is_null($aFolderItem['name']) === false &&
                in_array($aFolderItem['name'], $aExcludedFile) === false
            ) {
                $sFilename = substr($aFolderItem['name'], 0, strlen($aFolderItem['name']) - strlen('.php'));
                if (in_array($sFilename, $this->aEntities) === false) {
                    $sEntityClassName = $this->computeClassNameFromPath($aFolderItem['path'], $sFilename);
                    if (class_exists($sEntityClassName)) {
                        $this->aEntities[] = new $sEntityClassName;
                    }
                }
            } elseif (
                $aFolderItem['type'] === 'folder' &&
                in_array($aFolderItem['name'], $aExcludedPath) === false
            ) {
                $this->parseEntities($sAbsolutePath . $aFolderItem['name']);
            }
        }
    }

    /**
     * Return PSR4 namespace compliant from a class path
     * @param $sFilePath
     * @param $sFilename
     * @return string
     */
    protected function computeClassNameFromPath($sFilePath, $sFilename)
    {
        $sNamespace = str_replace(Bootstrap::getRootPath(), '', $sFilePath);
        return '\\' . str_replace(DIRECTORY_SEPARATOR, '\\', $sNamespace) . $sFilename;
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

