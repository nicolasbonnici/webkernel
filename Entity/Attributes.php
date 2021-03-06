<?php
namespace Library\Core\Entity;

use Library\Core\Cache\Drivers\Memcache;
use Library\Core\Database\Pdo;

/**
 * Entities attributes generic abstract
 *
 * Class Attributes
 * @package Library\Core\Orm
 */
abstract class Attributes extends Crud
{
    /**
     * PDO field information keys
     * @var string
     */
    const KEY_FIELD_NAME        = 'Field';
    const KEY_FIELD_TYPE        = 'Type';
    const KEY_FIELD_NULLABLE    = 'Null';
    const KEY_FIELD_DEFAULT     = 'Default';
    const KEY_FIELD_EXTRA       = 'Extra';

    /**
     * Entity attribute's data types
     * @var string
     */
    const DATA_TYPE_STRING   = 'string';
    const DATA_TYPE_INTEGER  = 'integer';
    const DATA_TYPE_FLOAT    = 'string'; # Issue with PDO handle float as string to avoid truncated data
    const DATA_TYPE_DATETIME = 'datetime';

    /**
     * Regular expressions to detect the SGBD field type
     */
    const REGEXP_DETECT_STRING   = '/^(varchar|text|blob|tinyblob|tinytext|mediumblob|mediumtext|longblob|longtext)/';
    const REGEXP_DETECT_INTEGER  = '/^(int|integer|tinyint|smallint|mediumint|tinyint|bigint)/';
    const REGEXP_DETECT_FLOAT    = '/^(float|decimal|numeric)/';
    const REGEXP_DETECT_DATETIME = '/^(date|datetime)/';

    /**
     * Load the list of fields of the associated database table
     *
     * @throws EntityException
     */
    protected function loadAttributes()
    {
        $sCacheKey = Memcache::getKey(__METHOD__, get_called_class());
        if (($this->aFields = Memcache::get($sCacheKey)) === false) {
            if (($oStatement = Pdo::dbQuery('SHOW COLUMNS FROM `' . $this->getTableName() . '`')) === false) {
                throw new EntityException('Unable to list fields for table ' . $this->getTableName());
            }
            foreach ($oStatement->fetchAll(\PDO::FETCH_ASSOC) as $aColumn) {
                $this->aFields[$aColumn[self::KEY_FIELD_NAME]] = $aColumn;
            }

            /**
             * @todo utiliser la cache duration de la configuration de l entité pour la mise en cache
             */

            Memcache::set($sCacheKey, $this->aFields, Memcache::CACHE_TIME_MINUTE);
        }

        if (empty($this->aFields) === true) {
            throw new EntityException('No fields found for table ' . $this->getTableName() . ' please check Entity.');
        }

    }

    /**
     * QueryAbstract if an attribute exists
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
    public function getDatabaseType($sAttributeName)
    {
        assert('strlen($sAttributeName) > 0');
        if (strlen($sAttributeName) > 0 && isset($this->aFields[$sAttributeName])) {
            return $this->aFields[$sAttributeName][self::KEY_FIELD_TYPE];
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
            return $this->aFields[$sAttributeName][self::KEY_FIELD_NULLABLE] !== 'NO';
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
     * @param string $sName
     * @return string
     * @throws EntityException
     */
    public function getDataType($sName = null)
    {
        assert('$this->getDatabaseType($sName) !== null');

        $sDataType = '';
        if (! is_null($sName)) {
            $sDatabaseType = $this->getDatabaseType($sName);
            switch ($sDatabaseType) {
                case (preg_match(self::REGEXP_DETECT_INTEGER, $sDatabaseType) === 1) :
                    $sDataType = self::DATA_TYPE_INTEGER;
                    break;
                case (preg_match(self::REGEXP_DETECT_STRING, $sDatabaseType) === 1) :
                    $sDataType = self::DATA_TYPE_STRING;
                    break;
                case (preg_match(self::REGEXP_DETECT_FLOAT, $sDatabaseType) === 1) :
                    $sDataType = self::DATA_TYPE_FLOAT;
                    break;
                case (preg_match(self::REGEXP_DETECT_DATETIME, $sDatabaseType) === 1) :
                    $sDataType = self::DATA_TYPE_DATETIME;
                    break;
                default:
                    throw new EntityException(
                        __CLASS__ . ' Unsupported database field type: ' . $sDatabaseType);
                    break;
            }

        }
        return $sDataType;
    }

}