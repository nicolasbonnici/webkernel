<?php
namespace Library\Core\Orm;

use Library\Core\Cache;
use Library\Core\Database\Database;
use Library\Core\Database\Query\Select;
use Library\Core\Database\Query\Where;

/**
 * On the fly ORM CRUD managment abstract class
 *
 * @todo decouper en composants unit testés
 *
 * @author Antoine <antoine.preveaux@bazarchic.com>
 * @author niko <nicolasbonnici@gmail.com>
 *
 * @important Entities need a primary auto incremented index (id[entity])
 *
 *       @dependancy \Library\Core\Validator
 *       @dependancy \Library\Core\Cache
 *       @dependancy Database
 */
abstract class Entity extends EntityAttributes
{
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
    protected $aMappedEntities = array();

    /**
     * Constructor
     *
     * @param mixed $mPrimaryKey
     *            Primary key. If left empty, blank object will be instanciated
     * @throws EntityException
     */
    public function __construct($mPrimaryKey = null)
    {
        // If we just want to instanciate a blank object, do not pass any parameter to constructor
        $this->loadAttributes();
        if (! is_null($mPrimaryKey) && is_string($mPrimaryKey) || is_int($mPrimaryKey)) {
            // Build only one object
            $this->{static::PRIMARY_KEY} = $mPrimaryKey;
            $this->loadByPrimaryKey();
        } elseif (is_array($mPrimaryKey)) {
            // Array case
            $this->loadByParameters($mPrimaryKey);
        }

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
    public function loadByData($aData, $bRefreshCache = true, $sCacheKey = null)
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

        return $this->loadByQuery(
            'SELECT * FROM ' . static::TABLE_NAME .
                ' WHERE `' . implode('` = ? AND `', array_keys($aParameters)) . '` = ? ', array_values($aParameters),
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
     *            Binded values for query
     * @param boolean $bUseCache
     *            Whether object caching must be used to retrieve data or not
     * @param string $sCacheKey
     *            Cache key for given query
     * @return boolean TRUE if object was successfully loaded, otherwise FALSE
     * @throws EntityException
     */
    protected function loadByQuery($sQuery, array $aBindedValues = array(), $bUseCache = true, $sCacheKey = null)
    {
        $bRefreshCache = false;
        if ($bUseCache && $this->bIsCacheable && ! empty($this->iCacheDuration)) {
            if (is_null($sCacheKey)) {
                $sCacheKey = Cache::getKey(get_called_class(), $sQuery, $aBindedValues);
            }
            $aObject = Cache::get($sCacheKey);
        }

        if (! isset($aObject) || $aObject === false) {
            $bRefreshCache = true;

            if (($oStatement = Database::dbQuery($sQuery, $aBindedValues)) === false) {
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
     * Add record corresponding to object to database
     *
     * @return boolean TRUE if record was successfully inserted, otherwise FALSE
     * @throws EntityException
     */
    public function add()
    {
        $aInsertedFields = array();
        $aInsertedValues = array();
        foreach ($this->aFields as $sFieldName => $aFieldInfos) {
            if (
                isset($this->{$sFieldName}) &&
                $this->validate($sFieldName, $this->{$sFieldName})
            ) {
                $aInsertedFields[] = $sFieldName;
                $aInsertedValues[] = $this->{$sFieldName};
            }
        }

        if (count($aInsertedFields) === 0) {
            throw new EntityException('Cannot create empty object of class ' . get_called_class());
        }

        try {
        	$sQuery = 'INSERT INTO ' . static::TABLE_NAME . '(`' . implode('`,`', $aInsertedFields) . '`) VALUES (?' . str_repeat(',?', count($aInsertedValues) - 1) . ')';
            $oStatement = Database::dbQuery($sQuery, $aInsertedValues);
            $this->{static::PRIMARY_KEY} = Database::lastInsertId();
            return $this->bIsLoaded = ($oStatement !== false && intval($this->{static::PRIMARY_KEY}) > 0);
            
        } catch (\Exception $oException) {
            return false;
        }
		
    }

    /**
     * Update record corresponding to object in database
     *
     * @return boolean TRUE if record was successfully updated, otherwise FALSE
     * @throws EntityException
     */
    public function update()
    {
        $aUpdatedFields = array();
        $aUpdatedValues = array();
        foreach ($this->aFields as $sFieldName => $aFieldInfos) {
            if (isset($this->{$sFieldName})) {
                $aUpdatedFields[] = $sFieldName;
                $aUpdatedValues[] = $this->{$sFieldName};
            }
        }

        if (count($aUpdatedFields) === 0) {
            throw new EntityException('Cannot update empty object of class ' . get_called_class());
        }

        if (empty($this->{static::PRIMARY_KEY})) {
            throw new EntityException('Cannot update object of class ' . get_called_class() . ' with no primary key value');
        }

        try {
            $oOriginalObject = new $this->sChildClass($this->{static::PRIMARY_KEY});

            if ($this->bIsHistorized) {
                $this->saveHistory($oOriginalObject);
            }

            $aUpdatedValues[] = $this->{static::PRIMARY_KEY};
            $oStatement = Database::dbQuery('UPDATE ' . static::TABLE_NAME . ' SET `' . implode('` = ?, `', $aUpdatedFields) . '` = ? WHERE `' . static::PRIMARY_KEY . '` = ?', $aUpdatedValues);
            
            return ($oStatement !== false && $this->refresh());
        } catch (\Exception $oException) {
            return false;
        }

    }

    /**
     * Delete row corresponding to current instance in database and reset instance
     * 
     * @todo Need review on return ...
     * 
     * @throws EntityException
     * @return boolean TRUE if deletion was successful, otherwise FALSE
     */
    public function delete()
    {
        if (! $this->bIsDeletable) {
            throw new EntityException('Cannot delete object of type "' . get_called_class() . '", this type of object is not deletable');
        }

        if (! $this->bIsLoaded) {
            throw new EntityException('Cannot delete entry, object not loaded properly');
        }

        try {
            $oStatement = Database::dbQuery('DELETE FROM `' . static::TABLE_NAME . '` WHERE `' . static::PRIMARY_KEY . '` = ?', array(
                $this->{static::PRIMARY_KEY}
            ));
            $this->reset();
            
	        return ($oStatement !== false && $this->isLoaded() === false);
	        
        } catch (\Exception $oException) {
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
     * Load object using its primary key
     *
     * @param boolean $bUseCache
     *            Whether object caching must be used to retrieve data or not
     * @return boolean TRUE if object was successfully loaded, otherwise FALSE
     * @throws EntityException
     */
    protected function loadByPrimaryKey($bUseCache = true)
    {
        if (! isset($this->{static::PRIMARY_KEY})) {
            throw new EntityException('Cannot load object of class ' . get_called_class() . ' by primary key, no value provided for key ' . static::PRIMARY_KEY);
        }

        return $this->loadByQuery('SELECT * FROM ' . static::TABLE_NAME . ' WHERE `' . static::PRIMARY_KEY . '` = ?', array(
            $this->{static::PRIMARY_KEY}
        ), $bUseCache, Cache::getKey(get_called_class(), $this->{static::PRIMARY_KEY}));
    }

    /**
     * Reset current instance to blank state
     */
    public function reset()
    {
        $aEntityAttributes = $this->getAttributes();
        foreach ($this as $sKey => $mValue) {
            if (! in_array($sKey, $aEntityAttributes)) {
                unset($this->$sKey);
            } else {
                $this->$sKey = null;
            }
        }

        $this->bIsLoaded = false;
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
     * @param integer $iId
     *            Instance ID (primary key of table)
     * @return boolean TRUE if instance is in cache, otherwise false
     */
    public static function isInCache($iId)
    {
        return (Cache::get(Cache::getKey(get_called_class(), $iId)) !== false);
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
     * Mapped entities configuration accessor
     * @return array
     */
    public function getMappedEntities()
    {
        return $this->aMappedEntities;
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

