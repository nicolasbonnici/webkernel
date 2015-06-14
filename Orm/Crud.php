<?php
namespace Core\Orm;

/**
 * CRUD action model layer abstract class
 * Perform generic create, update, read and delete actions on Entity
 * Also perform load and search requests
 * If Entity has a foreign key to a User the scope is restricted to current session entities for CRUD, load and search actions
 *
 * @author Nicolas Bonnici <nicolasbonnici@gmail.com>
 *
 */
abstract class Crud
{

    /**
     * Error codes
     * @var integer
     */
    const ERROR_USER_INVALID                        = 402;
    const ERROR_FORBIDDEN_BY_ACL                    = 403;
    const ERROR_ENTITY_EXISTS                       = 404;
    const ERROR_ENTITY_NOT_LOADED                   = 405;
    const ERROR_ENTITY_MISSING_REQUIRED_ATTRIBUTE   = 406;

    /**
     * Current user instance (optional if $oEntity has no foreign key attribute to \bundles\user\Entities\User)
     *
     * @var \bundles\user\Entities\User
     */
    protected $oUser;

    /**
     * Current \app\Entities\
     *
     * @var \app\Entities\
     */
    protected $oEntity;

    /**
     * Entities collection
     *
     * @var \app\Entities\Collection\
     */
    protected $oEntities;

    /**
     * Restricted attributes scope for update
     *
     * @var array
     */
    protected $aEntityRestrictedAttributes = array();

    /**
     * Instance constructor
     * 
     * @todo virer le param entity collection et plutot computer entity avec un methode generique
     * 
     */
    public function __construct($sEntityClassName, $sEntityCollectionClassName, $iPrimaryKey = 0, $mUser = null)
    {
        assert('is_null($mUser) || $mUser instanceof \bundles\user\Entities\User && $mUser->isLoaded() || (is_int($mUser) && intval($mUser) > 0)');
        assert('is_null($iPrimaryKey) || $iPrimaryKey === 0 || (is_int($iPrimaryKey) && intval($iPrimaryKey) > 0)');

        if (empty($sEntityClassName) || ! class_exists($sEntityClassName)) {
            throw new CrudException(
                'Entity requested not found (' . $sEntityClassName . '), you need to create manually or scaffold his \app\Entities class.',
                App::ERROR_ENTITY_EXISTS
            );
        } else {
            try {
                // Instanciate \bundles\user\Entities\User provided at instance constructor
                if ($mUser instanceof \bundles\user\Entities\User && $mUser->isLoaded()) {
                    $this->oUser = $mUser;
                } elseif (is_int($mUser) && intval($mUser) > 0) {
                    try {
                        $this->oUser = new \bundles\user\Entities\User($mUser);
                    } catch (\Core\Orm\EntityException $oException) {
                        $this->oUser = null;
                    }
                } else {
                    $this->oUser = null;
                }
            } catch (\Core\Orm\EntityException $oException) {
                throw new CrudException('Invalid user instance provided', App::ERROR_USER_INVALID);
            }

            try {
                $this->oEntity = new $sEntityClassName(((intval($iPrimaryKey) > 0) ? $iPrimaryKey : null));
                $this->oEntities = new $sEntityCollectionClassName;
            } catch (\Core\Orm\EntityException $oException) {
                throw new CrudException('Invalid entity provided, unable to load...', App::ERROR_ENTITY_NOT_LOADABLE);
            }
        }
    }

