<?php
namespace Library\Core\Entity;

use Library\Core\Cache\Drivers\Memcache;
use Library\Core\Database\Pdo;
use Library\Core\Database\Query\Delete;
use Library\Core\Database\Query\Insert;
use Library\Core\Database\Query\Operators;
use Library\Core\Database\Query\QueryAbstract;
use Library\Core\Database\Query\Select;
use Library\Core\Database\Query\Update;
use Library\Core\Exception\CoreException;
use Library\Core\Log\Log;

/**
 * Entities management abstract class
 *
 * Class Entity
 * @package Library\Core\Orm
 *
 * @author niko <nicolasbonnici@gmail.com>
 */
abstract class Entity extends Attributes
{

    /**
     * Separator between foreign table name and primary key name on a foreign key name (ex: table_idtable)
     */
    const FOREIGN_KEY_SEPARATOR = '_';

    /**
     * Generic fields to handle or not on all Entities
     */
    const FIELD_LASTUPDATE = 'lastupdate';
    const FIELD_CREATED    = 'created';

    /**
     * Whether row in database may be deleted or not
     * @var boolean
     */
    protected $bIsDeletable = false;

    /**
     * Whether row in database may be searchable or not
     * @var boolean
     */
    protected $bIsSearchable = false;

    /**
     * Whether historical must be saved in DB on update/delete
     * @var boolean
     */
    protected $bIsHistorized = false;

    /**
     * Whether object may be put in cache or not
     *
     * @var boolean
     */
    protected $bIsCacheable = true;

    /**
     * Whether object may be put in cache or not
     *
     * @var boolean
     */
    protected $bIsI18n = false;

    /**
     * Object caching duration in seconds
     *
     * @var integer
     */
    protected $iCacheDuration = 10;

    /**
     * Whether object has been successfully loaded or not
     *
     * @var boolean
     */
    protected $bIsLoaded = false;

    /**
     * If loaded with an array of primary keys identifier
     *
     * @var object collection
     */
    protected $aCollection = array();

    /**
     * Enhance perf
     *
     * @var string
     */
    protected $sChildClass;

    /**
     * Current instance locale (country lang info ex: BE_fr)
     * @var string
     */
    protected $sLocale = null;

    /**
     * Cache identifiers params at instance
     *
     * Collection Idientifiers list at instance (optionnal)
     */
    protected $aOriginIds = array();

    /**
     * Mapped entities with mapping properties
     *
     * @see EntityMapper 
     * @var array
     */
    protected $aMappingConfiguration = array();

    /**
     * Internationalization configuration
     * @var array
     */
    protected $aTranslatedAttributes = array();

    /**
     * List of entity fields (parsed from database)
     *
     * @var array
     */
    protected $aFields = array();

    /**
     * Excluded for CRUD operations
     *
     * @var array
     */
    protected $aEntityRestrictedAttributes = array();

    /**
     * Entities Mapper instance
     * @var Mapper
     */
    protected $oMapperInstance = null;

    /**
     * Constructor
     *
     * @param mixed int|array $mValue   Primary key value or parameters to load. If left empty, blank object will be instantiated
     * @param string $sLocale
     * @throws EntityException
     */
    public function __construct($mValue = null, $sLocale = null)
    {
        $this->loadAttributes();
        if (is_null($mValue) === false) {
            if (is_string($mValue) === true || is_int($mValue) === true) {
                // Build only one object
                $this->{static::PRIMARY_KEY} = $mValue;
                $this->loadByPrimaryKey();
            } elseif (is_array($mValue) === true) {
                $this->loadByParameters($mValue);
            }
        }

        # Instance local for i18n
        $this->sLocale = $sLocale;

        # Post init hook
        $this->postLoadedHook();
    }

    /**
     * Return the Entity class name
     *
     * @return string
     */
    public function __toString()
    {
        return $this->sChildClass . (($this->isLoaded()) ? ' #' . $this->getId() : '');
    }

