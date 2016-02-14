<?php
namespace Library\Core\Entity;

use app\Entities\User;
use Library\Core\Exception\CoreException;

/**
 * CRUD action model layer abstract class
 * Perform generic create, update, read and delete actions on Entities with ACL check, data validation and I18n support
 * If Entity has a foreign key to a User the scope is restricted to current session for CRUD
 *
 * @author Nicolas Bonnici <nicolasbonnici@gmail.com>
 *
 */
abstract class Crud extends Acl
{

    /**
     * User instance for the ACL layer check
     * @var User
     */
    protected $oUser = null;

    /**
     * Create new entity
     *
     * @return boolean Library\Core\EntityException
     */
    public function create()
    {
        try {
            if ($this->hasAccess('create') === false) {
                throw new CrudException(
                    sprintf(
                        CrudException::$aErrors[CrudException::ERROR_FORBIDDEN_BY_ACL],
                        array(
                            'create',
                            $this->getEntityName(),
                            $this->oUser->getId()
                        )
                    ),
                    CrudException::ERROR_FORBIDDEN_BY_ACL
                );
            }

            foreach ($this->getAttributes() as $sParameter) {

                # Internationalization support
                if ($this->isI18n() === true) {
                    if (in_array($sParameter, $this->getTranslatedAttributes()) === true) {
                        $this->setTranslation($sParameter, $this->{$sParameter});
                    }
                    continue;
                }

            }

            # Check for foreign keys on User Entity
            if (is_null($this->oUser) === false && $this->hasAttribute($this->oUser->computeForeignKeyName())) {
                $sUserRef = $this->oUser->computeForeignKeyName();
                $this->{$sUserRef} = $this->oUser->getId();
            }

            if ($this->hasAttribute('created')) {
                $this->created = time();
            }
            if ($this->hasAttribute('lastupdate')) {
                $this->lastupdate = null;
            }

            # Check for Nullable attributes
            foreach ($this->getAttributes() as $sAttr) {
                if (
                    $sAttr !== $this->getPrimaryKeyName() &&
                    $this->isNullable($sAttr) === false &&
                    (
                        empty($this->{$sAttr}) === true ||
                        is_null($this->{$sAttr}) === true
                    )
                ) {
                    throw new CrudException(
                        sprintf(
                            CrudException::$aErrors[CrudException::ERROR_ENTITY_MISSING_REQUIRED_ATTRIBUTE],
                            $sAttr
                        ),
                        CrudException::ERROR_ENTITY_MISSING_REQUIRED_ATTRIBUTE
                    );
                }
            }

            if ($this->_create() === true) {
                return $this->isLoaded();
            }

            return false;
        } catch (\Exception $oException) {

            die(var_dump($oException->getMessage()));

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
        try {
            if ($this->hasAccess('read') === false) {
                throw new CrudException(
                    sprintf(
                        CrudException::$aErrors[CrudException::ERROR_FORBIDDEN_BY_ACL],
                        array(
                            'read',
                            $this->getEntityName(),
                            $this->oUser->getId()
                        )
                    ),
                    CrudException::ERROR_FORBIDDEN_BY_ACL
                );
            }

            // Check for user bypass attempt
            if ($this->hasAttribute('user_iduser') && $this->oUser->getId() !== intval($this->user_iduser)) {
                throw new CrudException(
                    CrudException::$aErrors[CrudException::ERROR_USER_INVALID],
                    CrudException::ERROR_USER_INVALID
                );
            } elseif (! $this->isLoaded()) {
                throw new CrudException(
                    CrudException::$aErrors[CrudException::ERROR_ENTITY_NOT_FOUND],
                    CrudException::ERROR_ENTITY_NOT_FOUND
                );
            } else {
                return $this;
            }

        } catch (\Exception $oException) {
            return false;
        }
    }

    /**
     * Update an entity restricted to instanciate user scope if entity is mapped with User
     *
     * @return EntityException
     */
    public function update()
    {
        try {
            if ($this->hasAccess('update') === false) {
                throw new CrudException(
                    sprintf(
                        CrudException::$aErrors[CrudException::ERROR_FORBIDDEN_BY_ACL],
                        array(
                            'update',
                            $this->getEntityName(),
                            $this->oUser->getId()
                        )
                    ),
                    CrudException::ERROR_FORBIDDEN_BY_ACL
                );
            }

            if ($this->hasAttribute('user_iduser') && $this->oUser->getId() !== intval($this->user_iduser)) {
                throw new CrudException(
                    CrudException::$aErrors[CrudException::ERROR_USER_INVALID],
                    CrudException::ERROR_USER_INVALID
                );
            } elseif (! $this->isLoaded()) {
                throw new CrudException(
                    CrudException::$aErrors[CrudException::ERROR_ENTITY_EXISTS],
                    CrudException::ERROR_ENTITY_EXISTS
                );
            } else {

                foreach ($this->getAttributes() as $sKey) {
                    if (empty($sKey) === false) {

                        // Check for user bypass attempt
                        if (
                            (
                                $this->hasAttribute('user_iduser') &&
                                $sKey === 'user_iduser' &&
                                $this->oUser->getId() !== intval($this->{$sKey})
                            ) ||
                            (
                                $this->hasAttribute('user_iduser') &&
                                $sKey === 'iduser' &&
                                $this->oUser->getId() !== intval($this->{$sKey})
                            )
                        ) {
                            throw new CrudException(
                                CrudException::$aErrors[CrudException::ERROR_USER_INVALID],
                                CrudException::ERROR_USER_INVALID
                            );
                        }

                        # Internationalization support
                        if ($this->isI18n() === true) {
                            if (in_array($sKey, $this->getTranslatedAttributes()) === true) {
                                $this->setTranslation($sKey, $this->{$sKey});
                            }
                            continue;
                        }
                    }
                }

                if ($this->hasAttribute('lastupdate')) {
                    $this->lastupdate = time();
                }

                foreach ($this->getAttributes() as $sAttr) {

                    // Check for restricted attributes
                    if (array_key_exists($sAttr, $this->getRestrictedEntityAttributes()) === true) {
                        unset($this->{$sAttr});
                    }

                    // Check for not null value
                    if (empty($this->{$sAttr}) && $this->isNullable($sAttr) === false) {
                        unset($this->{$sAttr});
                    }

                }

                return $this->_update();

            }
        } catch (\Exception $oException) {

            die(var_dump($oException->getMessage(), $this->getEntityName(), 'there'));

            return false;
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
        try {
            if ($this->hasAccess('delete') === false) {
                throw new CrudException(
                    sprintf(
                        CrudException::$aErrors[CrudException::ERROR_FORBIDDEN_BY_ACL],
                        array(
                            'delete',
                            $this->getEntityName(),
                            $this->oUser->getId()
                        )
                    ),
                    CrudException::ERROR_FORBIDDEN_BY_ACL
                );
            }

            // Check for user bypass attempt
            if (($this->hasAttribute('user_iduser') && (is_null($this->oUser))) || ($this->hasAttribute('user_iduser') && $this->oUser->getId() !== intval($this->user_iduser))) {
                throw new CrudException(
                    CrudException::$aErrors[CrudException::ERROR_USER_INVALID],
                    CrudException::ERROR_USER_INVALID
                );
            } elseif (! $this->isLoaded()) {
                throw new CrudException(
                    CrudException::$aErrors[CrudException::ERROR_ENTITY_EXISTS],
                    CrudException::ERROR_ENTITY_EXISTS
                );
            } else {
                return $this->_delete();
            }
        } catch (\Exception $oException) {
            return false;
        }

    }

    /**
     * Get CRUD restricted entity attributes scope
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

    /**
     * @return User
     */
    public function getUser()
    {
        return $this->oUser;
    }

    /**
     * @param User $oUser
     */
    public function setUser(User $oUser)
    {
        $this->oUser = $oUser;

        # Refresh of the ACL layer
        $this->loadUserAcl();
    }

}

class CrudException extends CoreException
{
    /**
     * Error codes
     * @var integer
     */
    const ERROR_USER_INVALID                        = 402;
    const ERROR_FORBIDDEN_BY_ACL                    = 403;
    const ERROR_ENTITY_EXISTS                       = 404;
    const ERROR_ENTITY_NOT_FOUND                    = 405;
    const ERROR_ENTITY_MISSING_REQUIRED_ATTRIBUTE   = 406;

    public static $aErrors = array(
        self::ERROR_USER_INVALID                        => 'You cannot update other user Entities.',
        self::ERROR_FORBIDDEN_BY_ACL                    => 'Current request %s forbidden by ACL layer On Entity %s for User #%s',
        self::ERROR_ENTITY_EXISTS                       => 'Entity was not found.',
        self::ERROR_ENTITY_NOT_FOUND                    => 'Invalid User instance provided.',
        self::ERROR_ENTITY_MISSING_REQUIRED_ATTRIBUTE   => 'Not nullable attribute %s with no value setted.'
    );

}