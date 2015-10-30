<?php
namespace Library\Core\Entity\Mapping;

use Library\Core\Exception\CoreException;
use Library\Core\Entity\Entity;


/**
 * Common couch to mapping supported types
 *
 * Class MappingAbstract
 * @package Library\Core\Orm\Mapping
 */
abstract class MappingAbstract
{

    /**
     * Mapping configuration keys for Entities
     */
    const KEY_LOAD_BY_DEFAULT           = 'loadByDefault';
    const KEY_MAPPING_TYPE              = 'relationship';
    const KEY_MAPPING_TABLE             = 'mappingTable';
    const KEY_MAPPED_ENTITY_REFERENCE   = 'mappedEntityReferenceField';
    const KEY_SOURCE_ENTITY_REFERENCE   = 'sourceEntityReferenceField';
    const KEY_CONSTRAINTS               = 'constraints';

    /**
     * Value allowed for the self::KEY_REFERENCE_STORED_ON
     */
    const SOURCE_ENTITY             = 'source';
    const MAPPED_ENTITY             = 'mapped';
    const MAPPING_ENTITY            = 'mapping';
    const DEFAULT_FOREIGN_FIELD_ON  = self::MAPPED_ENTITY;

    /**
     * Mapping types for the self::KEY_MAPPING_TYPE key
     * @var string
     */
    const MAPPING_ONE_TO_ONE    = 'oneToOne';
    const MAPPING_ONE_TO_MANY   = 'oneToMany';
    const MAPPING_MANY_TO_MANY  = 'manyToMany';

    /**
     * Supported mapping types
     * @var array
     */
    protected $aSuppportedMappingTypes = array(
        self::MAPPING_ONE_TO_ONE,
        self::MAPPING_ONE_TO_MANY,
        self::MAPPING_MANY_TO_MANY
    );

    /**
     * Source entity
     *
     * @var Entity
     */
    protected $oSourceEntity;

    /**
     * Array to store mapped entities
     * '\EntityNamespace\Entity' => Entity || EntityCollection
     *
     * @var array
     */
    protected $aMappingConfiguration = array();

    /**
     * Required keys on the mapping configuration array (declared on the Entity class)
     * @var array
     */
    protected $aRequiredMappingConfigurationFields = array();

    /**
     * Bypass Entity configuration to load all mapped entities
     * @var bool
     */
    protected $bForceLoad = false;

    /**
     * Instance constructor
     *
     * @param Entity $oSourceEntity
     */
    public function __construct(Entity $oSourceEntity)
    {
        if ($oSourceEntity->isLoaded() === false) {
            throw new EntityMappingException(
                EntityMappingException::$aErrors[EntityMappingException::ERROR_SOURCE_ENTITY_NOT_LOADED],
                EntityMappingException::ERROR_SOURCE_ENTITY_NOT_LOADED
            );
        } else {

            # Try to load source entity mapping configuration
            $this->aMappingConfiguration = $oSourceEntity->getMappingConfiguration();

            if (count($this->aMappingConfiguration) === 0) {
                throw new EntityMappingException(
                    sprintf(EntityMappingException::$aErrors[EntityMappingException::ERROR_MISSING_MAPPING_SETUP], get_class($oSourceEntity)),
                    EntityMappingException::ERROR_MISSING_MAPPING_SETUP
                );
            } else {
                # Set source entity
                $this->oSourceEntity = $oSourceEntity;
            }
        }
    }

    /**
     * Load all mapped entities (useless and potentialy dangerous)
     */
    public function load()
    {
        foreach ($this->aMappingConfiguration as $sLinkedEntity => $aMappingSetup) {
            $this->loadMapped($sLinkedEntity);
        }
    }

    /**
     * Find specific mapped entity or EntityCollection with parameters
     *
     * @param Entity $oMappedEntity
     * @param array $aParameters
     * @param array $aOrderFields
     * @param mixed int|array $mLimit
     * @return mixed
     */
    abstract public function loadMapped(
        Entity $oMappedEntity,
        array $aParameters = array(),
        array $aOrderFields = array(),
        $mLimit = null
    );

    /**
     * Store a mapped entity
     *
     * @param Entity $oMappedEntity
     * @return bool
     */
    abstract public function store(Entity $oMappedEntity);

    /**
     * Delete a mapped Entity
     *
     * @param Entity $oMappedEntity
     * @return bool
     */
    abstract public function delete(Entity $oMappedEntity);

    /**
     * Verify mapping configuration according to mapping type requirement
     *
     * @param array $aMappingConfiguration
     * @return bool
     */
    protected function checkMappingConfiguration(array $aMappingConfiguration)
    {
        $aErrors = array();
        foreach ($this->aRequiredMappingConfigurationFields as $iKey => $sFieldName) {
            $aErrors[] = (isset($aMappingConfiguration[$sFieldName]) === true);
            $aErrors[] = (
                empty($aMappingConfiguration[$sFieldName]) === false ||
                is_bool($aMappingConfiguration[$sFieldName]) === true
            );
        }
        return (in_array(false, $aErrors) === false);
    }

    /**
     * Load mapping configuration for a given Entity class name (with full namespace)
     *
     * @param $sEntityClassName     Entity class name (with full namespace)
     * @return array
     */
    protected function loadMappingConfiguration($sEntityClassName)
    {
        return (isset($this->aMappingConfiguration[$sEntityClassName]) === true)
            ? $this->aMappingConfiguration[$sEntityClassName]
            : null;
    }

    /**
     * Force load parameter accessor
     * @return boolean
     */
    public function isForceLoad()
    {
        return (bool) $this->bForceLoad;
    }

    /**
     * Force load parameter setter
     * @param boolean $bForceLoad
     */
    public function setForceLoad($bForceLoad)
    {
        $this->bForceLoad = boolval($bForceLoad);
    }


    /**
     * Get mapped entities for a specific mapped type or whole mapping
     *
     * @param string $sEntityClassName      NULL to get whole mapped entities
     * @return array
     */
    public function getMapping($sEntityClassName = null)
    {
        return (isset($this->aMappingConfiguration[$sEntityClassName]) === true)
            ? $this->aMappingConfiguration[$sEntityClassName]
            : $this->aMappingConfiguration;
    }
}

class EntityMappingException extends CoreException
{

    /**
     * Error codes
     * @var integer
     */
    const ERROR_SOURCE_ENTITY_NOT_LOADED = 2;
    const ERROR_MISSING_MAPPING_SETUP = 3;
    const ERROR_MAPPING_TYPE_NOT_SUPPORTED = 4;
    const ERROR_EMPTY_MAPPED_ENTITY_CLASSNAME = 5;
    const ERROR_NOT_LOADED_MAPPED_ENTITY_TO_STORE = 6;
    const ERROR_ENTITY_NOT_MAPPED = 7;

    /**
     * Error message
     * @var array
     */
    public static $aErrors = array(
        self::ERROR_MAPPING_TYPE_NOT_SUPPORTED => 'Mapping type %s not supported.',
        self::ERROR_SOURCE_ENTITY_NOT_LOADED => 'The provided source Entity instance was not load loaded.',
        self::ERROR_MISSING_MAPPING_SETUP => 'Mapping setup not found for Entity %s.',
        self::ERROR_EMPTY_MAPPED_ENTITY_CLASSNAME => 'No mapped Entity class name provided.',
        self::ERROR_NOT_LOADED_MAPPED_ENTITY_TO_STORE => 'Not loaded mapped entity to store',
        self::ERROR_ENTITY_NOT_MAPPED => 'The provided Entity isn\'t mapped to the source Entity.',
    );
}