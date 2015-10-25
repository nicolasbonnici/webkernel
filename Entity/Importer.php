<?php
namespace Library\Core\Entity;

use Library\Core\Database\Pdo;
use Library\Core\Exception\CoreException;
use Library\Core\FileSystem\File;

use Library\Core\Traits\NamespaceTools;

/**
 * This class can import any entity sql structure from project and bundles
 *
 * Class Importer
 * @package Library\Core\Entity
 */
class Importer
{

    # Register namespaces toolbox trait
    use NamespaceTools;

    /**
     * Default dump sql file path (relative to the Entities folder)
     * @var string
     */
    const DUMPS_FOLDER_NAME = 'Deploy';

    /**
     * SQL script functions separator character
     * @var string
     */
    const SQL_SCRIPT_FUNCTIONS_SEPARATOR = ';';

    /**
     * The Entity name
     * @var string
     */
    protected $sEntityName  = '';

    /**
     * Path to the Entity dump file (must be on Entities/Deploy/ folder named EntityName.sql)
     *
     * @var string
     */
    protected $sSqlDumpPath = '';

    /**
     * @var bool
     */
    protected $bIsLoaded = false;

    /**
     * Instance constructor
     *
     * @param $sEntityClassName
     * @throws ImporterException
     */
    public function __construct($sEntityClassName)
    {
        # Compute sql dump file path
        $sComputedDumpPath = $this->computeDumpPathFromEntityClassName($sEntityClassName);
        if (File::exists($sComputedDumpPath) === false) {
            # Dump not found
            throw new ImporterException(
                sprintf(ImporterException::getError(ImporterException::ERROR_DUMP_FILE_NOT_FOUND), $sEntityClassName),
                ImporterException::ERROR_DUMP_FILE_NOT_FOUND
            );
        }

        if (class_exists($sEntityClassName) === false) {
            # Entity class not found (catch this exception then use the Entities scaffolding component)
            throw new ImporterException(
                sprintf(ImporterException::getError(ImporterException::ERROR_DUMP_FILE_NOT_FOUND), $sEntityClassName),
                ImporterException::ERROR_DUMP_FILE_NOT_FOUND
            );
        }

        # Set dump path
        $this->sSqlDumpFilePath = $sComputedDumpPath;

        # Set instance correctly loaded
        $this->bIsLoaded = true;
    }

    /**
     * Create the entity table structure
     *
     * @return bool
     */
    public function process()
    {
        $aExecutes = array();
        $sSql = File::getContent($this->sSqlDumpFilePath);
        if ($sSql !== false) {
            $aSqlRequests = explode(self::SQL_SCRIPT_FUNCTIONS_SEPARATOR, $sSql);
            foreach ($aSqlRequests as $sSqlQuery) {
                $aExecutes[] = Pdo::dbQuery($sSqlQuery);
            }

        }
        return (bool) (in_array(false, $aExecutes) === false);
    }

    /**
     * Compute the sql dump file path from the entity class name
     *
     * @param $sEntityClassName         Entity class name with namespace
     * @return string
     */
    protected function computeDumpPathFromEntityClassName($sEntityClassName)
    {
        $aNamespace = explode('\\', $sEntityClassName);
        # Extract class name from namespace
        $sClassName = array_pop($aNamespace);
        return $this->computeAbsolutePathFromNamespace(implode('\\', $aNamespace))  . DIRECTORY_SEPARATOR .
            self::DUMPS_FOLDER_NAME . DIRECTORY_SEPARATOR . $sClassName . '.sql';
    }

    /**
     * Tell if instance was correctly loaded
     *
     * @return bool
     */
    public function isLoaded()
    {
        return $this->bIsLoaded;
    }

}

/**
 * Class ImporterException
 * @package Library\Core\Entity
 */
class ImporterException extends CoreException
{
    const ERROR_DUMP_FILE_NOT_FOUND    = 2;
    const ERROR_ENTITY_CLASS_NOT_FOUND = 3;

    public static $aErrors = array(
        self::ERROR_DUMP_FILE_NOT_FOUND     => 'Entity sql dump file for class name %s not found.',
        self::ERROR_ENTITY_CLASS_NOT_FOUND  => 'Entity class not found.'
    );
}

