<?php
namespace Library\Core;

/**
 * On the fly ORM CRUD abstract class managment
 *
 * @author Antoine <antoine.preveaux@bazarchic.com>
 * @author niko <nicolasbonnici@gmail.com>
 *
 *         @important Entities need a primary auto incremented index (id[entity])
 * @todo optimiser la gestion du cache dans le composant Cache
 *       @dependancy \Library\Core\Validator
 *       @dependancy \Library\Core\Cache
 *       @dependancy \Library\Core\Database
 */
abstract class Entity extends Database
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
     * List of associated table's fields
     *
     * @var array
     */
    protected $aFields = array();

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
     * List of class members that may be used to chain
     * This array is composed of arrays with
     * keys being class member name
     * values being 'field' and 'class'
     * 'field' refers to current class member name which value will be used as the ID of chained object
     * 'class' refers to chained object class name
     * Example:
     * protected $aLinkedEntities = array(
     * 'membre' => array(
     * 'field' => 'idmembre',
     * 'class' => 'db_Membres'
     * )
     * );
     *
     * @var array
     */
    protected $aLinkedEntities = array();

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
        $this->loadFields();
        if (! is_null($mPrimaryKey) && is_string($mPrimaryKey) || is_int($mPrimaryKey)) {

            // @see Build only one object
            $this->{static::PRIMARY_KEY} = $mPrimaryKey;
            $this->loadByPrimaryKey();
        } elseif (is_array($mPrimaryKey)) {
            // @see Sinon si c'est un array je load l'objet via different paramètres
            $this->loadByParameters($mPrimaryKey);
        }

        $this->sChildClass = get_called_class();
    }

    /**
     * Return the Entity class name
     *
     * @return string
     */
    public final function __toString()
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
        $this->loadFields();
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

        return ($this->bIsLoaded = true);
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

        return $this->loadByQuery('SELECT * FROM ' . static::TABLE_NAME . ' WHERE `' . implode('` = ? AND `', array_keys($aParameters)) . '` = ? ', array_values($aParameters), true, Cache::getKey(__METHOD__, $aParameters));
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

            if (($oStatement = \Library\Core\Database::dbQuery($sQuery, $aBindedValues)) === false) {
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
     * @param unknown $iId
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
            if (isset($this->{$sFieldName}) && ! is_null($this->{$sFieldName}) && $this->validateDataIntegrity($sFieldName, $this->{$sFieldName})) {
                $aInsertedFields[] = $sFieldName;
                $aInsertedValues[] = $this->{$sFieldName};
            }
        }

        if (count($aInsertedFields) === 0) {
            throw new EntityException('Cannot create empty object of class ' . get_called_class());
        }

        try {
            $oStatement = \Library\Core\Database::dbQuery('INSERT INTO ' . static::TABLE_NAME . '(`' . implode('`,`', $aInsertedFields) . '`) VALUES (?' . str_repeat(',?', count($aInsertedValues) - 1) . ')', $aInsertedValues);
            $this->{static::PRIMARY_KEY} = \Library\Core\Database::lastInsertId();
            $this->refresh();
        } catch (PDOException $oException) {
            return false;
        }

        return ($this->bIsLoaded = true);
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
            if (isset($this->{$sFieldName}) && ! is_null($this->{$sFieldName}) && $this->validateDataIntegrity($sFieldName, $this->{$sFieldName})) {
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
            $oStatement = \Library\Core\Database::dbQuery('UPDATE ' . static::TABLE_NAME . ' SET `' . implode('` = ?, `', $aUpdatedFields) . '` = ? WHERE `' . static::PRIMARY_KEY . '` = ?', $aUpdatedValues);
            $this->refresh();
        } catch (\PDOException $oException) {
            return false;
        }

        return ($this->bIsLoaded = true);
    }

    /**
     * Delete row corresponding to current instance in database and reset instance
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
            $oStatement = \Library\Core\Database::dbQuery('DELETE FROM `' . static::TABLE_NAME . '` WHERE `' . static::PRIMARY_KEY . '` = ?', array(
                $this->{static::PRIMARY_KEY}
            ));
            $this->reset();
        } catch (\PDOException $oException) {
            return false;
        }

        return true;
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
     * Check whether object was successfully loaded
     *
     * @return boolean TRUE if object was successfully loaded, otherwise FALSE
     */
    public function isLoaded()
    {
        return $this->bIsLoaded;
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
     * Load the list of fields of the associated database table
     *
     * @throws EntityException
     */
    protected function loadFields()
    {
        $sCacheKey = Cache::getKey(__METHOD__, get_called_class());
        if (($this->aFields = Cache::get($sCacheKey)) === false) {
            if (($oStatement = \Library\Core\Database::dbQuery('SHOW COLUMNS FROM ' . static::TABLE_NAME)) === false) {
                throw new EntityException('Unable to list fields for table ' . static::TABLE_NAME);
            }

            foreach ($oStatement->fetchAll(\PDO::FETCH_ASSOC) as $aColumn) {
                $this->aFields[$aColumn['Field']] = $aColumn;
            }

            Cache::set($sCacheKey, $this->aFields, false, Cache::CACHE_TIME_MINUTE);
        }
    }

    /**
     * Query if an attribute exists
     *
     * @return boolean
     */
    public function hasAttribute($sAttributeName)
    {
        assert('strlen($sAttributeName) > 0');
        return array_key_exists($sAttributeName, $this->aFields);
    }

    /**
     * Get Entity SGBD type from experimental PDO driver
     *
     * @param string $sAttributeName
     * @return NULL string SGBD field type if exists otherwhise NULL
     */
    public function getAttributeType($sAttributeName)
    {
        assert('strlen($sAttributeName) > 0');
        if (strlen($sAttributeName) > 0 && isset($this->aFields[$sAttributeName])) {
            return $this->aFields[$sAttributeName]['Type'];
        }
        return null;
    }

    /**
     * Determine if an Entity attribute can be nullable
     *
     * @param string $sAttributeName
     * @return boolean TRUE if Entity attribute can be null otherwhise FALSE
     */
    public function isNullable($sAttributeName)
    {
        assert('strlen($sAttributeName) > 0');
        if (strlen($sAttributeName) > 0 && isset($this->aFields[$sAttributeName])) {
            return $this->aFields[$sAttributeName]['Null'] !== 'NO';
        }
        return false;
    }

    /**
     * Get Entity attributes
     *
     * @return array A one dimensional array with all Entity attributes
     */
    public function getAttributes()
    {
        return array_keys($this->aFields);
    }

    /**
     * Translate a SGBD field type to PHP types
     *
     * @todo optimiser cette méthode et utiliser un switch
     * @param string $sName
     * @return string null
     * @throws EntityException
     */
    public function getDataType($sName = null)
    {
        assert('$this->getAttributeType($sName) !== null');

        $sDataType = null;
        if (! is_null($sName)) {

            $sDataType = $this->getAttributeType($sName);

            if (preg_match('#(^int|^integer|^tinyint|^smallmint|^mediumint|^tinyint|^bigint)#', $sDataType)) {
                $sDataType = 'integer';
            } elseif (preg_match('#(^float|^decimal|^numeric)#', $this->aFields[$sName]['Type'])) {
                $sDataType = 'float';
            } elseif (preg_match('#(^varchar|^text|^blob|^tinyblob|^tinytext|^mediumblob|^mediumtext|^longblob|^longtext|^date|^datetime|^timestamp)#', $this->aFields[$sName]['Type'])) {
                $sDataType = 'string';
            } elseif (preg_match('#^enum#', $this->aFields[$sName]['Type'])) {
                $sDataType = 'array'; // @todo ajouter un type enum dans validator puis un inArray pour valider
            } else {
                throw new EntityException(__CLASS__ . ' Unsuported database field type: ' . $this->aFields[$sName]['Type']);
            }
        }
        return $sDataType;
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
     * Reset current instance to blank state
     *
     * @todo aucun interet autant re instancier la class appelé
     */
    public function reset()
    {
        $aOriginProperties = array();
        $oReflection = new \ReflectionClass($this);

        foreach ($oReflection->getProperties() as $oRelectionProperty) {
            $aOriginProperties[] = $oRelectionProperty->getName();
        }

        foreach ($this as $sKey => $mValue) {
            if (! in_array($sKey, $aOriginProperties)) {
                unset($this->$sKey);
            }
        }

        $this->bIsLoaded = false;
    }

    /**
     * Validate data integrity for the database field
     *
     * @todo remettre la gestion des exceptions
     *
     * @param string $sFieldName
     * @param
     *            mixed string|int|float $mValue
     * @throws EntityException
     * @return bool
     */
    protected function validateDataIntegrity($sFieldName, $mValue)
    {
        assert('isset($this->aFields[$sFieldName]["Type"])');

        $iValidatorStatus = 0;
        $sDataType = '';

        // @todo prendre en charge les variables nullables à ce niveau en fonctions des infos sur le champs mysql
        // @todo Dépend d'une feature experimentale de PDO attendre la version stable
        if (is_null($mValue) && $this->aFields[$sFieldName]['Null'] === 'YES') {
            return true;
        }

        if (! empty($sFieldName) && ! empty($mValue)) {
            if (($sDataType = $this->getDataType($sFieldName)) !== NULL && method_exists(__NAMESPACE__ . '\\Validator', $sDataType) && ($iValidatorStatus = Validator::$sDataType($mValue)) === Validator::STATUS_OK) {
                return true;
            }
        }
        return false;
    }

    /**
     * List all database tables
     *
     * @todo rendre facilement overidable pour d'autres SGBD que Mysql
     *
     * @return \Library\Core\Collection
     */
    protected function getDatabaseEntities()
    {
        $aDatabaseEntities = array();
        $aConfig = \Bootstrap::getConfig();

        $oStatement = Database::dbQuery('SELECT * FROM INFORMATION_SCHEMA.TABLES WHERE `TABLE_SCHEMA` = ? ORDER BY `TABLES`.`TABLE_SCHEMA` DESC', array(
            $aConfig['database']['name']
        ));
        if ($oStatement !== false && $oStatement->rowCount() > 0) {
            $aDatabaseEntities = $oStatement->fetchAll(\PDO::FETCH_ASSOC);
        }

        return $aDatabaseEntities;
    }


    /**
     * Save history on update for historized objects
     * @param \app\Entities $oOriginalObject          Original object before update
     */
    protected function saveHistory($oOriginalObject)
    {
        $aBefore = array();
        $aAfter = array();

        foreach ($this as $sPropertyName => $mValue) {
            if ($mValue != $oOriginalObject->{$sPropertyName}) {
                $aBefore[$sPropertyName] = $oOriginalObject->{$sPropertyName};
                $aAfter[$sPropertyName] = $mValue;
            }
        }

        $oEntityHistory = new \app\Entities\EntityHistory();
        $oEntityHistory->classe = substr($this->sChildClass, 3);
        $oEntityHistory->idobjet = $this->{static::PRIMARY_KEY};
        $oEntityHistory->avant = json_encode($aBefore);
        $oEntityHistory->apres = json_encode($aAfter);
        $oEntityHistory->date_modif = date('Y-m-d');
        $oEntityHistory->time_modif = date('H:i:s');
        $oEntityHistory->iduser = \model\UserSession::getInstance()->getUserId();
        $oEntityHistory->add();
    }
}

class EntityException extends \Exception
{
}

