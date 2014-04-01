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
    const ERROR_EMPTY_REQUEST = 2;

    const ERROR_UNAUTHORIZED_REQUEST = 403;

    const ERROR_EMPTY_ENTITIES_SCOPE = 3;

    const ERROR_EMPTY_ENTITY = 4;

    /**
     * Current user instance (optional if $oEntity has no foreign key attribute to \app\Entities\User)
     *
     * @var \app\Entities\User
     */
    protected $oUser;

    /**
     * Scope of requested entities to search
     *
     * @var array
     */
    protected $aEntitiesScope = array();

    /**
     * Scope of forbidden entities to search
     *
     * @var array
     */
    protected $aForbiddenEntities = array(
        'User',
        'Role',
        'Permission',
        'Ressource'
    );

    /**
     * An two dimensionnal array with the Entity name and the Entity collection of founded items
     *
     * @var array
     */
    protected $aResults;

    /**
     * Instance constructor
     */
    public function __construct($mSearch, array $aOrderBy = array(), array $aLimit = array(0, 100), $mEntity = null, $mUser = null)
    {
        if (empty($mSearch)) {
            throw new SearchException('No search parameters found!', self::ERROR_EMPTY_REQUEST);
        } elseif (is_array($mEntity)) {
            $this->aEntitiesScope = $mEntity;
        } elseif (is_string($mEntity) && strlen($mEntity) > 0 && class_exists(App::ENTITIES_NAMESPACE . $mEntity)) {
            $this->aEntitiesScope[] = $mEntity;
        } elseif (is_null($mEntity)) {
            // By default behavior we take all available Entities
            $this->aEntitiesScope = App::buildEntities();
        }
        
        // Instanciate \app\Entities\User provided at instance constructor
        if ($mUser instanceof \app\Entities\User && $mUser->isLoaded()) {
            $this->oUser = clone $mUser;
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
            throw new SearchException('Empty entities scope...', self::ERROR_EMPTY_ENTITIES_SCOPE);
        } else {
            
            // For each entity in scope perform the key => value search if the key attribute exists
            foreach ($this->aEntitiesScope as $sEntity) {
                if (is_string($sEntity) && strlen($sEntity) > 0 && class_exists(App::ENTITIES_NAMESPACE . $sEntity)) {
                    try {
                        $this->doSearch($sEntity, $mSearch, $aOrderBy, $aLimit);
                    } catch (SearchException $oException) {}
                }
            }
        }
    }

    private function doSearch($sEntity, $mSearch, array $aOrderBy = array(), array $aLimit = array())
    {
        assert('is_null($sEntity) || is_array($sEntity) || is_string($sEntity) && strlen($sEntity) > 0');
        assert('!empty($mSearch)');
        
        if (in_array($sEntity, $this->aForbiddenEntities) && is_null($this->oUser)) {
            throw new SearchException('Unauthorized request!', self::ERROR_UNAUTHORIZED_REQUEST);
        } elseif (empty($sEntity)) {
            throw new SearchException('No entity provided...', self::ERROR_EMPTY_ENTITY);
        } elseif (empty($mSearch)) {
            throw new SearchException('No search parameters found!', self::ERROR_EMPTY_REQUEST);
        } else {
            $aParameters = array();
            $sEntityClassName = App::ENTITIES_NAMESPACE . $sEntity;
            $sEntityCollectionClassName = App::ENTITIES_COLLECTION_NAMESPACE . $sEntity . 'Collection';
            $oEntity = new $sEntityClassName();
            $oEntityCollection = new $sEntityCollectionClassName();
            
            // JSON encoded parameters
            $mSearch = json_decode($mSearch);
            
            if (is_array($mSearch) && count($mSearch) > 0) {
                foreach ($mSearch as $oRequest) {
                    if (isset($oRequest->name, $oRequest->value) && $oRequest->name === 'search' && ! empty($oRequest->value)) {
                        // Generic search
                        $aAttributes = $oEntity->getAttributes();
                        foreach ($aAttributes as $sKey) {
                            if ($oEntity->getDataType($sKey) === 'string') {
                                $aParameters[$sKey] = $oRequest->value;
                            }
                        }
                    } elseif (isset($oRequest->name, $oRequest->value) && ! empty($oRequest->name) && ! empty($oRequest->value)) {
                        // Simple keys values search
                        $aParameters[$oRequest->name] = $oRequest->value;
                    } else {
                        throw new SearchException('No search parameters found!', self::ERROR_EMPTY_REQUEST);
                    }
                }
            }
            
            // @important the bStrictMode flag to false (for AND => OR | ' = ?' => LIKE %?%)
            $oEntityCollection->loadByParameters($aParameters, $aOrderBy, $aLimit, false);
            $this->aResults[$sEntity] = $oEntityCollection;
            $this->aResults[$sEntity]->count = $oEntityCollection->count();
        }
    }

    /**
     *
     * @return \app\Entities\Collection\
     */
    public function getResults()
    {
        assert('count($this->aResults) > 0');
        return $this->aResults;
    }
}

class SearchException extends \Exception
{
}