    /**
     * Create new entity
     *
     * @param array $aParameters A one dimensional array: attribute name => value
     * @throws CrudException If the currently loaded user session is different than the ne entity one
     * @return boolean Core\Orm\EntityException
     */
    public function create(array $aParameters = array())
    {
        assert('count($aParameters) > 0');
        assert('!is_null($this->oEntity)');
        assert('!is_null($this->oUser)');

        // Check for user bypass attempt
        if (($this->oEntity->hasAttribute('user_iduser') && isset($aParameter['user_iduser']) && $this->oUser->getId() !== intval($aParameter['user_iduser'])) || ($this->oEntity->hasAttribute('iduser') && isset($aParameter['iduser']) && $this->oUser->getId() !== intval($aParameter['iduser']))) {
            throw new CrudException('Invalid user', App::ERROR_USER_INVALID);
        } else {
            try {
                $oEntity = clone $this->oEntity;

                foreach ($aParameters as $sParameter=>$mValue) {
                    if ($oEntity->hasAttribute($sParameter)) {
                        $oEntity->{$sParameter} = $mValue;
                    }
                }

                // Check for user bypass attempt
                if ($oEntity->hasAttribute('user_iduser')) {
                    $oEntity->user_iduser = $this->oUser->getId();
                }

                if ($oEntity->hasAttribute('created')) {
                    $oEntity->created = time();
                }
                if ($oEntity->hasAttribute('lastupdate')) {
                    $oEntity->lastupdate = time();
                }

                // Check for Null attributes
                foreach ($oEntity->getAttributes() as $sAttr) {
                    if ($sAttr !== $oEntity->getPrimaryKeyName() && ! $oEntity->isNullable($sAttr) && empty($oEntity->{$sAttr})) {
                        throw new CrudException(
                            'No value provided for the "' . $sAttr . '" attribute of "' . $oEntity . '" Entity',
                            self::ERROR_ENTITY_MISSING_REQUIRED_ATTRIBUTE
                        );
                    }
                }

                $this->oEntity = clone $oEntity;

                return $oEntity->add();
            } catch (\Core\Orm\EntityException $oException) {
                return $oException;
            }
        }
    }

    /**
     * Read an entity restricted to user scope
     *
     * @throws CrudException
     * @return mixed \app\Entities\{Entity}|\Core\Orm\EntityException TRUE is entity is correctly deleted otherwhise the \Core\Orm\EntityException
     */
    public function read()
    {
        if (is_null($this->oUser)) {
            throw new CrudException('Invalid user', App::ERROR_USER_INVALID);
        } else {
            // Check for user bypass attempt
            if ($this->oEntity->hasAttribute('user_iduser') && $this->oUser->getId() !== intval($this->oEntity->user_iduser)) {
                throw new CrudException('Invalid user', App::ERROR_USER_INVALID);
            } elseif (! $this->oEntity->isLoaded()) {
                throw new CrudException('Cannot read an unloaded entity.', App::ERROR_ENTITY_NOT_LOADED);
            } else {
                try {
                    return $this->getEntity();
                } catch (\Core\Orm\EntityException $oException) {
                    return $oException;
                }
            }
        }
    }

    /**
     * Update an entity restricted to instanciate user scope if entity is mapped with \bundles\user\Entities\User
     *
     * @param array $aParameters
     * @throws CrudException
     * @return \Core\Orm\EntityException
     */
    public function update(array $aParameters = array())
    {
        assert('count($aParameters) > 0');

        if ($this->oEntity->hasAttribute('user_iduser') && $this->oUser->getId() !== intval($this->oEntity->user_iduser)) {
            throw new CrudException('Invalid user', App::ERROR_USER_INVALID);
        } elseif (! $this->oEntity->isLoaded()) {
            throw new CrudException('Cannot update an unloaded enitity.', App::ERROR_ENTITY_NOT_LOADED);
        } else {
            try {

                foreach ($aParameters as $sKey=>$mValue) {
                    if (! empty($sKey)) { // Since value can be nullable handle later on the Entity component

                        // Check for user bypass attempt
                        if (($this->oEntity->hasAttribute('user_iduser') && $sKey === 'user_iduser' && $this->oUser->getId() !== intval($mValue)) || ($this->oEntity->hasAttribute('user_iduser') && $sKey === 'iduser' && $this->oUser->getId() !== intval($mValue))) {
                            throw new CrudException('Invalid user', App::ERROR_USER_INVALID);
                        }

                        $this->oEntity->{$sKey} = $mValue;
                    }
                }

                if ($this->oEntity->hasAttribute('lastupdate')) {
                    $this->oEntity->lastupdate = time();
                }

                foreach ($this->oEntity->getAttributes() as $sAttr) {

                    // Check for restricted attributes
                    if (array_key_exists($sAttr, $this->getRestrictedEntityAttributes()) === true) {
                        unset($this->oEntity->{$sAttr});
                    }

                    // Check for not null value
                    if (empty($this->oEntity->{$sAttr}) && $this->oEntity->isNullable($sAttr) === false) {
                        unset($this->oEntity->{$sAttr});
                    }

                }

                return $this->oEntity->update();
            } catch (\Core\Orm\EntityException $oException) {
                return $oException;
            }
        }
    }

