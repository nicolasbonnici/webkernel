<?php
namespace Library\Core;

/**
 * CRUD action model layer abstract class
 *
 * @author niko
 *
 */

abstract class Crud {

    /**
     * @var string
     */
    const ENTITIES_NAMESPACE                = '\app\Entities\\';
    const ENTITIES_COLLECTION_NAMESPACE     = '\app\Entities\Collection\\';

    /**
     * Exceptions error code
     * @var integer
     */
    const ERROR_ENTITY_EXISTS                   = 400;
    const ERROR_USER_INVALID                    = 401;
    const ERROR_ENTITY_NOT_LOADED               = 402;
    const ERROR_ENTITY_NOT_OWNED_BY_USER        = 403;
    const ERROR_ENTITY_NOT_LOADABLE             = 404;
    const ERROR_ENTITY_NOT_MAPPED_TO_USERS      = 405;
    const ERROR_FORBIDDEN_BY_ACL                = 406;
    const ERROR_ENTITY_EMPTY_ATTRIBUTE          = 407;

    /**
     * Current user instance (optional if $oEntity has no foreign key attribute to \app\Entities\User)
     *
     * @var \app\Entities\User
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
     * Instance constructor
     */
    public function __construct($sEntityName, $iPrimaryKey = 0, $mUser = null)
    {
        assert('is_null($mUser) || $mUser instanceof \app\Entities\User && $mUser->isLoaded() || is_int($mUser) && intval($mUser) > 0');
        assert('!empty($sEntityName) || !class_exists(self::ENTITIES_NAMESPACE . $sEntityName)');
        assert('intval($iPrimaryKey) === 0 || intval($iPrimaryKey) > 0');

        if (empty($sEntityName) || !class_exists(self::ENTITIES_NAMESPACE . $sEntityName)) {
            throw new CrudException("Entity requested not found, you need to create manually or scaffold his \app\Entities class.", self::ERROR_ENTITY_EXISTS);
        } else {
            try {
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
            } catch (\Library\Core\EntityException $oException) {
                throw new CrudException('Invalid user instance provided', self::ERROR_USER_INVALID);
             }

             try {
                $sEntityClassName = self::ENTITIES_NAMESPACE . $sEntityName;
                $sEntityCollectionClassName = self::ENTITIES_COLLECTION_NAMESPACE . $sEntityName . 'Collection';
                $this->oEntity = new $sEntityClassName(((intval($iPrimaryKey) > 0) ? $iPrimaryKey : null));
                $this->oEntities = new $sEntityCollectionClassName();
            } catch (\Library\Core\EntityException $oException) {
                throw new CrudException('Invalid user instance provided', self::ERROR_ENTITY_NOT_LOADABLE);
            }

        }

    }

