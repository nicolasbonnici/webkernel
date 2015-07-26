<?php
namespace Library\Core\Orm;

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
    const KEY_LOAD_BY_DEFAULT    = 'loadByDefault';
    const KEY_MAPPING_TYPE       = 'relationship';
    const KEY_MAPPED_BY_ENTITY   = 'mappingEntity';
    const KEY_MAPPED_BY_FIELD    = 'mappedByField';
    const KEY_FOREIGN_FIELD      = 'foreignField';

	/**
	 * Error codes
	 * @var integer
	 */
	const ERROR_SOURCE_ENTITY_NOT_LOADED		    = 2;
	const ERROR_MISSING_MAPPING_SETUP			    = 3;
	const ERROR_MAPPING_TYPE_NOT_SUPPORTED 		    = 4;
	const ERROR_EMPTY_MAPPED_ENTITY_CLASSNAME 	    = 5;
	const ERROR_NOT_LOADED_MAPPED_ENTITY_TO_STORE   = 6;
	const ERROR_ENTITY_NOT_MAPPED                   = 7;

	/**
	 * Mapping types
	 * @var string
	 */
	const MAPPING_ONE_TO_ONE   = 'oneToOne';
	const MAPPING_ONE_TO_MANY  = 'oneToMany';
	const MAPPING_MANY_TO_MANY = 'manyToMany';
	
	protected $aErrorCodeMessages = array(
		self::ERROR_MAPPING_TYPE_NOT_SUPPORTED 		    => 'Mapping type %s not supported.',
		self::ERROR_SOURCE_ENTITY_NOT_LOADED		    => 'The provided source Entity instance was not load loaded.',
		self::ERROR_MISSING_MAPPING_SETUP	 		    => 'Mapping setup not found for Entity %s.',
		self::ERROR_EMPTY_MAPPED_ENTITY_CLASSNAME	    => 'No mapped Entity class name provided.',
		self::ERROR_NOT_LOADED_MAPPED_ENTITY_TO_STORE   => 'Not loaded mapped entity to store',
		self::ERROR_ENTITY_NOT_MAPPED                   => 'The provided Entity isn\'t mapped to the source Entity.',
	);
	
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
				$this->aErrorCodeMessages[self::ERROR_SOURCE_ENTITY_NOT_LOADED], 
				self::ERROR_SOURCE_ENTITY_NOT_LOADED
			);
		} elseif (count($oSourceEntity->getMappedEntities()) < 1) {
			throw new EntityMapperException(
				sprintf($this->aErrorCodeMessages[self::ERROR_MISSING_MAPPING_SETUP], get_class($oSourceEntity)), 
				self::ERROR_MISSING_MAPPING_SETUP
			);
		} else {
			$this->oSourceEntity = $oSourceEntity;
		}
	}
	
	/**
	 * Load all mapped entities (useless and potentialy dangerous)
	 */
	public function load($bForceLoad = false)
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
	 */
	public function loadMapping($sEntityClassName, array $aParameters = array(), $bForceLoad = false)
	{
 		try {
			if (empty($sEntityClassName) === true) {
				throw new EntityMapperException(
					$this->aErrorCodeMessages[self::ERROR_EMPTY_MAPPED_ENTITY_CLASSNAME], 
					self::ERROR_EMPTY_MAPPED_ENTITY_CLASSNAME
				);
			} elseif (array_key_exists($sEntityClassName, $this->oSourceEntity->getMappedEntities()) === false) {
				throw new EntityMapperException(
					sprintf($this->aErrorCodeMessages[self::ERROR_MISSING_MAPPING_SETUP], $sEntityClassName), 
					self::ERROR_MISSING_MAPPING_SETUP
				);
			} else {
				
				$aMap = $this->oSourceEntity->getMappedEntities();
				$aMappingSetup = $aMap[$sEntityClassName];

				$sMappingType = $aMappingSetup['relationship'];
				if (in_array($sMappingType, $this->aSuppportedMappingTypes) === false) {
					throw new EntityMapperException(
						sprintf($this->aErrorCodeMessages[self::ERROR_MAPPING_TYPE_NOT_SUPPORTED], $sMappingType), 
						self::ERROR_MAPPING_TYPE_NOT_SUPPORTED
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
 		} catch (\Exception $oException) {
 			return null;
 		}
	}

    /**
     * Store mapped entity
     *
     * @param Entity $oMappedEntity
     * @return bool
     * @throws EntityMapperException
     */
    public function store(Entity $oMappedEntity)
    {
        $aMap = $this->oSourceEntity->getMappedEntities();
        $aMappingSetup = $aMap[$oMappedEntity->getEntityName()];

        if ($oMappedEntity->isLoaded() === false) {
            throw new EntityMapperException(
                $this->aErrorCodeMessages[self::ERROR_NOT_LOADED_MAPPED_ENTITY_TO_STORE],
                self::ERROR_NOT_LOADED_MAPPED_ENTITY_TO_STORE
            );
        } elseif (in_array($oMappedEntity->getEntityName(), $aMappingSetup) === false) {
            throw new EntityMapperException(
                $this->aErrorCodeMessages[self::ERROR_ENTITY_NOT_MAPPED],
                self::ERROR_ENTITY_NOT_MAPPED
            );
        } else {

            $sMappingType = $aMappingSetup['relationship'];
            if (in_array($sMappingType, $this->aSuppportedMappingTypes) === false) {
                throw new EntityMapperException(
                    sprintf($this->aErrorCodeMessages[self::ERROR_MAPPING_TYPE_NOT_SUPPORTED], $sMappingType),
                    self::ERROR_MAPPING_TYPE_NOT_SUPPORTED
                );
            }

            switch ($sMappingType) {
                case self::MAPPING_ONE_TO_ONE:
                    return $this->storeOneToOneMappedEntity($oMappedEntity);
                    break;
                case self::MAPPING_ONE_TO_MANY:
                    return $this->storeOneToManyMappedEntity($oMappedEntity, $aMappingSetup);
                    break;
                case self::MAPPING_MANY_TO_MANY:
                    break;
            }

        }
        return false;
    }

    /**
     * @todo delete mapped entity
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

        } catch(\Exception $oException) {
            return false;
        }
    }

	/**
	 * Load a mapped entity using Entity foreign key (only oneToOne relationship)
	 * @see EntityMapper component for more relationship types
	 *
	 * @param string $sEntityClassName
	 * @param array $aMappingConfiguration
	 * @param boolean $bForceLoad			Flag to bypass Entity mapping settings 'loadByDefault'
	 */
	private function loadMappedEntity($sEntityClassName, array $aMappingConfiguration, $bForceLoad = false)
	{
		// @todo try catch et s'assurer que l'instance est isLoaded() === true avant de l'add
	
		$oMappedEntity = new $sEntityClassName;
		if ($aMappingConfiguration['loadByDefault'] === true || $bForceLoad === true) {
			$oMappedEntity->loadByParameters(array(
					$this->aMapping[$sEntityClassName]['mappedByField'] => $this->{$this->aMapping[$sEntityClassName]['mappedByField']}
			));
		}
		// Store mapped object
		$this->aMapping[$sEntityClassName] = $oMappedEntity;
		return $oMappedEntity;
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
     * @return bool
     */
    private function storeOneToOneMappedEntity(Entity $oMappedEntity)
    {
        try {
            return $oMappedEntity->add();
        } catch(\Exception $oException) {
            return false;
        }
    }

    private function storeOneToManyMappedEntity(Entity $oMappedEntity, array $aMappingSetup)
    {
        try {

            // Load mapping entity
            $oMappingEntity = new $aMappingSetup['mappedBy']();
            die(var_dump($oMappingEntity));

        } catch(\Exception $oException) {
            return false;
        }
        // Find mapping entity

        // If mapped entity was stored then record mapping entity too
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

class EntityMapperException extends \Exception
{
}