    /**
     * Load object with provided data
     * Data must be an array of key/value, key being table fields names
     *
     * @param array $aData
     *            Object data
     * @param boolean $bRefreshCache
     *            Whether cache must be updated or not
     * @param string $sLocale
     * @return boolean TRUE if object was successfully loaded, otherwise FALSE
     */
    public function loadByData(array $aData, $bRefreshCache = true, $sCacheKey = null, $sLocale = null)
    {
        $this->loadAttributes();
        foreach ($aData as $sName => $mValue) {
            $this->{$sName} = $mValue;
        }

        if ($bRefreshCache && isset($aData[static::PRIMARY_KEY]) && !empty($this->iCacheDuration)) {
            $sObjectCacheKey = self::getCacheKey($aData[static::PRIMARY_KEY]);
            // If given cache key is not object main key, we save relation between given cache key and object
            if (!is_null($sCacheKey) && $sCacheKey !== $sObjectCacheKey) {
                Memcache::set($sCacheKey, $aData[static::PRIMARY_KEY], Memcache::CACHE_TIME_DAY);
            }
            Memcache::set($sObjectCacheKey, $aData, $this->iCacheDuration);
        }

        # Instance local for i18n
        if (is_null($sLocale) === false) {
            $this->sLocale = $sLocale;
        }

        # Set instance to loaded
        $this->bIsLoaded = true;

        # Run the post loaded hook
        $this->postLoadedHook();

        return $this->isLoaded();
    }

    protected function postLoadedHook()
    {
        # Store called class
        $this->sChildClass = get_called_class();

        # Internationalization support
        if ($this->isI18n() === true && is_null($this->sLocale) === false) {
            $this->loadTranslation();
        }

    }

    /**
     * Load object depending on given parameters values
     * Parameters is a key/value array, key being table fields names
     *
     * @param array $aParameters Parameters to check
     * @param string $sLocale
     * @throws EntityException
     */
    public function loadByParameters(array $aParameters)
    {
        if (empty($aParameters)) {
            throw new EntityException(
                sprintf(
                    EntityException::$aErrors[EntityException::ERROR_NO_PARAMETERS_TO_LOAD_ENTITY],
                    get_called_class()
                ),
                EntityException::ERROR_NO_PARAMETERS_TO_LOAD_ENTITY
            );
        }

        $oSelectQuery = new Select();
        # Build where condition
        foreach ($aParameters as $mKey => $mValue) {
            $oSelectQuery->addWhereCondition(Operators::equal($mKey), QueryAbstract::QUERY_WHERE_CONNECTOR_AND);
        }
        $oSelectQuery->addColumn('*')
            ->setFrom($this->getTableName(), true);

        return $this->loadByQuery(
            $oSelectQuery->build(),
            $aParameters,
            true,
            Memcache::getKey(__METHOD__, $aParameters)
        );
    }

    /**
     * Load object by executing given SQL query
     * NOTE: method is protected because query must be generated within child class along with cache key definition
     *
     * @param string $sQuery SQL query to use for loading object
     * @param array $aBoundedValues
     * @param string $sCacheKey Cache key for given query
     * @return boolean TRUE if object was successfully loaded, otherwise FALSE
     * @throws EntityException
     */
    protected function loadByQuery($sQuery, array $aBoundedValues = array(), $bUseCache = true, $sCacheKey = null)
    {
        $bRefreshCache = false;
        if ($bUseCache && $this->isCacheable() && ! empty($this->iCacheDuration)) {
            if (is_null($sCacheKey)) {
                $sCacheKey = Memcache::getKey(get_called_class(), $sQuery, $aBoundedValues);
            }
            $aObject = Memcache::get($sCacheKey);
        }

        if (! isset($aObject) || $aObject === false) {
            $bRefreshCache = true;

            if (($oStatement = Pdo::dbQuery($sQuery, $aBoundedValues)) === false) {
                throw new EntityException(
                    sprintf(
                        EntityException::$aErrors[EntityException::ERROR_UNABLE_TO_CONSTRUCT_ENTITY],
                        array(get_called_class(), $sQuery)
                    ),
                    EntityException::ERROR_UNABLE_TO_CONSTRUCT_ENTITY
                );
            }

            if ($oStatement->rowCount() === 0) {
                return NULL;
            }

            if ($oStatement->rowCount() > 1) {
                throw new EntityException(
                    sprintf(
                        EntityException::$aErrors[EntityException::ERROR_MORE_THAN_ONE_ENTITY_FOUND],
                        array(get_called_class(), $sQuery)
                    ),
                    EntityException::ERROR_MORE_THAN_ONE_ENTITY_FOUND
                );
            }

            $aObject = $oStatement->fetchAll(\PDO::FETCH_ASSOC);
            $aObject = $aObject[0];
        }
        return $this->loadByData($aObject, $bRefreshCache, $this->getLocale());
    }

