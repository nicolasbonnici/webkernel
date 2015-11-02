<?php
namespace Library\Core\Entity;

use Library\Core\Cache\Drivers\Memcache;
use Library\Core\Database\Pdo;
use Library\Core\Validator;

/**
 * Entities attributes generic abstract
 *
 * Class Attributes
 * @package Library\Core\Orm
 */
abstract class Attributes {

    /**
     * Entity attribute's types
     * @var string
     */
    const DATA_TYPE_STRING   = 'string';
    const DATA_TYPE_INTEGER  = 'integer';
    const DATA_TYPE_FLOAT    = 'string'; # Issue with PDO handle float as string to avoid truncated data
    const DATA_TYPE_DATETIME = 'datetime';

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
                $this->aFields[$aColumn['Field']] = $aColumn;
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
    private function getAttributeType($sAttributeName)
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
     * @param string $sName
     * @return string
     * @throws EntityException
     */
    public function getDataType($sName = null)
    {
        assert('$this->getAttributeType($sName) !== null');

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
                case (preg_match('/(date|datetime)/', $sDataType) === 1) :
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


    /**
     * Validate data integrity for the database field
     *
     * @param string $sFieldName
     * @param mixed string|int|float $mValue
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

                # Auto cast Datetime before validation
                if ($sDataType === self::DATA_TYPE_DATETIME) {
                    $mValue = new \DateTime($mValue);
                }

                $iValidatorStatus = Validator::$sDataType($mValue);
                if ($iValidatorStatus === Validator::STATUS_OK) {
                    return true;
                }
            }
            return false;
        }

    }

}