<?php
namespace Library\Core\Orm;

use Library\Core\CoreException;
use Library\Core\Collection;
use Library\Core\Scope\BundlesEntitiesScope;

use bundles\user\Entities\User;

/**
 * Search component for Entities
 *
 * @author Nicolas Bonnici <nicolasbonnici@gmail.com>
 *
 */
class EntitySearch
{

    /**
     * Search term
     * @var string
     */
    protected $sSearch;

    /**
     * Current User instance (optional)
     *
     * @var \bundles\user\Entities\User
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

    protected function doSearch($sBundleName, \Library\Core\Orm\Entity $oEntity, array $aConstraints = array())
    {
        assert('empty($this->sSearch) === false');

        $aParameters = array();
        $sEntityCollectionClassName = $oEntity->computeCollectionClassName();

        // Entities must be searchable and have a EntityCollection class too
        if ($oEntity->isSearchable() === false) {
            throw new EntitySearchException(
                sprintf(EntitySearchException::$aErrors[EntitySearchException::ERROR_ENTITY_NOT_ALLOWED], $oEntity ),
                EntitySearchException::ERROR_ENTITY_NOT_ALLOWED
            );
        } elseif (class_exists($sEntityCollectionClassName) === false) {
            throw new EntitySearchException(
                sprintf(EntitySearchException::$aErrors[EntitySearchException::ERROR_ENTITY_COLLECTION_NOT_FOUND], $oEntity ),
                EntitySearchException::ERROR_ENTITY_COLLECTION_NOT_FOUND
            );
        } else {
            $oEntityCollection = new $sEntityCollectionClassName();

            // @todo build query with constraints directly then pass the query to EntityCollection::loadByQuery

            // Generic search
            $aAttributes = $oEntity->getAttributes();
            foreach ($aAttributes as $sKey) {
                if ($oEntity->getDataType($sKey) === EntityAttributes::DATA_TYPE_STRING) {
                    $aParameters[$sKey] = $this->sSearch;
                }
            }

            // @todo use Query component
            // @todo handle last parameters the bStrictMode flag to false (for switch AND => OR | ' = ?' => LIKE %?%)
            $oEntityCollection->loadByParameters(
                $aParameters,
                array(),
                array(0,99),
                false
            );

            // store Entity primary key value (id[entity] value)
            // @todo dirty and quick just for avoid calling methods from view
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
     * @return EntitySearch
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
            } catch (\Library\Core\Orm\EntityException $oException) {
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
     * @return EntitySearch
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

class EntitySearchException extends CoreException
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