    /**
     * Retrieve the cached instances of objects
     *
     * @param integer $iId
     *            Instance ID (primary key of table)
     * @return boolean TRUE if instance is in cache, otherwise false
     */
    public static function getCached($iId)
    {
        return Memcache::get(self::getCacheKey($iId));
    }

    /**
     * Retrieve cache key for single instance of class for given ID
     *
     * @param int $iId
     * @return string
     */
    public static function getCacheKey($iId)
    {
        return Memcache::getKey(get_called_class(), $iId);
    }

    /**
     * Add record corresponding to object on database
     *
     * @return boolean TRUE if record was successfully inserted, otherwise FALSE
     * @throws EntityException
     */
    protected function _create()
    {
        try {
            # Retrieve instance setted values
            $aParameters = $this->getInstanceData();

            # Build insert query
            $oInsert = new Insert();
            $oInsert->setFrom($this->getTableName(), true)
                ->setParameters($aParameters);

            $oStatement = Pdo::dbQuery($oInsert->build(), $aParameters);

            # Set primary key value
            $this->{static::PRIMARY_KEY} = Pdo::lastInsertId();

            return (bool) $this->bIsLoaded = ($oStatement !== false && $this->{static::PRIMARY_KEY} > 0);

        } catch (\Exception $oException) {

            # Log exception
            $oLog = new Log();
            $oLog->setMessage($oException->getMessage())
                ->setErrorCode($oException->getCode())
                ->setStackTrace(debug_backtrace())
                ->setType(Log::TYPE_EXCEPTION)
                ->setDatetime(new \DateTime())
                ->create();

            return false;
        }

    }

    /**
     * Update record corresponding to object in database
     *
     * @return boolean          TRUE if entity was successfully updated, otherwise FALSE
     * @throws EntityException
     */
    protected function _update()
    {

        try {

            if (empty($this->{static::PRIMARY_KEY})) {
                throw new EntityException(
                    sprintf(
                        EntityException::$aErrors[EntityException::ERROR_UPDATE_WITH_NO_PRIMARY_KEY],
                        get_called_class()
                    ),
                    EntityException::ERROR_UPDATE_WITH_NO_PRIMARY_KEY
                );
            }

            # Handle Entity history if needed
            if ($this->bIsHistorized === true) {
                $oOriginalObject = new $this->sChildClass($this->{static::PRIMARY_KEY});
                $oEntityHistory = new History($oOriginalObject, $this->getUser());
            }

            # Retrieve instance setted values
            $aParameters = $this->getInstanceData();

            # Prepare parameters
            $aValues = array_values($aParameters);
            # Add the current Entity instance id for the query where clause
            $aValues[] = $this->getId();

            $oUpdate = new Update();
            $oUpdate->setFrom($this->getTableName(), true)
                ->setParameters($aParameters)
                ->addWhereCondition(Operators::equal($this->getPrimaryKeyName(), false));

            $oStatement = Pdo::dbQuery(
                $oUpdate->build(),
                $aValues
            );

            if ($oStatement !== false && $this->refresh()) {
                # Store Entity history
                if ($this->bIsHistorized) {
                    if ($oEntityHistory->save($aParameters) === false) {
                        throw new EntityException(
                            sprintf(
                                EntityException::$aErrors[EntityException::ERROR_UNABLE_TO_STORE_ENTITY_HISTORY],
                                $this->getEntityName()
                            ),
                            EntityException::ERROR_UNABLE_TO_STORE_ENTITY_HISTORY
                        );
                    }
                }

                return true;
            }
            return false;
        } catch (\Exception $oException) {

            # Log exception
            $oLog = new Log();
            $oLog->setMessage($oException->getMessage())
                ->setErrorCode($oException->getCode())
                ->setStackTrace(debug_backtrace())
                ->setType(Log::TYPE_EXCEPTION)
                ->setDatetime(new \DateTime())
                ->create();

            return false;
        }
    }

