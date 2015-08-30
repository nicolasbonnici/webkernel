<?php
namespace Library\Core\Orm;

use Library\Core\CoreException;

/**
 * Class EntityMapper
 *
 * Manage all relations between Entities
 *
 * @package Library\Core\Orm
 */
class EntityMapper
{

    /**
     * Mapping configuration keys for Entities
     */
    const KEY_LOAD_BY_DEFAULT = 'loadByDefault';
    const KEY_MAPPING_TYPE = 'relationship';
    const KEY_MAPPED_BY_ENTITY = 'mappingEntity';
    const KEY_MAPPED_BY_FIELD = 'mappedByField';
    const KEY_FOREIGN_FIELD = 'foreignField';
    const KEY_FOREIGN_FIELD_ON = 'foreignFieldOn';

    /**
     * Value allowed for the self::KEY_FOREIGN_FIELD_ON
     */
    const SOURCE_ENTITY = 'source';
    const MAPPED_ENTITY = 'mapped';

    /**
     * Mapping types for the self::KEY_MAPPING_TYPE key
     * @var string
     */
    const MAPPING_ONE_TO_ONE = 'oneToOne';
    const MAPPING_ONE_TO_MANY = 'oneToMany';
    const MAPPING_MANY_TO_MANY = 'manyToMany';

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
    protected $aMapping = array();

    /**
     * Instance constructor
     *
     * @param Entity $oSourceEntity
     */
    public function __construct(Entity $oSourceEntity, $bForceLoad = false)
    {
        if ($oSourceEntity->isLoaded() === false) {
            throw new EntityMapperException(
                EntityMapperException::$aErrors[EntityMapperException::ERROR_SOURCE_ENTITY_NOT_LOADED],
                EntityMapperException::ERROR_SOURCE_ENTITY_NOT_LOADED
            );
        } elseif (count($oSourceEntity->getMappedEntities()) < 1) {
            throw new EntityMapperException(
                sprintf(EntityMapperException::$aErrors[EntityMapperException::ERROR_MISSING_MAPPING_SETUP], get_class($oSourceEntity)),
                EntityMapperException::ERROR_MISSING_MAPPING_SETUP
            );
        } else {
            $this->oSourceEntity = $oSourceEntity;
            $this->load();
        }
    }

    /**
     * Store mapped entity
     *
     * @param mixed Entity|EntityCollection $oMappedEntity
     * @return bool
     * @throws EntityMapperException
     */
    public function store($oMappedEntity)
    {
        if (
            ($oMappedEntity instanceof Entity) === false ||
            ($oMappedEntity instanceof EntityCollection) === false
        ) {
            return false;
        }

        $aMap = $this->oSourceEntity->getMappedEntities();
        $aMappingSetup = $aMap[get_class($oMappedEntity)];

        $sMappingType = $aMappingSetup['relationship'];
        if (in_array($sMappingType, $this->aSuppportedMappingTypes) === false) {
            throw new EntityMapperException(
                sprintf(EntityMapperException::$aErrors[EntityMapperException::ERROR_MAPPING_TYPE_NOT_SUPPORTED], $sMappingType),
                EntityMapperException::ERROR_MAPPING_TYPE_NOT_SUPPORTED
            );
        }

        switch ($sMappingType) {
            case self::MAPPING_ONE_TO_ONE:
                return $this->storeOneToOneMappedEntity($oMappedEntity, $aMappingSetup);
                break;
            case self::MAPPING_ONE_TO_MANY:
                return $this->storeOneToManyMappedEntity($oMappedEntity, $aMappingSetup);
                break;
            case self::MAPPING_MANY_TO_MANY:
                break;
        }

        throw new EntityMapperException(
            sprintf(
                EntityMapperException::getError(EntityMapperException::ERROR_MAPPING_TYPE_NOT_SUPPORTED),
                $sMappingType
            ),
            EntityMapperException::ERROR_MAPPING_TYPE_NOT_SUPPORTED
        );
    }

    /**
     * @todo refacto delete mapped entity
     *
     * @return bool
     */
    public function delete(Entity $oMappedEntity)
    {
        try {
            $aMap = $this->oSourceEntity->getMappedEntities();
            $aMappingSetup = $aMap[$oMappedEntity->getEntityName()];
            $sMappingType = $aMappingSetup['relationship'];

            switch ($sMappingType) {
                case self::MAPPING_ONE_TO_ONE:
                    // Just delete mapped entity
                    return $oMappedEntity->delete();
                    break;
                case self::MAPPING_ONE_TO_MANY:
                    // First delete mapping entity

                    // Then delete mapped entity itself
                    break;
                case self::MAPPING_MANY_TO_MANY:
                    break;
            }

        } catch (\Exception $oException) {
            return false;
        }
    }


