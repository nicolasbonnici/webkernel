<?php
namespace Library\Core;

use app\Entities\Collection\PermissionCollection;
use app\Entities\Group;
use app\Entities\Permission;
use app\Entities\User;
use Library\Core\Exception\CoreException;
use Library\Core\Json\Json;

/**
 * ACL layer to manage CRUD access to entities using permissions at group level system
 *
 * @author Nicolas Bonnici <nicolasbonnici@gmail.com>
 */
class Acl
{

    /**
     * User instance
     *
     * @var \app\Entities\User
     */
    protected $oUser;

    /**
     * Entity
     *
     * @var \app\Entities\User
     */
    protected $oEntity;

    /**
     * User instance current permissions
     *
     * Permission's permission attribute structure:
     * { "create": 1, "read": 1,  "update": 1, "delete": 1, "list":1 }
     *
     * @var PermissionCollection
     */
    protected $oPermissionCollection;

    /**
     * User instance current groups
     *
     * @var GroupCollection
     */
    protected $oGroupCollection;

    /**
     * An array to store already parsed rights
     *
     * @var array
     */
    protected $aRights = array();

    /**
     * Available resources
     *
     * @param \app\Entities\User $oUser
     * @throws AclException
     */
    protected $aAvailableRessources;

    /**
     * Tell if Acl was properly load for given User instance
     * @var bool
     */
    protected $bIsLoaded = false;

    /**
     * Instance constructor
     * @param \app\Entities\User $oUser
     * @throws AclException
     */
    public function __construct(User $oUser)
    {
        if (! $oUser->isLoaded()) {
            throw new AclException(get_called_class() . ' Empty user instance provided.');
        } else {
            $this->oUser = $oUser;

            # Try to load Acl rights for the given User instance
            $this->bIsLoaded = $this->loadUserRights();

        }

    }

    /**
     * Tell if current user has create access on a given resource name
     *
     * @param string $sEntityClassName
     * @return boolean
     */
    public function hasCreateAccess($sEntityClassName)
    {
        try {
            if (empty($sEntityClassName) === false && (($oRights = $this->getCRUD($sEntityClassName)) !== null)) {
                return (bool) ($oRights->get('create') === 1);
            }
            return false;
        } catch (\Exception $oException) {
            return false;
        }
    }

    /**
     * Tell if current user has read access on a given resource name
     * @param string $sEntityClassName
     * @return boolean
     */
    public function hasReadAccess($sEntityClassName)
    {
        try {
            if (empty($sEntityClassName) === false && (($oRights = $this->getCRUD($sEntityClassName)) !== null)) {
                return (bool) ($oRights->get('read') === 1);
            }
            return false;
        } catch (\Exception $oException) {
            return false;
        }
    }

    /**
     * Tell if current user has update access on a given resource name
     * @param string $sEntityClassName        Ressource label name
     * @return boolean
     */
    public function hasUpdateAccess($sEntityClassName)
    {
        try {
            if (empty($sEntityClassName) === false && (($oRights = $this->getCRUD($sEntityClassName)) !== null)) {
                return (bool) ($oRights->get('update') === 1);
            }
            return false;
        } catch (\Exception $oException) {
            return false;
        }
    }

    /**
     * Tell if user has delete access on a given entity name
     * @param string $sEntityClassName
     * @return boolean
     */
    public function hasDeleteAccess($sEntityClassName)
    {
        try {
            if (empty($sEntityClassName) === false && (($oRights = $this->getCRUD($sEntityClassName)) !== null)) {
                return (bool) ($oRights->get('delete') === 1);
            }
            return false;
        } catch (\Exception $oException) {
            return false;
        }
    }

    /**
     * Tell if user has list access on a given entity name
     * @param string $sEntityClassName
     * @return boolean
     */
    public function hasListAccess($sEntityClassName)
    {
        try {
            if (empty($sEntityClassName) === false && (($oRights = $this->getCRUD($sEntityClassName)) !== null)) {
                return (bool) ($oRights->get('list') === 1);
            }
            return false;
        } catch (\Exception $oException) {
            return false;
        }
    }

    /**
     * @todo delete useless
     *
     * Tell if user has list access on a given entity name
     * @param string $sEntityClassName
     * @return boolean
     */
    public function hasListByUserAccess($sEntityClassName)
    {
        return $this->hasListAccess($sEntityClassName);
    }

    /**
     * Get user's CRUD rights
     * @param string $sEntityClassName
     * @return Json
     */
    private function getCRUD($sEntityClassName)
    {
        if ($this->bIsLoaded !== true) {
            throw new AclException(
                AclException::getError(AclException::ERROR_NOT_LOADED_INSTANCE),
                AclException::ERROR_NOT_LOADED_INSTANCE
            );
        }

        if (
            empty($sEntityClassName) === false &&
            $this->oGroupCollection->hasItem() &&
            $this->oPermissionCollection->hasItem()
        ) {
            if (($oPermission = $this->oPermissionCollection->search('entity_class', $sEntityClassName)) != null) {
                return new Json($oPermission->permission);
            }
        }
        return null;
    }

    /**
     * Load current user instance group
     *
     * @throws AclException
     * @return boolean
     */
    private function loadUserRights()
    {
        /** @var GroupCollection oGroupCollection */
        $this->oGroupCollection = $this->oUser->loadMapped(new Group());
        if (is_null($this->oGroupCollection) === false && $this->oGroupCollection->hasItem() === true) {
            # Load mapped permissions
            /** @var Group $oGroup */
            foreach ($this->oGroupCollection as $oGroup) {
                $this->oPermissionCollection = $oGroup->loadMapped(new Permission(), array(), array(), array(0,99));
            }

            return (bool) (
                $this->oGroupCollection->hasItem() == true &&
                $this->oPermissionCollection->hasItem() == true
            );
        }
        return false;
    }

    /**
     * Tell if Acl layer was properly loaded
     * @return bool
     */
    public function isLoaded()
    {
        return $this->bIsLoaded;
    }
}

class AclException extends CoreException
{
    const ERROR_NOT_LOADED_INSTANCE = 2;

    public static $aErrors = array(
        self::ERROR_NOT_LOADED_INSTANCE => 'Acl not loaded properly for given User instance, probably no mapped group.'
    );
}