    /**
     * Delete row corresponding to current instance in database and reset instance
     * 
     * @throws EntityException
     * @return boolean TRUE if deletion was successful, otherwise FALSE
     */
    protected function _delete()
    {
        try {
            if ($this->isDeletable() === false) {
                throw new EntityException(
                    sprintf(EntityException::$aErrors[EntityException::ERROR_ENTITY_NOT_DELETABLE], get_called_class()),
                    EntityException::ERROR_ENTITY_NOT_DELETABLE
                );
            }

            if ($this->isLoaded() === false) {
                throw new EntityException(
                    EntityException::$aErrors[EntityException::ERROR_DELETE_NOT_LOADED_ENTITY],
                    EntityException::ERROR_DELETE_NOT_LOADED_ENTITY
                );
            }

            # Build delete query
            $oDelete = new Delete();
            $oDelete->setFrom($this->getTableName(), true)
                ->addWhereCondition(Operators::equal($this->getPrimaryKeyName()));

            # Build query parameters
            $aParameters = array(
                $this->getPrimaryKeyName() => $this->getId()
            );

            $oStatement = Pdo::dbQuery(
                $oDelete->build(),
                $aParameters
            );

            # Reset current instance
            $this->reset();

            return ($oStatement !== false && $this->isLoaded() === false);

        } catch (\Exception $oException) {

            # Log exception
            $oLog = new Log();
            $oLog->setMessage($oException->getMessage())
                ->setErrorCode($oException->getCode())
                ->setStackTrace(debug_backtrace())
                ->setType(Log::TYPE_EXCEPTION)
                ->setDatetime(new \DateTime())
                ->create();

            return false;
        }

    }

    /**
     * Refresh object data from database
     *
     * @return boolean TRUE if object was successfully refreshed, otherwise FALSE
     */
    public function refresh()
    {
        return $this->loadByPrimaryKey(false);
    }

    /**
     * Get object instance value as an array
     *
     * @return array
     */
    protected function getInstanceData()
    {
        $aParameters = array();
        foreach ($this->getAttributes() as $sFieldName) {
            if (isset($this->{$sFieldName})) {
                $aParameters[$sFieldName] = $this->{$sFieldName};
            }
        }
        return $aParameters;
    }

    /**
     * Load Entity using its primary key
     *
     * @param boolean $bUseCache        Whether object caching must be used to retrieve data or not
     * @return boolean                  TRUE if object was successfully loaded, otherwise FALSE
     * @throws EntityException
     */
    protected function loadByPrimaryKey($bUseCache = true)
    {
        if (! isset($this->{static::PRIMARY_KEY})) {
            throw new EntityException(
                sprintf(
                    EntityException::$aErrors[EntityException::ERROR_CANNOT_LOAD_WITH_EMPTY_PK],
                    array(get_called_class(), static::PRIMARY_KEY)
                ),
                EntityException::ERROR_CANNOT_LOAD_WITH_EMPTY_PK
            );
        }

        $oSelect = new Select();
        $oSelect->setFrom('`' . $this->getTableName() . '`')
            ->addColumn('*')
            ->setLimit(1)
            ->addWhereCondition(Operators::equal($this->getPrimaryKeyName()));

        $aParameters = array(
            $this->getPrimaryKeyName() => $this->{static::PRIMARY_KEY}
        );

        return $this->loadByQuery(
            $oSelect->build(),
            $aParameters,
            $bUseCache,
            Memcache::getKey(get_called_class())
        );
    }