    /**
     * Load all mapped entities (useless and potentialy dangerous)
     */
    private function load($bForceLoad = false)
    {
        foreach ($this->oSourceEntity->getMappedEntities() as $sLinkedEntity => $aMappingSetup) {
            $this->loadMapping($sLinkedEntity, $aMappingSetup, $bForceLoad);
        }
    }

    /**
     * Load mapping for a given mapped Entity with given parameters
     *
     * @param string $sEntityClassName
     * @param array $aParameters
     * @param boolean $bForceLoad
     * @throws EntityMapperException
     * @return void
     */
    private function loadMapping($sEntityClassName, array $aParameters = array(), $bForceLoad = false)
    {
// 		try {
        if (empty($sEntityClassName) === true) {
            throw new EntityMapperException(
                EntityMapperException::$aErrors[EntityMapperException::ERROR_EMPTY_MAPPED_ENTITY_CLASSNAME],
                EntityMapperException::ERROR_EMPTY_MAPPED_ENTITY_CLASSNAME
            );
        } elseif (array_key_exists($sEntityClassName, $this->oSourceEntity->getMappedEntities()) === false) {
            throw new EntityMapperException(
                sprintf(EntityMapperException::$aErrors[EntityMapperException::ERROR_MISSING_MAPPING_SETUP], $sEntityClassName),
                EntityMapperException::ERROR_MISSING_MAPPING_SETUP
            );
        } else {

            $aMap = $this->oSourceEntity->getMappedEntities();
            $aMappingSetup = $aMap[$sEntityClassName];

            $sMappingType = $aMappingSetup['relationship'];
            if (in_array($sMappingType, $this->aSuppportedMappingTypes) === false) {
                throw new EntityMapperException(
                    sprintf(EntityMapperException::$aErrors[EntityMapperException::ERROR_MAPPING_TYPE_NOT_SUPPORTED], $sMappingType),
                    EntityMapperException::ERROR_MAPPING_TYPE_NOT_SUPPORTED
                );
            }

            switch ($sMappingType) {
                case self::MAPPING_ONE_TO_ONE:
                    return $this->loadMappedEntity($sEntityClassName, $aMappingSetup, $bForceLoad);
                    break;
                case self::MAPPING_ONE_TO_MANY:
                    return $this->loadMappedEntities($sEntityClassName, $aMappingSetup, $bForceLoad);
                    break;
                case self::MAPPING_MANY_TO_MANY:
                    // @todo instancier et mapper deux collections
                    break;
            }
        }
// 		} catch (\Exception $oException) {
// 			return null;
// 		}
    }

    /**
     * Load a mapped entity using Entity foreign key (only oneToOne relationship)
     *
     * @param $sEntityClassName
     * @param array $aMappingConfiguration
     * @param bool|false $bForceLoad
     * @return null
     */
    private function loadMappedEntity($sEntityClassName, array $aMappingConfiguration, $bForceLoad = false)
    {

//        try {
        /** @var Entity $oMappedEntity */
        $oMappedEntity = new $sEntityClassName;
        if ($aMappingConfiguration[self::KEY_LOAD_BY_DEFAULT] === true || $bForceLoad === true) {
            switch ($this->aMapping[$sEntityClassName][self::KEY_FOREIGN_FIELD_ON]) {
                case self::SOURCE_ENTITY :
                    $oMappedEntity->loadByParameters(array(
                        $oMappedEntity->getPrimaryKeyName() => $this->oSourceEntity->{$this->aMapping[$sEntityClassName][self::KEY_MAPPED_BY_FIELD]}
                    ));
                    break;
                case self::MAPPED_ENTITY :
                    $oMappedEntity->loadByParameters(array(
                        $this->aMapping[$sEntityClassName][self::KEY_MAPPED_BY_FIELD] => $this->oSourceEntity->getId()
                    ));
                    break;
            }

            if ($oMappedEntity->isLoaded() === true) {

                // Store in instance loaded mapped object for a cache at call
                $this->aMapping[$sEntityClassName] = $oMappedEntity;

                return $oMappedEntity;
            }

        }
        return null;
//        } catch (\Exception $oException) {
//            return null;
//        }
    }

