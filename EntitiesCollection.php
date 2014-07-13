<?php
namespace Library\Core;

/**
 * On the fly ORM CRUD managment abstract class
 *
 * @author Antoine <antoine.preveaux@bazarchic.com>
 * @author niko <nicolasbonnici@gmail.com>
 *
 * @important Entities need a primary auto incremented index (id[entity])
 *
 *       @dependancy \Library\Core\Collection
 *       @dependancy \Library\Core\Validator
 *       @dependancy \Library\Core\Cache
 *       @dependancy \Library\Core\Database
 *
 */
abstract class EntitiesCollection extends Collection
{

    /**
     * Collection objects class name
     *
     * @var string
     */
    protected $sChildClass;

    /**
     * IDs of elements to add to collection
     *
     * @var array
     */
    protected $aOriginIds;

    /**
     * Constructor
     *
     * @param string $sEntity
     *            the tiy name
     * @param array $aIds
     *            IDs of the elements to instanciate
     */
    public function __construct($aIds = array())
    {
        $this->sChildClass = str_replace(array(
            '\Collection',
            'Collection'
        ), '', get_called_class());
        if (is_array($aIds) && count($aIds) > 0) {
            $this->loadByIds($aIds);
        }

        return;
    }

    /**
     * Simple methode de load
     *
     * @param string $sOrderBy
     *            database field name
     * @param string $sOrder
     *            DESC|ASC
     * @param array $aLimit
     * @throws EntityException
     */
    public function load($sOrderBy = '', $sOrder = 'DESC', array $aLimit = array(0,50))
    {

        if (empty($sOrderBy)) {
            $sOrderBy = constant($this->sChildClass . '::PRIMARY_KEY');
        }

        if (! in_array($sOrder, array(
            'ASC',
            'DESC'
        ))) {
            $sOrder = 'DESC';
        }

        $sQuery = 'SELECT *
        FROM `' . constant($this->sChildClass . '::TABLE_NAME') . '`
        ORDER BY ' . $sOrderBy . ' ' . $sOrder . ' LIMIT ' . $aLimit[0] . ',' . $aLimit[1];
        try {
            $oStatement = Database::dbQuery($sQuery);
        } catch (\PDOException $oException) {
            throw new EntityException('Unable to load collection of ' . $this->sChildClass . ' with query "' . $sQuery . '" ');
        }
        if ($oStatement !== false) {
            foreach ($oStatement->fetchAll(\PDO::FETCH_ASSOC) as $aObjectData) {
                $oObject = new $this->sChildClass();
                $oObject->loadByData($aObjectData);
                $this->add($oObject->getId(), $oObject);
            }
        }
    }

