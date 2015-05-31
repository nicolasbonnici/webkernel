<?php
namespace Library\Core;

use bundles\user\Entities\User;

/**
 * Search on all entities or a restricted scope with custom parameters and filters
 *
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
    const ERROR_UNAUTHORIZED_REQUEST    = 2;
    const ERROR_EMPTY_REQUEST           = 3;
    const ERROR_EMPTY_ENTITIES_SCOPE    = 4;
    const ERROR_EMPTY_ENTITY            = 5;

    /**
     * Current user instance (optional only if $oEntity has a foreign key attribute to \bundles\user\Entities\User)
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
     * Default scope when User was not authenticated
     * @var array
     */
    protected $aPublicEntitiesScope = array();

    /**
     * Search results
     * @var array
     */
    protected $aResults = array();

    /**
     * Instance constructor
     *
     * @todo revoir la signature du constructeur...
     *
     * @param string $sSearch
     * @param array $aOrderBy
     * @param array $aLimit
     * @param mixed string|array $mEntity
     * @param User $mUser
     * @throws SearchException
     * @return Collection
     */
    public function __construct($sSearch, array $aOrderBy = array(), array $aLimit = array(0, 100), $mEntity = null, $mUser = null)
    {
        if (empty($sSearch) === true) {
            throw new EntitySearchException('No search parameters found!', self::ERROR_EMPTY_REQUEST);
        }

        $this->setEntitiesScope($mEntity);

        $this->setUser($mUser);

        return $this->process($sSearch, $aOrderBy, $aLimit);
    }

    /**
     *
     * @return Collection
     */
    protected function process($sSearch, array $aOrderBy, array $aLimit = array(0, 100))
    {
        $oExceptions = new Collection();
        // For each entity in scope perform the key => value search if the key attribute exists
        foreach ($this->aEntitiesScope as $sEntity => $aEntityParams) {
            if (
                is_string($aEntityParams['entity']) &&
                strlen($aEntityParams['entity']) > 0 &&
                class_exists($aEntityParams['entity'])
            ) {
                try {
                    $this->doSearch($aEntityParams['entity'], $sSearch, $aOrderBy, $aLimit, $aEntityParams['constraints']);
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
     * @param string $sEntity
     * @param string $sSearch
     * @param array $aOrderBy
     * @param array $aLimit
     * @throws SearchException
     */
    protected function doSearch($sEntity, $sSearch, array $aOrderBy = array(), array $aLimit = array(0, 100), array $aConstraints = array())
    {
        assert('!empty($sSearch)');

        if (empty($sEntity)) {
            throw new EntitySearchException('No entity provided...', self::ERROR_EMPTY_ENTITY);
        } elseif (empty($sSearch)) {
            throw new EntitySearchException('No search parameters found!', self::ERROR_EMPTY_REQUEST);
        } else {
            $aParameters = array();
            $sEntityClassName = $sEntity;
            $oEntity = new $sEntityClassName();

            $sEntityCollectionClassName = $oEntity->computeCollectionClassName();

            // Entities must be searchable and have a EntityCollection class too
            if (! $oEntity->isSearchable() || ! class_exists($sEntityCollectionClassName)) {
                throw new EntitySearchException('You can\'t search in this entity ' . $oEntity , self::ERROR_EMPTY_ENTITY);
            } else {
                $oEntityCollection = new $sEntityCollectionClassName();

                // Generic search
                $aAttributes = $oEntity->getAttributes();
                foreach ($aAttributes as $sKey) {
                    if ($oEntity->getDataType($sKey) === 'string') {
                        $aParameters[$sKey] = $sSearch;
                    }
                }

                // @todo handle last parameters the bStrictMode flag to false (for switch AND => OR | ' = ?' => LIKE %?%)
                $oEntityCollection->loadByParameters($aParameters, $aOrderBy, $aLimit, false);

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

    }

    /**
     * Set the entities scope
     * @param mixed array|string $mEntity   Entitie(s) scope for search
     */
    protected function setEntitiesScope($mEntity)
    {
        if (is_array($mEntity) && count($mEntity) > 0) {
            $this->aEntitiesScope = $mEntity;
        } elseif (is_string($mEntity) && empty($mEntity) === false && class_exists(App::ENTITIES_NAMESPACE . $mEntity)) {
            $this->aEntitiesScope[] = $mEntity;
        } elseif (is_null($mEntity) || empty($mEntity) === true) {
            // By default behavior we take all available Entities
            $this->aEntitiesScope = $this->aPublicEntitiesScope;
        }
    }

    /**
     * Set instance User
     * @param mixed int|User $mUser
     */
    protected function setUser($mUser)
    {
        if ($mUser instanceof User && $mUser->isLoaded()) {
            $this->oUser = clone $mUser;
        } elseif (is_int($mUser) && intval($mUser) > 0) {
            try {
                $this->oUser = new User($mUser);
            } catch (\Library\Core\EntityException $oException) {
                $this->oUser = null;
            }
        } else {
            $this->oUser = null;
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