    private function loadMappedEntities($sEntityClassName, array $aMappingSetup, $bForceLoad = false)
    {
        $oLinkedEntityCollection = new $sEntityClassName;
        $oMappingEntities = new $aMappingSetup['mappingEntity'];
        $oMappingEntities->loadByParameters(array(
            $aMappingSetup['mappedByField'] => $this->oSourceEntity->getId()
        ));
        if ($oMappingEntities->count() > 0) {
            $aMappedEntityIds = array();
            foreach ($oMappingEntities as $oMappingEntity) {
                $aMappedEntityIds[] = intval($oMappingEntity->{$aMappingSetup['foreignField']});
            }

            // Restrict scope to mapped entities
            $aParameters[constant($oLinkedEntityCollection->getChildClass() . '::PRIMARY_KEY')] = $aMappedEntityIds;
            $oLinkedEntityCollection->loadByParameters(
                $aParameters
            );

            // Store mapped entities
            $this->aMapping[$sEntityClassName] = $oLinkedEntityCollection;
            return $oLinkedEntityCollection;
        }
        return null;
    }

    /**
     * Store One to One mapped Entity
     *
     * @param Entity $oMappedEntity
     * @param array $aEntityMappingSetup
     * @return bool
     * @throws EntityException
     */
    private function storeOneToOneMappedEntity(Entity $oMappedEntity, array $aEntityMappingSetup)
    {
//        try {

        if ($aEntityMappingSetup[self::KEY_FOREIGN_FIELD_ON] === self::MAPPED_ENTITY) {
            // Store foreign key on mapped Entity
            $sField = $aEntityMappingSetup[self::KEY_MAPPED_BY_FIELD];
            $oMappedEntity->{$sField} = $this->oSourceEntity->getId();
        }


        if ($oMappedEntity->add() === true) {
            if ($aEntityMappingSetup[self::KEY_FOREIGN_FIELD_ON] === self::SOURCE_ENTITY) {
                // Store foreign Entity reference directly on source entity
                $sFieldName = $aEntityMappingSetup[self::KEY_MAPPED_BY_FIELD];
                $this->oSourceEntity->{$sFieldName} = $oMappedEntity->getId();
                return $this->oSourceEntity->update();
            }
            return true;
        }
        return false;
//        } catch(\Exception $oException) {
//            return false;
//        }
    }

    /**
     * Store a collection of mapped entities (one to many case)
     *
     * @todo MYSQL transactional mode when we have to store a mapping entity plus the entity itself
     *
     * @param EntityCollection $oMappedEntities
     * @param array $aMappingSetup
     * @throws EntityException
     */
    private function storeOneToManyMappedEntity(EntityCollection $oMappedEntities, array $aMappingSetup)
    {
        try {
            $aErrors = array();
            /** @var Entity $oMappedEntity */
            foreach ($oMappedEntities as $oMappedEntity) {

                // First store mapped entity itself to retrieve primary key value then store mapping entity
                if ($oMappedEntity->add() === true) {
                    /** @var Entity $oMappingEntity */
                    $oMappingEntity = new $aMappingSetup[self::KEY_MAPPED_BY_ENTITY]();
                    $oMappingEntity->{$this->computeSourceKeyFieldNameOnMappingEntity()} = $this->oSourceEntity->getId();
                    $oMappingEntity->{$this->computeMappedKeyFieldNameOnMappingEntity($oMappedEntity)} = $oMappedEntity->getId();
                    $oMappingEntity->add();
                } else {
                    $aErrors[] = $oMappedEntity;
                }

            }
            return (count($aErrors) === 0);

        } catch (\Exception $oException) {
            return false;
        }
    }

    private function computeSourceKeyFieldNameOnMappingEntity()
    {
        return $this->oSourceEntity->getTableName() . '_' . $this->oSourceEntity->getPrimaryKeyName();
    }

    private function computeMappedKeyFieldNameOnMappingEntity($oMappedEntity)
    {
        return $oMappedEntity->getTableName() . '_' . $oMappedEntity->getPrimaryKeyName();
    }

    /**
     * Generic mapped entities generic accessor
     *
     * @param string $sEntityClassName
     * @return array
     */
    public function getMapping()
    {
        return $this->aMapping;
    }

}

class EntityMapperException extends CoreException
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
