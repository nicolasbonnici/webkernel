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

class Search
{
    /**
     * Errors codes
     * @var integer
     */
    const ERROR_EMPTY_REQUEST           = 2;
    const ERROR_EMPTY_ENTITIES_SCOPE    = 3;
    const ERROR_EMPTY_ENTITY            = 4;

    /**
     * Current user instance (optional if $oEntity has no foreign key attribute to \app\Entities\User)
     *
     * @var \app\Entities\User
     */
    protected $oUser;

    /**
     * Scope of entities to search
     * @var array
     */
    protected $aEntitiesScope = array();

    /**
     * An two dimensionnal array with the Entity name and the Entity collection of founded items
     * @var array
     */
    protected $aResults;

    /**
     * Instance constructor
     */
    public function __construct(array $aParameters = array(), $mEntity = null, array $aOrderBy = array(), array $aLimit = array(0, 10), $mUser = null)
    {
        if (empty($aParameters)) {
            throw new SearchException('No search parameters found!', self::ERROR_EMPTY_REQUEST);
        } elseif(is_array($mEntity)) {
            $this->aEntitiesScope = $mEntity;
        } elseif (is_string($mEntity) && strlen($mEntity) > 0 && class_exists(App::ENTITIES_NAMESPACE . $mEntity)) {
            $this->aEntitiesScope[] = $mEntity;
        } elseif (is_null($mEntity)) {
            // By default behavior we take all available Entities
            $this->aEntitiesScope = App::buildEntities();
        }

        // Instanciate \app\Entities\User provided at instance constructor
        if ($mUser instanceof \app\Entities\User && $mUser->isLoaded()) {
            $this->oUser = $mUser;
        } elseif (is_int($mUser) && intval($mUser) > 0) {
            try {
                $this->oUser = new \app\Entities\User($mUser);
            } catch (\Library\Core\EntityException $oException) {
                $this->oUser = null;
            }
        } else {
            $this->oUser = null;
        }

        if (empty($this->aEntitiesScope)) {
            throw new SearchException('Empty entities search scope...', self::ERROR_EMPTY_ENTITIES_SCOPE);
        } else {
            // For each entity in scope perform the key => value search if the key attribute exists
            foreach ($this->aEntitiesScope as $sEntity) {
                if (is_string($sEntity) && strlen($sEntity) > 0 && class_exists(App::ENTITIES_NAMESPACE . $sEntity)) {
                    $this->doSearch($sEntity, $aParameters, $aOrderBy, $aLimit);
                }
            }
        }

    }

    private function doSearch($sEntity, array $aParameters = array(), array $aOrderBy = array(), array $aLimit = array())
    {
        assert('!empty($aParameters)');
        assert('is_null($sEntity) || is_array($sEntity) || is_string($sEntity) && strlen($sEntity) > 0');

        if (empty($sEntity)) {
            throw new SearchException('No entity provided...', self::ERROR_EMPTY_ENTITY);
        } elseif (empty($aParameters)) {
            throw new SearchException('No search parameters found!', self::ERROR_EMPTY_REQUEST);
        } else {
            $sEntityCollectionClassName = App::ENTITIES_COLLECTION_NAMESPACE . $sEntity . 'Collection';
            $oEntityCollection = new $sEntityCollectionClassName();
            $oEntityCollection->loadByParameters($aParameters, $aOrderBy, $aLimit);
            $this->aResults[$sEntity] = $oEntityCollection;
        }
    }

    /**
     * @return \app\Entities\Collection\
     */
    public function getResults()
    {
        assert('count($this->aResults) > 0');
        return $this->aResults;
    }
}

class SearchException extends \Exception {}