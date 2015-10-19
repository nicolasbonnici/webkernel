<?php
namespace Library\Core\Orm;

use Library\Core\Cache;
use Library\Core\Database\Pdo;
use Library\Core\Validator;

/**
 * Entities attributes generic abstract
 *
 * @todo implement Query
 *
 * Class EntityAttributes
 * @package Library\Core\Orm
 */
abstract class EntityAttributes {

    /**
     * Entity attribute's types
     * @var string
     */
    const DATA_TYPE_STRING  = 'string';
    const DATA_TYPE_INTEGER = 'integer';
    const DATA_TYPE_FLOAT   = 'float';
    const DATA_TYPE_ARRAY   = 'array';

    /**
     * Load the list of fields of the associated database table
     *
     * @throws EntityException
     */
    protected function loadAttributes()
    {
        $sCacheKey = Cache::getKey(__METHOD__, get_called_class());
        if (($this->aFields = Cache::get($sCacheKey)) === false) {
            if (($oStatement = Pdo::dbQuery('SHOW COLUMNS FROM `' . static::TABLE_NAME . '`')) === false) {
                throw new EntityException('Unable to list fields for table ' . static::TABLE_NAME);
            }
            foreach ($oStatement->fetchAll(\PDO::FETCH_ASSOC) as $aColumn) {
                $this->aFields[$aColumn['Field']] = $aColumn;
            }

            Cache::set($sCacheKey, $this->aFields, false, Cache::CACHE_TIME_MINUTE);
        }

        if (empty($this->aFields) === true) {
            throw new EntityException('No fields found for table ' . static::TABLE_NAME . ' please check Entity.');
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
    private function getAttributeType($sAttributeName)
    {
        assert('strlen($sAttributeName) > 0');
        if (strlen($sAttributeName) > 0 && isset($this->aFields[$sAttributeName])) {
            return $this->aFields[$sAttributeName]['Type'];
        }
        return null;
    }

    /**
     * @todo get attribute size information too
     */

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
     * @param string $sName
     * @return string
     * @throws EntityException
     */
    public function getDataType($sName = null)
    {
        assert('$this->getAttributeType($sName) !== null');

        // @todo dynamic regexp with implode('|', $aStringDatabaseTypes) ...

        $sDataType = '';
        if (! is_null($sName)) {
            $sDatabaseType = $this->getAttributeType($sName);
            switch ($sDatabaseType) {
                case (preg_match('/^(int|integer|tinyint|smallint|mediumint|tinyint|bigint)/', $sDatabaseType) === 1) :
                    $sDataType = self::DATA_TYPE_INTEGER;
                    break;
                case (preg_match(
                        '/^(varchar|text|blob|tinyblob|tinytext|mediumblob|mediumtext|longblob|longtext|date|datetime)/',
                        $sDatabaseType
                    ) === 1) :
                    $sDataType = self::DATA_TYPE_STRING;
                    break;
                case (preg_match('/^(float|decimal|numeric)/', $sDatabaseType) === 1) :
                    $sDataType = self::DATA_TYPE_FLOAT;
                    break;
                case (preg_match('/(enum|list)/', $sDataType) === 1) :
                    $sDataType = self::DATA_TYPE_ARRAY;
                    break;
                default:
                    throw new EntityException(
                        __CLASS__ . ' Unsupported database field type: ' . $sDatabaseType);
                    break;
            }

        }
        return $sDataType;
    }


    /**
     * Validate data integrity for the database field
     *
     * @param string $sFieldName
     * @param
     *            mixed string|int|float $mValue
     * @throws EntityException
     * @return bool
     */
    protected function validate($sFieldName, $mValue)
    {
        assert('isset($this->aFields[$sFieldName]["Type"])');
        $iValidatorStatus = 0;
        $sDataType = '';

        // If nullable
        if (is_null($mValue) === true && $this->isNullable($sFieldName) === true) {
            return true;
        }

        $sDataType = $this->getDataType($sFieldName);
        $oValidator = new Validator();
        if (is_null($sDataType) === true || method_exists($oValidator , $sDataType) === false) {
            throw new EntityException('Attribute data type not support: ' . $sDataType);
        } else {
            if (! empty($sFieldName) && ! empty($mValue)) {
                $iValidatorStatus = Validator::$sDataType($mValue);
                if ($iValidatorStatus === Validator::STATUS_OK) {
                    return true;
                }
            }
            return false;
        }

    }

}