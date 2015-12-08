<?php
namespace Library\Core\Entity;

use app\Entities\User;

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
     * Current user instance (optional if $oEntity has no foreign key attribute to User)
     *
     * @var User
     */
    protected $oUser;

    /**
     * Current \app\Entities\
     *
     * @var Entity $oEntity
     */
    protected $oEntity;

    /**
     * Entities collection
     *
     * @var EntityCollection
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
     */
    public function __construct(Entity $oEntity, $mUser = null)
    {

        // Instanciate User provided at instance constructor
        if ($mUser instanceof User && $mUser->isLoaded()) {
            $this->oUser = $mUser;
        } elseif (is_int($mUser) && intval($mUser) > 0) {
            try {
                $this->oUser = new User($mUser);
            } catch (EntityException $oException) {
                $this->oUser = null;
            }
        } else {
            $this->oUser = null;
        }

        # Load Entity and EntityCollection instances
        $this->oEntity = $oEntity;
        $sCollectionClassName = $oEntity->computeCollectionClassName();
        $this->oEntities = new $sCollectionClassName;
    }

    /**
     * Create new entity
     *
     * @param array $aParameters A one dimensional array: attribute name => value
     * @throws CrudException If the currently loaded user session is different than the ne entity one
     * @return boolean Library\Core\EntityException
     */
    public function create(array $aParameters = array())
    {
        assert('count($aParameters) > 0');
        assert('!is_null($this->oEntity)');
        assert('!is_null($this->oUser)');

        try {
            $oEntity = clone $this->oEntity;

            foreach ($aParameters as $sParameter=>$mValue) {
                if ($oEntity->hasAttribute($sParameter)) {
                    $oEntity->{$sParameter} = $mValue;
                }
            }

            # Check for foreign keys on User Entity
            if ($oEntity->hasAttribute($this->oUser->computeForeignKeyName())) {
                $oEntity->user_iduser = $this->oUser->getId();
            }

            if ($oEntity->hasAttribute('created')) {
                $oEntity->created = time();
            }
            if ($oEntity->hasAttribute('lastupdate')) {
                $oEntity->lastupdate = null;
            }

            # Check for Nullable attributes
            foreach ($oEntity->getAttributes() as $sAttr) {
                if (
                    $sAttr !== $oEntity->getPrimaryKeyName() &&
                    $oEntity->isNullable($sAttr) === false &&
                    (
                        empty($oEntity->{$sAttr}) === true ||
                        is_null($oEntity->{$sAttr}) === true
                    )
                ) {
                    throw new CrudException(
                        'No value provided for the "' . $sAttr . '" attribute of "' . $oEntity . '" Entity',
                        self::ERROR_ENTITY_MISSING_REQUIRED_ATTRIBUTE
                    );
                }
            }

            if ($oEntity->add() === true) {
                $this->oEntity = clone $oEntity;
                return $this->oEntity->isLoaded();
            }
            return false;
        } catch (\Exception $oException) {
            return false;
        }
    }

    /**
     * Read an entity restricted to user scope
     *
     * @throws CrudException
     * @return Entity
     */
    public function read()
    {
        if (is_null($this->oUser)) {
            throw new CrudException('Invalid user', self::ERROR_USER_INVALID);
        } else {
            // Check for user bypass attempt
            if ($this->oEntity->hasAttribute('user_iduser') && $this->oUser->getId() !== intval($this->oEntity->user_iduser)) {
                throw new CrudException('Invalid user', self::ERROR_USER_INVALID);
            } elseif (! $this->oEntity->isLoaded()) {
                throw new CrudException('Cannot read an unloaded entity.', self::ERROR_ENTITY_NOT_LOADED);
            } else {
                try {
                    return $this->getEntity();
                } catch (EntityException $oException) {
                    return $oException;
                }
            }
        }
    }

    /**
     * Update an entity restricted to instanciate user scope if entity is mapped with User
     *
     * @param array $aParameters
     * @throws CrudException
     * @return EntityException
     */
    public function update(array $aParameters = array())
    {
        assert('count($aParameters) > 0');

        if ($this->oEntity->hasAttribute('user_iduser') && $this->oUser->getId() !== intval($this->oEntity->user_iduser)) {
            throw new CrudException('Invalid user', self::ERROR_USER_INVALID);
        } elseif (! $this->oEntity->isLoaded()) {
            throw new CrudException('Cannot update an unloaded entity.', self::ERROR_ENTITY_NOT_LOADED);
        } else {
            try {

                foreach ($aParameters as $sKey=>$mValue) {
                    if (! empty($sKey)) { // Since value can be nullable handle later on the Entity component

                        // Check for user bypass attempt
                        if (($this->oEntity->hasAttribute('user_iduser') && $sKey === 'user_iduser' && $this->oUser->getId() !== intval($mValue)) || ($this->oEntity->hasAttribute('user_iduser') && $sKey === 'iduser' && $this->oUser->getId() !== intval($mValue))) {
                            throw new CrudException('Invalid user', self::ERROR_USER_INVALID);
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
            } catch (EntityException $oException) {
                return $oException;
            }
        }
    }

    /**
     * Delete an entity restricted to user scope
     *
     * @throws CrudException
     * @return mixed
     */
    public function delete()
    {
        // Check for user bypass attempt
        if (($this->oEntity->hasAttribute('user_iduser') && (is_null($this->oUser))) || ($this->oEntity->hasAttribute('user_iduser') && $this->oUser->getId() !== intval($this->oEntity->user_iduser))) {
            throw new CrudException('Invalid user', self::ERROR_USER_INVALID);
        } elseif (! $this->oEntity->isLoaded()) {
            throw new CrudException('Cannot delete an unloaded entity.', self::ERROR_ENTITY_NOT_LOADED);
        } else {
            try {
                return $this->oEntity->delete();
            } catch (EntityException $oException) {
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
    public function load(array $aOrder = array(), $mLimit = null)
    {
        $this->oEntities->load($aOrder, $mLimit);
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
     * @return boolean
     */
    public function loadUserEntities(array $aParameters = array(), array $aOrderBy = array(), array $aLimit = array(0, 10))
    {
        if (is_null($this->oUser)) {
            throw new CrudException('No User entity instance found!', self::ERROR_USER_INVALID);
        }

        if (isset($aParameters['user_iduser']) === false) {
            $aParameters['user_iduser'] = $this->oUser->getId();
        }

        try {
            return $this->loadEntities($aParameters, $aOrderBy, $aLimit);
        } catch (EntityException $oException) {
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
     * @param array $aEntityRestrictedAttributes
     * @return Crud
     */
    public function setRestrictedEntityAttributes(array $aEntityRestrictedAttributes)
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