    /**
     * Delete an entity restricted to user scope
     *
     * @throws CrudException
     * @return mixed \app\Entities\{Entity}|\Core\Orm\EntityException
     */
    public function delete()
    {

        // Check for user bypass attempt
        if (($this->oEntity->hasAttribute('user_iduser') && (is_null($this->oUser))) || ($this->oEntity->hasAttribute('user_iduser') && $this->oUser->getId() !== intval($this->oEntity->user_iduser))) {
            throw new CrudException('Invalid user', App::ERROR_USER_INVALID);
        } elseif (! $this->oEntity->isLoaded()) {
            throw new CrudException('Cannot delete an unloaded entity.', App::ERROR_ENTITY_NOT_LOADED);
        } else {
            try {
                return $this->oEntity->delete();
            } catch (\Core\Orm\EntityException $oException) {
                return $oException;
            }
        }
    }

    /**
     * Load latest entities
     *
     * @param array $aParameters
     * @param array $aOrderBy
     * @param array $aLimit
     * @return boolean
     */
    public function load($sOrderBy = '', $sOrder = 'DESC', array $aLimit = array(0,50))
    {
        $this->oEntities->load($sOrderBy, $sOrder, $aLimit);
        return ($this->oEntities->count() > 0);
    }

    /**
     * Load entities on given parameters
     *
     * @param array $aParameters
     * @param array $aOrderBy
     * @param array $aLimit
     * @return boolean
     */
    public function loadEntities(array $aParameters = array(), array $aOrderBy = array(), array $aLimit = array(0, 25))
    {
        $this->oEntities->loadByParameters($aParameters, $aOrderBy, $aLimit);
        return ($this->oEntities->count() > 0);
    }

    /**
     * Load user's entities
     *
     * @param array $aParameters
     * @param array $aOrderBy
     * @param array $aLimit
     * @throws CrudException
     * @return boolean \Core\Orm\EntityException
     */
    public function loadUserEntities(array $aParameters = array(), array $aOrderBy = array(), array $aLimit = array(0, 10))
    {
        if (is_null($this->oUser)) {
            throw new CrudException('No \bundles\user\Entities\User entity instance found!', App::ERROR_ENTITY_NOT_MAPPED_TO_USERS);
        }

        if (isset($aParameters['user_iduser']) === false) {
            $aParameters['user_iduser'] = $this->oUser->getId();
        }

        try {
            return $this->loadEntities($aParameters, $aOrderBy, $aLimit);
        } catch (\Core\Orm\EntityException $oException) {
            return $oException;
        }
    }

    /**
     * Get allowed entity attributes scope
     *
     * @return array
     */
    public function getRestrictedEntityAttributes()
    {
        return $this->aEntityRestrictedAttributes;
    }

    /**
     * Set allowed entity attributes scope
     *
     * @param unknown $aEntityRestrictedAttributes
     * @return \Core\Crud
     */
    public function setRestrictedEntityAttributes($aEntityRestrictedAttributes)
    {
        $this->aEntityRestrictedAttributes = $aEntityRestrictedAttributes;
        return $this;
    }

    /*
     * Get current instance \app\Entities Entity properties @return array
     */
    public function getEntityAttributes()
    {
        return $this->oEntity->getAttributes();
    }

    /**
     *
     * @return \app\Entities\Collection\
     */
    public function getEntities()
    {
        return $this->oEntities;
    }

    /**
     *
     * @return \app\Entities\
     */
    public function getEntity()
    {
        return $this->oEntity;
    }
}

class CrudException extends \Exception
{
}