    /**
     * Load a mapped entity
     *
     * @param Entity $oMappedEntity
     * @param array $aParameters
     * @param array $aOrders
     * @param mixed array|int $mLimit
     * @param bool $bForceLoad
     * @return mixed Entity|EntityCollection    NULL if something went wrong
     */
    public function loadMapped(
        Entity $oMappedEntity,
        array $aParameters = array(),
        array $aOrders = array(),
        $mLimit = null,
        $bForceLoad = false
    )
    {
        if ($this->isLoaded() === true && $this->loadMapper() === true) {

            if ($bForceLoad === true) {
                $this->oMapperInstance->setForceLoad(true);
            }

            return $this->oMapperInstance->loadMapped(
                $oMappedEntity,
                $aParameters,
                $aOrders,
                $mLimit
            );
        }
        return null;
    }

    /**
     * Store a mapped Entity
     *
     * @param Entity $oMappedEntity
     * @return bool
     */
    public function storeMapped(Entity $oMappedEntity)
    {
        if ($this->loadMapper() === true) {
            return $this->oMapperInstance->store($oMappedEntity);
        }
        return false;
    }

    /**
     * Load the entities Mapper component
     *
     * @return bool
     */
    protected function loadMapper()
    {
        if (is_null($this->oMapperInstance) === true) {

            # Create new Entities Mapper instance
            $this->oMapperInstance = new Mapper();
            $this->oMapperInstance->setSourceEntity($this);

        }

        return (bool) ($this->oMapperInstance instanceof Mapper);
    }

    /**
     * Reset current instance to blank state
     */
    public function reset()
    {
        $aEntityAttrs = $this->getAttributes();
        foreach ($this as $sKey => $mValue) {
            if (array_key_exists($sKey, $aEntityAttrs) === false) {
                unset($this->$sKey);
            }
        }

        $this->bIsLoaded = false;

        return (bool) ($this->bIsLoaded === false);
    }

    /**
     * Compute the EntityCollection class name
     *
     * @return string
     */
    public function computeCollectionClassName()
    {
        $sCollectionClassName = str_replace(array(
            '\\Entities',
        ), '\\Entities\Collection', get_called_class());
        return $sCollectionClassName . 'Collection';
    }

    /**
     * Compute entity foreign key name
     * @return string
     */
    public function computeForeignKeyName()
    {
        return $this->getTableName() . self::FOREIGN_KEY_SEPARATOR . $this->getPrimaryKeyName();
    }

    /**
     * Check whether object was successfully loaded
     *
     * @return boolean TRUE if object was successfully loaded, otherwise FALSE
     */
    public function isLoaded()
    {
        return $this->bIsLoaded;
    }

    /**
     * Check whether instance is in cache or not
     *
     * @return boolean TRUE if instance is in cache, otherwise false
     */
    public function isInCache()
    {
        return (Memcache::get(Memcache::getKey(get_called_class(), $this->getId())) !== false);
    }

    /**
     * Check if the entity is searchable
     *
     * @return boolean
     */
    public function isSearchable()
    {
        return $this->bIsSearchable;
    }

    /**
     * Check if the Entity is deletable
     *
     * @return bool
     */
    public function isDeletable()
    {
        return $this->bIsDeletable;
    }

    /**
     * Check if Entity is cacheable
     *
     * @return bool
     */
    public function isCacheable()
    {
        return $this->bIsCacheable;
    }

    /**
     * Check if Entity is historized
     *
     * @return bool
     */
    public function isHistorized()
    {
        return $this->bIsHistorized;
    }

