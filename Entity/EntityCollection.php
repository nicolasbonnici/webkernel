<?php
namespace Library\Core\Entity;

use Library\Core\Collection;
use Library\Core\Database\Pdo;
use Library\Core\Database\Query\Operators;
use Library\Core\Database\Query\Select;
use Library\Core\Database\Query\Where;
use Library\Core\Exception\CoreException;

/**
 * Handle collection of Entities
 *
 * Class EntityCollection
 * @package Library\Core\Entity
 */
abstract class EntityCollection extends Collection
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
     * @param array $aIds   IDs of the elements to instanciate
     */
    public function __construct($aIds = array())
    {
        $this->sChildClass = $this->computeEntityClassName();
        if (is_array($aIds) && count($aIds) > 0) {
            $this->loadByIds($aIds);
        }
    }

    /**
     * Simple load method with no parameters (/!\ use this method carefully, mostly for Dataset component usage)
     *
     * @param array $aOrder
     * @param mixed array|int $mLimit
     * @throws EntityCollectionException
     * @throws \Exception
     */
    public function load(array $aOrder = array(), $mLimit = null)
    {
        # First reset collection
        $this->reset();

        # Build Select Query
        $oSelect = new Select();
        $oSelect->addColumn('*')
            ->setFrom(constant($this->sChildClass . '::TABLE_NAME'), true);

        # Order fields
        if (empty($aOrder) === false) {
            $oSelect->setOrderBy($aOrder);
        }

        # Limit clause
        if (is_null($mLimit) === false) {
            $oSelect->setLimit($mLimit);
        }

        try {
            $oStatement = Pdo::dbQuery($oSelect->build());
        } catch (\PDOException $oException) {
            throw new EntityCollectionException(
                sprintf(
                    EntityCollectionException::getError(
                        EntityCollectionException::ERROR_UNABLE_TO_LOAD_COLLECTION_WITH_QUERY
                    ),
                    array($this->getChildClass(), $oSelect->build())
                ),
                EntityCollectionException::ERROR_UNABLE_TO_LOAD_COLLECTION_WITH_QUERY
            );
        }
        if ($oStatement !== false) {
            foreach ($oStatement->fetchAll(\PDO::FETCH_ASSOC) as $aObjectData) {
                $oObject = new $this->sChildClass();
                $oObject->loadByData($aObjectData);
                $this->add($oObject, $oObject->getId());
            }
        }
    }

    /**
     * Load collection regarding given IDs
     *
     * @param array $aIds List of IDs
     */
    protected function loadByIds($aIds)
    {
        assert('!empty($aIds)');

        # Reset instance
        $this->reset();

        # Set origin ids
        $this->aOriginIds = $aIds;

        $aUncachedObjects = array();
        $aCachedObjects = $this->getCachedObjects($this->aOriginIds);

        if (count($aCachedObjects) === 0) {
            $aUncachedObjects = array_values($this->aOriginIds);
        } else {
            foreach ($aCachedObjects as $iObjectId => $aCachedObject) {
                $oObject = new $this->sChildClass();
                $oObject->loadByData($aCachedObject);
                $this->add($oObject, $oObject->getId());
            }
        }

        $oSelect = new Select();
        $oSelect->addColumn('*')
            ->setFrom(constant($this->sChildClass . '::TABLE_NAME'), true)
            ->addWhereCondition(Operators::in(constant($this->sChildClass . '::PRIMARY_KEY'), $this->aOriginIds));

        if (empty($aUncachedObjects) === false) {
            $this->loadByQuery($oSelect->build(), $aUncachedObjects);
        }

        uksort($this->aElements, array(
            $this,
            'sortElementsById'
        ));
    }

    /**
     * Load collection with provided parameters and options
     *
     * @param array $aParameters            List of parameters name/value
     * @param array $aOrderFields           List of order fields/direction
     * @param mixed array|int $mLimit       Start / End limit request for pagination or just limit with an integer
     * @param bool $bStrictMode             AND|OR operator switch and =|LIKE %someValue%
     * @throws EntityException
     */
    public function loadByParameters(
        array $aParameters,
        array $aOrderFields = array(),
        $mLimit = null,
        $bStrictMode = true
    )
    {
        # Reset instance
        $this->reset();

        if (empty($aParameters)) {
            throw new EntityCollectionException(
                sprintf(
                    EntityCollectionException::getError(EntityCollectionException::ERROR_UNABLE_TO_LOAD_WITHOUT_PARAMETERS),
                    $this->getChildClass()
                ),
                EntityCollectionException::ERROR_UNABLE_TO_LOAD_WITHOUT_PARAMETERS
            );
        }

        # Select Query
        $oSelect = new Select();
        $oSelect->addColumn('*')
            ->setFrom(constant($this->sChildClass . '::TABLE_NAME'), true);
        $aBindedValues = array();

        foreach ($aParameters as $sParameterName => $mParameterValue) {

            if (is_array($mParameterValue) && count($mParameterValue) > 0) {
                # Add Where condition
                $oSelect->addWhereCondition(
                    Operators::in($sParameterName, $mParameterValue),
                    (($bStrictMode === true) ? Where::QUERY_WHERE_CONNECTOR_AND : Where::QUERY_WHERE_CONNECTOR_OR)
                );

                $aBindedValues = array_merge($aBindedValues, $mParameterValue);
            } elseif(
                is_string($mParameterValue) === true ||
                is_int($mParameterValue) === true ||
                is_bool($mParameterValue) === true ||
                is_null($mParameterValue) === true
            ) {

                # Add Where condition
                $oSelect->addWhereCondition(
                    (($bStrictMode === true)
                        ? Operators::equal($sParameterName, false)
                        : Operators::like($sParameterName, $mParameterValue)
                    ),
                    (($bStrictMode === true) ? Where::QUERY_WHERE_CONNECTOR_AND : Where::QUERY_WHERE_CONNECTOR_OR)
                );

                $aBindedValues[] = (($bStrictMode === true) ? $mParameterValue : '%' . $mParameterValue . '%');
            } else {
                throw new EntityCollectionException(
                    sprintf(
                        EntityCollectionException::getError(EntityCollectionException::ERROR_BAD_BOUNDED_PARAMETER_VALUE),
                        $sParameterName
                    ),
                    EntityCollectionException::ERROR_BAD_BOUNDED_PARAMETER_VALUE
                );
            }
        }

        if (is_null($mLimit) === true) {
            $oSelect->setLimit(array(0,10));
        } else {
            $oSelect->setLimit($mLimit);
        }

        if (empty($aOrderFields) === true) {
            $oSelect->setOrderBy(array(constant($this->sChildClass . '::PRIMARY_KEY')));
        } else {
            $oSelect->setOrderBy($aOrderFields);
        }

        $this->loadByQuery($oSelect->build(), $aBindedValues);
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
            $oStatement = Pdo::dbQuery($sQuery, $aValues);
        } catch (\PDOException $oException) {
            throw new EntityException(
                sprintf(
                    EntityCollectionException::getError(EntityCollectionException::ERROR_UNABLE_TO_LOAD_COLLECTION_WITH_QUERY_AND_PARAMETERS),
                    array($sQuery, var_export($aValues, true))
                ),
                EntityCollectionException::ERROR_UNABLE_TO_LOAD_COLLECTION_WITH_QUERY_AND_PARAMETERS
            );
        }

        if ($oStatement !== false) {
            foreach ($oStatement->fetchAll(\PDO::FETCH_ASSOC) as $aObjectData) {
                $oObject = new $this->sChildClass();
                $oObject->loadByData($aObjectData);
                $this->add($oObject, $oObject->getId());
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
     * @param int|string $mKey
     * @param int|string $mValue
     * @return Entity
     */
    public function search($mKey, $mValue)
    {
        foreach ($this->aElements as $iCollectionIndex => $oEntity) {
            if (isset($oEntity->$mKey) && $oEntity->$mKey == $mValue) {
                return $oEntity;
            }
        }
        return null;
    }

    /**
     * Filter Entity Collection with parameters
     *
     * @param array $aParameters
     */
    public function filter(array $aParameters)
    {
        foreach ($this->aElements as $iCollectionIndex => $oEntity) {
            $bMatch = false;
            foreach($aParameters as $sKey => $mValue) {
                if (isset($oEntity->$sKey) && $oEntity->$sKey == $mValue) {
                    $bMatch = true;
                }
            }

            if ($bMatch === false) {
                $this->delete($iCollectionIndex);
            }

        }
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

    /**
     * Compute the child Entity instance class name
     * @return string
     */
    public function computeEntityClassName()
    {
        return str_replace(array(
            '\Collection',
            'Collection'
        ), '', get_called_class());
    }

    /**
     * Child class accessor
     * @return string
     */
    public function getChildClass()
    {
        return $this->sChildClass;
    }
}

class EntityCollectionException extends CoreException
{
    const ERROR_UNABLE_TO_LOAD_COLLECTION_WITH_QUERY                = 2;
    const ERROR_UNABLE_TO_LOAD_COLLECTION_WITH_QUERY_AND_PARAMETERS = 3;
    const ERROR_UNABLE_TO_LOAD_WITHOUT_PARAMETERS                   = 4;
    const ERROR_BAD_BOUNDED_PARAMETER_VALUE                         = 5;

    public static $aErrors = array(
        self::ERROR_UNABLE_TO_LOAD_COLLECTION_WITH_QUERY => 'Unable to load collection of "%s" with query "%s".',
        self::ERROR_UNABLE_TO_LOAD_COLLECTION_WITH_QUERY_AND_PARAMETERS => 'Unable to load collection of "%s" with query "%s" and parameters: %s.',
        self::ERROR_UNABLE_TO_LOAD_WITHOUT_PARAMETERS    => 'No parameter provided for loading collection of type "%s".',
        self::ERROR_BAD_BOUNDED_PARAMETER_VALUE          => 'Bad bounded parameter value for : %s',
    );
}
