<?php
namespace Library\Core\Entity;

use Library\Core\Cache;
use Library\Core\Database\Pdo;
use Library\Core\Database\Query\Delete;
use Library\Core\Database\Query\Insert;
use Library\Core\Database\Query\Operators;
use Library\Core\Database\Query\QueryAbstract;
use Library\Core\Database\Query\Select;
use Library\Core\Database\Query\Update;

/**
 * On the fly ORM CRUD management abstract class
 *
 * Class Entity
 * @package Library\Core\Orm
 *
 * @author niko <nicolasbonnici@gmail.com>
 */
abstract class Entity extends Attributes
{

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
     * List of entity fields (parsed from database)
     *
     * @var array
     */
    protected $aFields = array();

    /**
     * Constructor
     *
     * @param mixed int|array $mValue   Primary key value or parameters to load. If left empty, blank object will be instantiated
     * @throws EntityException
     */
    public function __construct($mValue = null)
    {
        // If we just want to instanciate a blank object, do not pass any parameter to constructor
        $this->loadAttributes();
        if (! is_null($mValue) && is_string($mValue) || is_int($mValue)) {
            // Build only one object
            $this->{static::PRIMARY_KEY} = $mValue;
            $this->loadByPrimaryKey();
        } elseif (is_array($mValue)) {
            $this->loadByParameters($mValue);
        }

        # Store called class
        $this->sChildClass = get_called_class();
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
     * @return boolean TRUE if object was successfully loaded, otherwise FALSE
     */
    public function loadByData(array $aData, $bRefreshCache = true, $sCacheKey = null)
    {
        $this->loadAttributes();
        foreach ($aData as $sName => $mValue) {
            $this->{$sName} = $mValue;
        }

        if ($bRefreshCache && isset($aData[static::PRIMARY_KEY]) && !empty($this->iCacheDuration)) {
            $sObjectCacheKey = self::getCacheKey($aData[static::PRIMARY_KEY]);
            // If given cache key is not object main key, we save relation between given cache key and object
            if (!is_null($sCacheKey) && $sCacheKey !== $sObjectCacheKey) {
                Cache::set($sCacheKey, $aData[static::PRIMARY_KEY], Cache::CACHE_TIME_DAY);
            }
            Cache::set($sObjectCacheKey, $aData, $this->iCacheDuration);
        }
        $this->bIsLoaded = true;
        
        return $this->isLoaded();
    }

    /**
     * Load object depending on given parameters values
     * Parameters is a key/value array, key being table fields names
     *
     * @param array $aParameters
     *            Parameters to check
     * @return boolean TRUE if object was successfully loaded, otherwise FALSE
     * @throws EntityException
     */
    public function loadByParameters(array $aParameters)
    {
        if (empty($aParameters)) {
            throw new EntityException('No parameter provided for loading object of type ' . get_called_class());
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
            Cache::getKey(__METHOD__, $aParameters)
        );
    }

    /**
     * Load object by executing given SQL query
     * NOTE: method is protected because query must be generated within child class along with cache key definition
     *
     * @param string $sQuery
     *            SQL query to use for loading object
     * @param array $aBindedValues
     * @param string $sCacheKey
     *            Cache key for given query
     * @return boolean TRUE if object was successfully loaded, otherwise FALSE
     * @throws EntityException
     */
    protected function loadByQuery($sQuery, array $aBindedValues = array(), $bUseCache = true, $sCacheKey = null)
    {
        $bRefreshCache = false;
        if ($bUseCache && $this->isCacheable() && ! empty($this->iCacheDuration)) {
            if (is_null($sCacheKey)) {
                $sCacheKey = Cache::getKey(get_called_class(), $sQuery, $aBindedValues);
            }
            $aObject = Cache::get($sCacheKey);
        }

        if (! isset($aObject) || $aObject === false) {
            $bRefreshCache = true;

            if (($oStatement = Pdo::dbQuery($sQuery, $aBindedValues)) === false) {
                throw new EntityException('Unable to construct object of class ' . get_called_class() . ' with query ' . $sQuery);
            }

            if ($oStatement->rowCount() === 0) {
                return NULL;
            }

            if ($oStatement->rowCount() > 1) {
                throw new EntityException('More than one occurence of object try to build a entityCollection?...' . get_called_class() . ' found for query ' . $sQuery);
            }

            $aObject = $oStatement->fetchAll(\PDO::FETCH_ASSOC);
            $aObject = $aObject[0];
        }
        return $this->loadByData($aObject, $bRefreshCache);
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
        return \Library\Core\Cache::get(self::getCacheKey($iId));
    }

    /**
     * Retrieve cache key for single instance of class for given ID
     *
     * @param int $iId
     * @return string
     */
    public static function getCacheKey($iId)
    {
        return \Library\Core\Cache::getKey(get_called_class(), $iId);
    }

    /**
     * Add record corresponding to object on database
     *
     * @return boolean TRUE if record was successfully inserted, otherwise FALSE
     * @throws EntityException
     */
    public function add()
    {
        try {
            # Retrieve instance setted values
            $aParameters = $this->getInstanceData();

            # Build insert query
            $oInsert = new Insert();
            $oInsert->setFrom($this->getTableName(), true)
                ->setParameters($aParameters);

            $oStatement = Pdo::dbQuery($oInsert->build(), array_values($aParameters));

            # Set primary key value
            $this->{static::PRIMARY_KEY} = Pdo::lastInsertId();

            return (bool) $this->bIsLoaded = ($oStatement !== false && $this->{static::PRIMARY_KEY} > 0);

        } catch (\Exception $oException) {

            # Throw exceptions on development environment
            if (defined('ENV') && ENV === 'dev') {
                throw new EntityException($oException->getMessage(), $oException->getCode());
            }

            return false;
        }

    }

    /**
     * Update record corresponding to object in database
     *
     * @return boolean          TRUE if entity was successfully updated, otherwise FALSE
     * @throws EntityException
     */
    public function update()
    {

        try {

            if (empty($this->{static::PRIMARY_KEY})) {
                throw new EntityException(
                    'Cannot update object of class ' . get_called_class() . ' with no primary key value'
                );
            }

            # Handle Entity history if needed
            if ($this->bIsHistorized === true) {
                $oOriginalObject = new $this->sChildClass($this->{static::PRIMARY_KEY});
                $oEntityHistory = new History($oOriginalObject);
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
                            sprintf('Unable to store Entity history for object: %s', $this->getEntityName())
                        );
                    }
                }

                return true;
            }
        } catch (\Exception $oException) {

            # Throw exceptions on development environment
            if (defined('ENV') && ENV === 'dev') {
                throw new EntityException($oException->getMessage(), $oException->getCode());
            }

            return false;
        }

    }

    /**
     * Delete row corresponding to current instance in database and reset instance
     * 
     * @throws EntityException
     * @return boolean TRUE if deletion was successful, otherwise FALSE
     */
    public function delete()
    {

        try {
            if ($this->isDeletable() === false) {
                throw new EntityException('Cannot delete object of type "' . get_called_class() . '", this type of object is not deletable');
            }

            if ($this->isLoaded() === false) {
                throw new EntityException('Cannot delete entry, object not loaded properly');
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

            # Throw exceptions on development environment
            if (defined('ENV') && ENV === 'dev') {
                throw new EntityException($oException->getMessage(), $oException->getCode());
            }

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
        foreach ($this->aFields as $sFieldName => $aFieldInfos) {
            if (
                isset($this->{$sFieldName}) &&
                $this->validate($sFieldName, $this->{$sFieldName})
            ) {
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
                'Cannot load object of class ' . get_called_class() . ' by primary key, no value provided for key ' . static::PRIMARY_KEY
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
            Cache::getKey(get_called_class())
        );
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
            } else {
                $this->$sKey = null;
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
        return (Cache::get(Cache::getKey(get_called_class(), $this->getId())) !== false);
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
     * Check if Entity is cachable
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
     * Retrieve instance ID (primary key)
     *
     * @return mixed Instance ID
     * @throws EntityException
     */
    public function getId()
    {
        if (! $this->bIsLoaded) {
            throw new EntityException('Cannot get ID of object not loaded');
        }
        return (int) $this->{static::PRIMARY_KEY};
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

class EntityException extends \Exception
{
}