    /**
     * Check if Entity use internationalization
     *
     * @return bool
     */
    public function isI18n()
    {
        return $this->bIsI18n;
    }

    /**
     * Retrieve instance ID (primary key)
     *
     * @return mixed Instance ID
     * @throws EntityException
     */
    public function getId()
    {
        if ($this->bIsLoaded !== true) {
            throw new EntityException(
                EntityException::$aErrors[EntityException::ERROR_CANNOT_GET_ID_OF_NOT_LOADED_ENTITY],
                EntityException::ERROR_CANNOT_GET_ID_OF_NOT_LOADED_ENTITY
            );
        }
        return (int) $this->{static::PRIMARY_KEY};
    }

    /**
     * Entity attributes fields accessor
     * @return array
     */
    public function getFields()
    {
        return $this->aFields;
    }

    /**
     * Return Entity instance name
     *
     * @return string
     */
    public function getEntityName()
    {
        return static::ENTITY;
    }

    /**
     * Mapped entities configuration accessor
     * @return array
     */
    public function getMappingConfiguration()
    {
        return $this->aMappingConfiguration;
    }

    /**
     * @return array
     */
    public function getTranslatedAttributes()
    {
        return $this->aTranslatedAttributes;
    }

    /**
     * @return string
     */
    public function getLocale()
    {
        return $this->sLocale;
    }

    /**
     * Get entity SGBD table name
     *
     * @return string
     */
    public function getTableName()
    {
        return static::TABLE_NAME;
    }

    /**
     * Get entity primary key attribute name
     *
     * @return string
     */
    public function getPrimaryKeyName()
    {
        return static::PRIMARY_KEY;
    }

    /**
     * Get inherited instance class name
     * @return string
     */
    public function getChildClass()
    {
        return $this->sChildClass;
    }

}

class EntityException extends CoreException
{

    const ERROR_NO_PARAMETERS_TO_LOAD_ENTITY        = 2;
    const ERROR_UNABLE_TO_CONSTRUCT_ENTITY          = 3;
    const ERROR_MORE_THAN_ONE_ENTITY_FOUND          = 4;
    const ERROR_UPDATE_WITH_NO_PRIMARY_KEY          = 5;
    const ERROR_UNABLE_TO_STORE_ENTITY_HISTORY      = 6;
    const ERROR_ENTITY_NOT_DELETABLE                = 7;
    const ERROR_DELETE_NOT_LOADED_ENTITY            = 8;
    const ERROR_CANNOT_LOAD_WITH_EMPTY_PK           = 9;
    const ERROR_CANNOT_GET_ID_OF_NOT_LOADED_ENTITY  = 10;

    public static $aErrors = array(
        self::ERROR_NO_PARAMETERS_TO_LOAD_ENTITY        => 'No parameter provided for loading object of type: %s.',
        self::ERROR_UNABLE_TO_CONSTRUCT_ENTITY          => 'Unable to construct object of class "%s" with query: %s.',
        self::ERROR_MORE_THAN_ONE_ENTITY_FOUND          => 'More than "%s" Entity type found for query %s.',
        self::ERROR_UPDATE_WITH_NO_PRIMARY_KEY          => 'Cannot update object of class %s with no primary key value.',
        self::ERROR_UNABLE_TO_STORE_ENTITY_HISTORY      => 'Unable to store Entity history for object: %s.',
        self::ERROR_ENTITY_NOT_DELETABLE                => 'Cannot delete object of type "%s", this not allowed.',
        self::ERROR_DELETE_NOT_LOADED_ENTITY            => 'Cannot delete Entity, instance not loaded properly.',
        self::ERROR_CANNOT_LOAD_WITH_EMPTY_PK           => 'Cannot load object of class "%s" by primary key, no value provided for key %s',
        self::ERROR_CANNOT_GET_ID_OF_NOT_LOADED_ENTITY  => 'Cannot get ID of object not loaded',
    );
}