    /**
     * Create new entity
     *
     * @param array $aParameters            A one dimensional array: attribute name => value
     * @throws CrudException        If the currently loaded user session is different than the ne entity one
     * @return boolean|Library\Core\EntityException
     */
    public function create(array $aParameters = array())
    {
        assert('count($aParameters) > 0');
        assert('!is_null($this->oEntity)');
        assert('!is_null($this->oUser)');

        // Check for user bypass attempt
        if (
            (
                $this->oEntity->hasAttribute('user_iduser') &&
                isset($aParameter['user_iduser']) &&
                $this->oUser->getId() !== intval($aParameter['user_iduser'])
            ) ||
            (
                $this->oEntity->hasAttribute('iduser') &&
                isset($aParameter['iduser']) &&
                $this->oUser->getId() !== intval($aParameter['iduser'])
            )
        ) {
            throw new CrudException('Invalid user', self::ERROR_USER_INVALID);
        } else {
            try {
                $oEntity = clone $this->oEntity;

                foreach ($aParameters as $aParameter) {
                        if (
                            !empty($aParameter['name']) &&
                            !empty($aParameter['value'])
                        ) {
                            $oEntity->{$aParameter['name']} = $aParameter['value'];
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
                     if ($sAttr !== $oEntity->getPrimaryKeyName() && !$oEntity->isNullable($sAttr) && empty($oEntity->{$sAttr})) {
                         throw new CrudException('No value provided for the "' . $sAttr . '" attribute of "' . $oEntity .'" Entity', self::ERROR_ENTITY_EMPTY_ATTRIBUTE);
                     }
                 }

                $this->oEntity = clone $oEntity;

                return $oEntity->add();
            } catch (\Library\Core\EntityException $oException) {
                return $oException;
            }
        }

    }


    /**
     * Read an entity restricted to user scope
     *
     * @throws CrudException
     * @return mixed \app\Entities\{Entity}|\Library\Core\EntityException TRUE is entity is correctly deleted otherwhise the \Library\Core\EntityException
     */
    public function read() {
        if (is_null($this->oUser)) {
            throw new CrudException('Invalid user', self::ERROR_USER_INVALID);
        } else {
            // Check for user bypass attempt
            if ($this->oEntity->hasAttribute('user_iduser') && $this->oUser->getId() !== intval($this->oEntity->user_iduser)) {
                throw new CrudException('Invalid user', self::ERROR_USER_INVALID);
            } elseif (!$this->oEntity->isLoaded()) {
                throw new CrudException('Cannot read an unloaded entity.', self::ERROR_ENTITY_NOT_LOADED);
            } else {
                try {
                    return $this->getEntity();
                } catch (\Library\Core\EntityException $oException) {
                    return $oException;
                }
            }
        }
    }

    /**
     * Update an entity restricted to instanciate user scope if entity is mapped with \app\Entities\User
     *
     * @param array $aParameters
     * @throws CrudException
     * @return \Library\Core\EntityException
     */
    public function update(array $aParameters = array()) {
        assert('count($aParameters) > 0');

        if ($this->oEntity->hasAttribute('user_iduser') && $this->oUser->getId() !== intval($this->oEntity->user_iduser)) {
            throw new CrudException('Invalid user', self::ERROR_USER_INVALID);
        } elseif (!$this->oEntity->isLoaded()) {
            throw new CrudException('Cannot update an unloaded enitity.', self::ERROR_ENTITY_NOT_LOADED);
        } else {

            try {
                // Check for user bypass attempt
                if (
                    (
                        $this->oEntity->hasAttribute('user_iduser') &&
                        !empty($aParameter['user_iduser']) &&
                        $this->oUser->getId() !== intval($aParameter['user_iduser'])
                    ) ||
                    (
                        $this->oEntity->hasAttribute('user_iduser') &&
                        !empty($aParameter['iduser']) &&
                        $this->oUser->getId() !== intval($aParameter['iduser'])
                    )
                ) {
                    throw new CrudException('Invalid user', self::ERROR_USER_INVALID);
                }

                foreach ($aParameters as $aParameter) {
                    if (
                        !empty($aParameter['name']) &&
                        !empty($aParameter['value'])
                    ) {
                        $this->oEntity->{$aParameter['name']} = $aParameter['value'];
                    }
                }

                if ($this->oEntity->hasAttribute('lastupdate')) {
                    $this->oEntity->lastupdate = time();
                }

                // Check for Null attributes
                foreach ($this->oEntity->getAttributes() as $sAttr) {
                    if (!$this->oEntity->isNullable($sAttr) && empty($this->oEntity->{$sAttr})) {
                        throw new CrudException('No value provided for the "' . $sAttr . '" attribute of "' . $oEntity .'" Entity', self::ERROR_ENTITY_EMPTY_ATTRIBUTE);
                    }
                }

                return $this->oEntity->update();
            } catch (\Library\Core\EntityException $oException) {
                return $oException;
            }
        }
    }

    /**
     * Delete an entity restricted to user scope
     *
     * @throws CrudException
     * @return mixed \app\Entities\{Entity}|\Library\Core\EntityException
     */
    public function delete() {

        // Check for user bypass attempt
        if (
            (
                $this->oEntity->hasAttribute('user_iduser') &&
                (is_null($this->oUser))
            ) || (
                $this->oEntity->hasAttribute('user_iduser') &&
                $this->oUser->getId() !== intval($this->oEntity->user_iduser)
            )
        ) {
            throw new CrudException('Invalid user', self::ERROR_USER_INVALID);
        } elseif (!$this->oEntity->isLoaded()) {
            throw new CrudException('Cannot delete an unloaded entity.', self::ERROR_ENTITY_NOT_LOADED);
        } else {
            try {
                return $this->oEntity->delete();
            } catch (\Library\Core\EntityException $oException) {
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
    public function loadEntities(array $aParameters = array(), array $aOrderBy = array(), array $aLimit = array(0, 10))
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
     * @return boolean|\Library\Core\EntityException
     */
    public function loadUserEntities(array $aParameters = array(), array $aOrderBy = array(), array $aLimit = array(0, 10))
    {
        if (is_null($this->oUser)) {
            throw new CrudException('No \app\Entities\User entity instance found!', self::ERROR_ENTITY_NOT_MAPPED_TO_USERS);
        }

        if (!isset($aParameters['user_iduser'])) {
            $aParameters['user_iduser'] = $this->oUser->getId();
        }

        try {
            return $this->loadEntities($aParameters, $aOrderBy, $aLimit);
        } catch (\Library\Core\EntityException $oException) {
            return $oException;
        }
    }


   /*
    * Get current instance \app\Entities Entity properties
    * @return array
    */
    public function getEntityAttributes()
    {
        return $this->oEntity->getAttributes();
    }

    /**
     * @return \app\Entities\Collection\
     */
    public function getEntities()
    {
        assert('$this->oEntities->count() > 0');
        return $this->oEntities;
    }

    /**
     * @return \app\Entities\
     */
    public function getEntity()
    {
        assert('$this->oEntity->isLoaded()') ;
        return $this->oEntity;
    }
}

class CrudException extends \Exception {}