    /**
     * Load collection regarding given IDs
     *
     * @todo bug voir avec les test unitaires
     *
     * @param array $aIds
     *            List of IDs
     */
    public function loadByIds($aIds)
    {
        assert('!empty($aIds)');

        if (is_null(constant($this->sChildClass . '::TABLE_NAME'))) {
            throw new EntityException('CoreObject class table name not defined for class ' . $this->sChildClass);
        }

        if (is_null(constant($this->sChildClass . '::PRIMARY_KEY'))) {
            throw new EntityException('CoreObject class primary key not defined for class ' . $this->sChildClass);
        }

        $this->aOriginIds = $aIds;
        if (($aCachedObjects = $this->getCachedObjects($aIds)) === false) {
            $aUncachedObjects = array_values($aIds);
        }

        foreach ($aCachedObjects as $iObjectId) {
            $oObject = new $this->sChildClass($iObjectId);
            $this->add($oObject->getId(), $oObject);
        }

        if (! empty($aUncachedObjects)) {
            $this->loadByQuery('
                SELECT *
                FROM `' . constant($this->sChildClass . '::TABLE_NAME') . '`
                WHERE `' . constant($this->sChildClass . '::PRIMARY_KEY') . '` IN(?' . str_repeat(', ?', count($aUncachedObjects) - 1) . ')', $aUncachedObjects);
        }

        uksort($this->aElements, array(
            $this,
            'sortElementsById'
        ));
    }

    /**
     * Load collection regarding values and ordering parameters
     *
     * @param array $aParameters
     *            List of parameters name/value
     * @param array $aOrderFields
     *            List of order fields/direction
     * @param array $aLimit
     *            Start / End limit request for pagination
     * @param bool $bStrictMode
     *            AND|OR operator switch and =|LIKE %%
     * @throws EntityException
     */
    public function loadByParameters(array $aParameters, array $aOrderFields = array(), array $aLimit = array(0,10), $bStrictMode = true)
    {
        assert('is_int($aLimit[0]) && is_int($aLimit[1])');

        if (empty($aParameters)) {
            throw new EntitiesCollectionException('No parameter provided for loading collection of type ' . $this->sChildClass);
        }

        $sWhere = '';
        $aBindedValues = array();

        foreach ($aParameters as $sParameterName => $mParameterValue) {
            if (! empty($sWhere)) {
                $sWhere .= ' ' . (($bStrictMode === true) ? 'AND' : 'OR') . ' ';
            }
            // Enable using LOWER(), UPPER(), ...
            if (strpos($sParameterName, '(') === false) {
                $sWhere .= '`' . $sParameterName . '`';
            } else {
                $sWhere .= $sParameterName;
            }

            if (is_array($mParameterValue)) {
                $sWhere .= ' IN(?' . str_repeat(', ?', count($mParameterValue) - 1) . ')';
                $aBindedValues = array_merge($aBindedValues, $mParameterValue);
            } else {
                $sWhere .= ' ' . (($bStrictMode === true) ? '= ?' : 'LIKE ?');
                $aBindedValues[] = (($bStrictMode === true) ? $mParameterValue : '%' . $mParameterValue . '%');
            }
        }

        $sQuery = '
            SELECT *
            FROM `' . constant($this->sChildClass . '::TABLE_NAME') . '`
            WHERE ' . $sWhere . '
            ORDER BY ';

        if (empty($aOrderFields)) {
            $sQuery .= '`' . constant($this->sChildClass . '::PRIMARY_KEY') . '` DESC';
        } else {
            foreach ($aOrderFields as $sFieldName => $sOrder) {
                if (strpos($sFieldName, '(') === false) {
                    $sQuery .= '`' . $sFieldName . '` ' . $sOrder . ', ';
                } else {
                    $sQuery .= $sFieldName . ' ' . $sOrder . ', ';
                }
            }
            $sQuery = trim($sQuery, ', ');
        }

        if (is_int($aLimit[0]) && is_int($aLimit[1])) {
            $sQuery .= ' LIMIT ' . $aLimit[0] . ', ' . $aLimit[1];
        }
        $this->loadByQuery($sQuery, $aBindedValues);
    }

    /**
     * Load collection regarding given Database query and values
     *
     * @param string $sQuery
     *            Database query
     * @param array $aValues
     *            Values of paramters
     * @throws EntityException
     */
    public function loadByQuery($sQuery, array $aValues = array())
    {
        try {
            $oStatement = Database::dbQuery($sQuery, $aValues);
        } catch (\PDOException $oException) {
            throw new EntityException('Unable to load collection of ' . $this->sChildClass . ' with query "' . $sQuery . '" and values ' . print_r($aValues, true));
        }

        if ($oStatement !== false) {
            foreach ($oStatement->fetchAll(\PDO::FETCH_ASSOC) as $aObjectData) {
                $oObject = new $this->sChildClass();
                $oObject->loadByData($aObjectData);
                $this->add($oObject->getId(), $oObject);
            }
        }
    }

    /**
     * Retrieve the cached instances of objects
     *
     * @param array $aIds
     *            IDs of cached objects to get
     * @return array Instances of cached objects
     */
    protected function getCachedObjects($aIds)
    {
        $aCachedObjects = array();
        foreach ($aIds as $iId) {
            if (($aCachedObject = call_user_func(array(
                $this->sChildClass,
                'getCached'
            ), $iId)) !== false) {
                $aCachedObjects[$iId] = $aCachedObject;
            }
        }
        return $aCachedObjects;
    }

    /**
     * Sort collection elements according to collection call order
     *
     * @param integer $iFirstKey
     *            First element key
     * @param integer $iSecondKey
     *            Second element key
     * @return integer 1 if first element is after second element, otherwise -1
     */
    protected function sortElements($iFirstKey, $iSecondKey)
    {
        $aKeys = array_flip($this->aOriginIds);
        return ($aKeys[$iFirstKey] > $aKeys[$iSecondKey]) ? 1 : - 1;
    }

    /**
     * Search within the collection
     *
     * @todo ajouter la gestion des filtre pour obtenir des sous collection avec cette methode
     * @todo migrer cette methode vers entitiesCollection!!
     *
     * @param int|string $mKey
     * @param int|string $mValue
     * @return object mixed NULL NULL
     */
    public function search($mKey, $mValue)
    {
        foreach ($this->aElements as $iCollectionIndex => $oEntity) {
            if (isset($oEntity->$mKey) && $oEntity->$mKey === $mValue) {
                return $oEntity;
            }
        }
        return NULL;
    }

    /**
     * Sort collection elements according to collection call order
     *
     * @param integer $iFirstKey
     *            First element key
     * @param integer $iSecondKey
     *            Second element key
     * @return integer 1 if first element is after second element, otherwise -1
     */
    protected function sortElementsById($iFirstKey, $iSecondKey)
    {
        $aKeys = array_flip($this->aOriginIds);
        return ($aKeys[$iFirstKey] > $aKeys[$iSecondKey]) ? 1 : - 1;
    }
}

class EntitiesCollectionException extends \Exception
{
}
