<?php
namespace Library\Core\Entity;

use Library\Core\Database\Pdo;
use Library\Core\Database\Query\Operators;
use Library\Core\Database\Query\QueryAbstract;
use Library\Core\Database\Query\Select;
use Library\Core\Database\Query\Where;
use Library\Core\Exception\CoreException;
use Library\Core\Collection\Collection;
use Library\Core\Scope\BundlesEntitiesScope;

use app\Entities\User;

/**
 * Search component for Entities
 *
 * @author Nicolas Bonnici <nicolasbonnici@gmail.com>
 *
 */
class Search
{

    /**
     * Search term
     * @var string
     */
    protected $sSearch;

    /**
     * Current User instance (optional)
     *
     * @var \app\Entities\User
     */
    protected $oUser;

    /**
     * Bundles scope to perform search on
     * @var \Library\Core\Scope\BundlesEntitiesScope
     */
    protected $oScope;


    /**
     * Search results
     * @var array
     */
    protected $aResults = array();

    /**
     * Instance constructor
     *
     * @return Collection
     */
    public function __construct()
    {
    }

    /**
     * Process the search
     *
     * @return Collection
     */
    public function process()
    {
        $oExceptions = new Collection();
        foreach ($this->oScope->getScope() as $sBundleName => $aEntities) {
            if (is_array($aEntities) === true) {
                foreach ($aEntities as $oEntity) {
                    try {
                        $this->doSearch($sBundleName, $oEntity);
                    } catch (\Exception $oException) {
                        $oExceptions->add($oException);
                        continue;
                    }
                }
            }
        }

        return $oExceptions;
    }

    /**
     * Perform a search on a given entity
     *
     * @param Entity $oEntity
     * @param string $sSearch
     * @throws SearchException
     */

    // @todo trier les resulats sur 3 dimensions bundle => entity => results
    // @todo Entity instance on parameter

    protected function doSearch($sBundleName, \Library\Core\Entity\Entity $oEntity, array $aConstraints = array())
    {
        assert('empty($this->sSearch) === false');

        $sEntityCollectionClassName = $oEntity->computeCollectionClassName();

        // Entities must be searchable and have a EntityCollection class too
        if ($oEntity->isSearchable() === false) {
            throw new SearchException(
                sprintf(SearchException::$aErrors[SearchException::ERROR_ENTITY_NOT_ALLOWED], $oEntity ),
                SearchException::ERROR_ENTITY_NOT_ALLOWED
            );
        } elseif (class_exists($sEntityCollectionClassName) === false) {
            throw new SearchException(
                sprintf(SearchException::$aErrors[SearchException::ERROR_ENTITY_COLLECTION_NOT_FOUND], $oEntity ),
                SearchException::ERROR_ENTITY_COLLECTION_NOT_FOUND
            );
        } else {
            /** @var EntityCollection $oEntityCollection */
            $oEntityCollection = new $sEntityCollectionClassName();

            // Generic search
            $aAttributes = $oEntity->getAttributes();
            $aWhere = array();
            $aBindedValues = array();
            foreach ($aAttributes as $sKey) {
                $aWhere[Operators::like($sKey, $this->getSearch())] = Where::QUERY_WHERE_CONNECTOR_OR;
                $aBindedValues[] = $this->getSearch();
            }

            $oSelect = new Select();
            $oSelect->setFrom($oEntity->getTableName(), true)
                ->addColumns($aAttributes)
                ->setLimit(array(0, 99))
                ->setOrderBy(array('created'))
                ->addWhereConditions($aWhere);

            $oStatement = Pdo::dbQuery($oSelect->build(), $aBindedValues);

            if ($oStatement !== false) {
                foreach ($oStatement->fetchAll(\PDO::FETCH_ASSOC) as $aResult) {
                    $oFoundEntity = clone $oEntity;
                    $oFoundEntity->loadByData($aResult);
                    if ($oFoundEntity->isLoaded() === true) {
                        $oEntityCollection->add($oFoundEntity, $oFoundEntity->getId());
                    }
                }

            }

            // store Entity primary key value (id[entity] value)
            foreach ($oEntityCollection as $oEntity) {
                $oEntity->pk = $oEntity->getId();
            }

            /** @var EntityCollection $oEntityCollection */
            if (($iEntitiesCount = $oEntityCollection->count()) > 0) {
                $this->aResults[$sBundleName][$oEntity->getEntityName()] = $oEntityCollection;
                $this->aResults[$sBundleName][$oEntity->getEntityName()]->count = $iEntitiesCount;
            }

        }

    }

    /**
     * Set the bundles scope to restrict search
     *
     * @param \Library\Core\Scope\BundlesEntitiesScope $oBundleScope
     * @return Search
     */
    public function setScope(BundlesEntitiesScope $oBundleScope)
    {
        $this->oScope = $oBundleScope;
        return $this;
    }

    /**
     * Set instance User
     * @param mixed int|User $mUser
     */
    public function setUser($mUser)
    {
        if ($mUser instanceof User && $mUser->isLoaded()) {
            $this->oUser = clone $mUser;
        } elseif (is_int($mUser) && intval($mUser) > 0) {
            try {
                $this->oUser = new User($mUser);
            } catch (\Library\Core\Entity\EntityException $oException) {
                $this->oUser = null;
            }
        } else {
            $this->oUser = null;
        }
    }

    /**
     * Set search term
     *
     * @param $sSearch
     * @return Search
     */
    public function setSearch($sSearch)
    {
        $this->sSearch = $sSearch;
        return $this;
    }

    /**
     * Searched term accessor
     * @return string
     */
    public function getSearch()
    {
        return $this->sSearch;
    }

    /**
     * Retrieve search results
     *
     * @return array
     */
    public function getResults()
    {
        return $this->aResults;
    }
}

class SearchException extends CoreException
{

    /**
     * Errors codes
     *
     * @var integer
     */
    const ERROR_UNAUTHORIZED_REQUEST        = 2;
    const ERROR_EMPTY_SEARCH_REQUEST        = 3;
    const ERROR_EMPTY_SCOPE                 = 4;
    const ERROR_ENTITY_NOT_ALLOWED          = 5;
    const ERROR_ENTITY_COLLECTION_NOT_FOUND = 6;

    public static $aErrors = array(
        self::ERROR_UNAUTHORIZED_REQUEST        => 'Unauthorized search request',
        self::ERROR_EMPTY_SEARCH_REQUEST        => 'No or empty search term',
        self::ERROR_EMPTY_SCOPE                 => 'No scope provided for search',
        self::ERROR_ENTITY_NOT_ALLOWED          => 'Search not allowed for this Entity: %s',
        self::ERROR_ENTITY_COLLECTION_NOT_FOUND => 'Collection for Entity %s not found'
    );
}