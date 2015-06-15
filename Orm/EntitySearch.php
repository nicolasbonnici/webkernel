<?php
namespace Library\Core\Orm;

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
     * Errors codes
     *
     * @var integer
     */
    const ERROR_UNAUTHORIZED_REQUEST        = 2;
    const ERROR_EMPTY_SEARCH_REQUEST        = 3;
    const ERROR_EMPTY_SCOPE                 = 4;
    const ERROR_ENTITY_NOT_ALLOWED          = 5;
    const ERROR_ENTITY_COLLECTION_NOT_FOUND = 6;

    protected static $aErrors = array(
        self::ERROR_UNAUTHORIZED_REQUEST => 'Unauthorized search request',
        self::ERROR_EMPTY_SEARCH_REQUEST => 'No or empty search term',
        self::ERROR_EMPTY_SCOPE          => 'No scope provided for search',
        self::ERROR_ENTITY_NOT_ALLOWED   => 'Search not allowed for this Entity: %s'
    );

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
     * @var \Library\Core\Scope\Bundles
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

        foreach ($this->oScope as $sBundleName => $oBundleEntitiesScope) {
            // @otodo passer directement les entités dans le scope
            foreach ($oBundleEntitiesScope as $oEntity => $oConstraint) {

                try {
                    $this->doSearch($oEntity, $oConstraint);
                } catch (\Exception $oException) {
                    $oExceptions->add(($oExceptions->count() + 1), $oException);
                    continue;
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

    protected function doSearch($oEntity, array $aConstraints = array())
    {
        assert('empty($this->sSearch) === false');

        $aParameters = array();
        $sEntityCollectionClassName = $oEntity->computeCollectionClassName();

        // Entities must be searchable and have a EntityCollection class too
        if ($oEntity->isSearchable() === false) {
            throw new EntitySearchException(
                sprintf(self::$aErrors[self::ERROR_ENTITY_NOT_ALLOWED], $oEntity ),
                self::ERROR_ENTITY_NOT_ALLOWED
            );
        } elseif (class_exists($sEntityCollectionClassName) === false) {
            throw new EntitySearchException(
                sprintf(self::$aErrors[self::ERROR_ENTITY_COLLECTION_NOT_FOUND], $oEntity ),
                self::ERROR_ENTITY_COLLECTION_NOT_FOUND
            );
        } else {
            $oEntityCollection = new $sEntityCollectionClassName();

            // @todo build query with constraints directly then pass the query to EntityCollection::loadByQuery

            // Generic search
            $aAttributes = $oEntity->getAttributes();
            foreach ($aAttributes as $sKey) {
                if ($oEntity->getDataType($sKey) === 'string') {
                    $aParameters[$sKey] = $this->sSearch;
                }
            }

            // @todo handle last parameters the bStrictMode flag to false (for switch AND => OR | ' = ?' => LIKE %?%)
            $oEntityCollection->loadByParameters(
                $aParameters,
                $this->aOrderBy,
                $this->aLimit,
                false
            );

            // Filter results with attribute constraints
            if (count($aConstraints) > 0) {
                foreach ($oEntityCollection as $oEntity) {
                    foreach ($aConstraints as $sKey => $mValue) {
                        if ($oEntity->{$sKey} !== $mValue) {
                            die(var_dump($oEntity));
                            unset($oEntity);
                        }
                    }
                }
            }


            // store Entity primary key value (id[entity] value)
            foreach ($oEntityCollection as $oEntity) {
                $oEntity->pk = $oEntity->getId();
            }

            $this->aResults[$oEntity::ENTITY] = $oEntityCollection;
            $this->aResults[$oEntity::ENTITY]->count = $oEntityCollection->count();

        }

    }

    /**
     * Set the bundles scope to restrict search
     *
     * @param Scope $oScope
     * @return EntitySearch
     */
    public function setScope(Scope $oScope)
    {
        $this->oScope = $oScope;
        return $this;
    }

    /**
     * @todo delete set entities from scope
     *
     * Set the entities scope
     * @param mixed array|string $mEntity   Entitie(s) scope for search
     */
    protected function setEntitiesScope()
    {

        // @todo iterer sur les bundles et recupérer les entités de chacun puis les placer dans ce scope

        if (is_array($mEntity) && count($mEntity) > 0) {
            $this->aEntitiesScope = $mEntity;
        } elseif (is_string($mEntity) && empty($mEntity) === false && class_exists(App::ENTITIES_NAMESPACE . $mEntity)) {
            $this->aEntitiesScope[] = $mEntity;
        } elseif (is_null($mEntity) || empty($mEntity) === true) {
            // By default behavior we take all available Entities
            $this->aEntitiesScope = $this->aPublicEntitiesScope;
        }

        // @todo retourner l'instance
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
     * Errors codes and messages accessor
     *
     * @todo degager un composant generic de ca... Enum
     *
     * @param integer $iErrorCode (optional)
     * @return mixed array|string|null  If an error code was provided return error message or null otherwise array of errors
     */
    public static function getError($iErrorCode = null)
    {
        if (is_null($iErrorCode) === true) {
            return self::$aErrors;
        } else {
            return ((isset(self::$aErrors[$iErrorCode])=== true) ? self::$aErrors[$iErrorCode] : null);
        }
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

class EntitySearchException extends \Exception
{
}