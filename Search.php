<?php
namespace Library\Core;

/**
 * Search on all entities or a restricted scope with custom parameters and filters
 *
 * @todo add Database LIKE %STRING% support
 *
 * @author Nicolas Bonnici <nicolasbonnici@gmail.com>
 *
 */
abstract class Search
{

    /**
     * Errors codes
     *
     * @var integer
     */
    const ERROR_UNAUTHORIZED_REQUEST    = 2;
    const ERROR_EMPTY_REQUEST           = 3;
    const ERROR_EMPTY_ENTITIES_SCOPE    = 4;
    const ERROR_EMPTY_ENTITY            = 5;

    /**
     * Current user instance (optional if $oEntity has no foreign key attribute to \bundles\user\Entities\User)
     *
     * @var \bundles\user\Entities\User
     */
    protected $oUser;

    /**
     * Scope of requested entities to search
     *
     * @var array
     */
    protected $aEntitiesScope = array();

    /**
     * An two dimensionnal array with the Entity name and the Entity collection of founded items
     *
     * @var array
     */
    protected $aResults = array();

    /**
     * Instance constructor
     */
    public function __construct($sSearch, array $aOrderBy = array(), array $aLimit = array(0, 100), $mEntity = null, $mUser = null)
    {
        if (empty($sSearch)) {
            throw new SearchException('No search parameters found!', self::ERROR_EMPTY_REQUEST);
        } elseif (is_array($mEntity)) {
            $this->aEntitiesScope = $mEntity;
        } elseif (is_string($mEntity) && strlen($mEntity) > 0 && class_exists(App::ENTITIES_NAMESPACE . $mEntity)) {
            $this->aEntitiesScope[] = $mEntity;
        } elseif (is_null($mEntity)) {
            // By default behavior we take all available Entities
            $this->aEntitiesScope = App::buildEntities();
        } elseif (is_array($mEntity) && count($mEntity) > 0) {
            $this->aEntitiesScope = $mEntity;
        }

        // Instanciate \bundles\user\Entities\User provided at instance constructor
        if ($mUser instanceof \bundles\user\Entities\User && $mUser->isLoaded()) {
            $this->oUser = clone $mUser;
        } elseif (is_int($mUser) && intval($mUser) > 0) {
            try {
                $this->oUser = new \bundles\user\Entities\User($mUser);
            } catch (\Library\Core\EntityException $oException) {
                $this->oUser = null;
            }
        } else {
            $this->oUser = null;
        }

        if (empty($this->aEntitiesScope)) {
            $this->aEntitiesScope = $this->aEntitiesScope = App::buildEntities();
        } else {

            // For each entity in scope perform the key => value search if the key attribute exists
            foreach ($this->aEntitiesScope as $sEntity) {
                if (is_string($sEntity) && strlen($sEntity) > 0 && class_exists(App::ENTITIES_NAMESPACE . $sEntity)) {
                    try {
                        $this->doSearch($sEntity, $sSearch, $aOrderBy, $aLimit);
                    } catch (SearchException $oException) {}
                }
            }
        }
    }

    /**
     * Perform a search on a given entity
     *
     * @param string $sEntity
     * @param string $sSearch
     * @param array $aOrderBy
     * @param array $aLimit
     * @throws SearchException
     */
    private function doSearch($sEntity, $sSearch, array $aOrderBy = array(), array $aLimit = array())
    {
        assert('is_null($sEntity) || is_array($sEntity) || is_string($sEntity) && strlen($sEntity) > 0');
        assert('!empty($sSearch)');

        if (empty($sEntity)) {
            throw new SearchException('No entity provided...', self::ERROR_EMPTY_ENTITY);
        } elseif (empty($sSearch)) {
            throw new SearchException('No search parameters found!', self::ERROR_EMPTY_REQUEST);
        } else {
            $aParameters = array();
            $sEntityClassName = App::ENTITIES_NAMESPACE . $sEntity;
            $oEntity = new $sEntityClassName();

            $sEntityCollectionClassName = App::ENTITIES_COLLECTION_NAMESPACE . $sEntity . 'Collection';

            // Entities must be searchable and have a EntitiesCollection class too
            if (! $oEntity->isSearchable() || ! class_exists($sEntityCollectionClassName)) {
                throw new SearchException('You can\'t search in this entity ' . $oEntity , self::ERROR_EMPTY_ENTITY);
            } else {
                $oEntityCollection = new $sEntityCollectionClassName();

                // Generic search
                $aAttributes = $oEntity->getAttributes();
                foreach ($aAttributes as $sKey) {
                    if ($oEntity->getDataType($sKey) === 'string') {
                        $aParameters[$sKey] = $sSearch;
                    }
                }

                // @important last parameters the bStrictMode flag to false (for switch AND => OR | ' = ?' => LIKE %?%)
                $oEntityCollection->loadByParameters($aParameters, $aOrderBy, $aLimit, false);

                // store Entity primary key value (id[entity] value)
                foreach ($oEntityCollection as $oEntity) {
                    $oEntity->pk = $oEntity->getId();
                }

                $this->aResults[$sEntity] = $oEntityCollection;
                $this->aResults[$sEntity]->count = $oEntityCollection->count();

            }

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

class SearchException extends \Exception
{
}