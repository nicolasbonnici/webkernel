<?php
namespace Library\Core\Orm;

/**
 *
 * @todo code review
 *
 * User: niko
 * Date: 11/06/15
 * Time: 01:49
 */

use Library\Core\Database;

class EntityAttributes extends Database {

    /**
     * Entity attribute's types
     * @var string
     */
    const DATA_TYPE_STRING  = 'string';
    const DATA_TYPE_INTEGER = 'integer';
    const DATA_TYPE_FLOAT   = 'float';
    const DATA_TYPE_ENUM    = 'array';

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
                $sDataType = self::DATA_TYPE_INTEGER;
            } elseif (preg_match('#(^float|^decimal|^numeric)#', $this->aFields[$sName]['Type'])) {
                $sDataType = self::DATA_TYPE_FLOAT;
            } elseif (preg_match('#(^varchar|^text|^blob|^tinyblob|^tinytext|^mediumblob|^mediumtext|^longblob|^longtext|^date|^datetime|^timestamp)#', $this->aFields[$sName]['Type'])) {
                $sDataType = self::DATA_TYPE_STRING;
            } elseif (preg_match('#^enum#', $this->aFields[$sName]['Type'])) {
                $sDataType = self::DATA_TYPE_ENUM; // @todo ajouter un type enum dans validator puis un inArray pour valider
            } else {
                throw new EntityException(__CLASS__ . ' Unsuported database field type: ' . $this->aFields[$sName]['Type']);
            }
        }
        return $sDataType;
    }


    /**
     * Validate data integrity for the database field
     *
     * @todo renommer validate()
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

        // If nullable
        if (is_null($mValue) && $this->isNullable($sFieldName)) {
            return true;
        }

        if (! empty($sFieldName) && ! empty($mValue)) {
            if (($sDataType = $this->getDataType($sFieldName)) !== NULL && method_exists(__NAMESPACE__ . '\\Validator', $sDataType) && ($iValidatorStatus = Validator::$sDataType($mValue)) === Validator::STATUS_OK) {
                return true;
            }
        }
        return false;
    }

}