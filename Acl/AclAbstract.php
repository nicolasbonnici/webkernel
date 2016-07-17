<?php
namespace Library\Core\Acl;

use app\Entities\Group;
use app\Entities\Permission;
use app\Entities\Collection\PermissionCollection;
use Library\Core\Entity\I18n;
use Library\Core\Exception\CoreException;
use Library\Core\Json\Json;

/**
 * ACL abstract layer
 *
 * Class AclAbstract
 * @package Library\Core\Acl
 */
abstract class AclAbstract
{

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
     * @var \app\Entities\Collection\GroupCollection
     */
    protected $oGroupCollection;

    /**
     * An array to store already parsed rights
     *
     * @var array
     */
    protected $aRights = array();

    /**
     * Tell if Acl was properly load for given User instance
     * @var bool
     */
    protected $bIsAclLoaded = false;

    /**
     * Get user's CRUD rights
     * @return Json
     */
    protected function getCRUD()
    {
        if (
            is_null($this->oGroupCollection) === false &&
            is_null($this->oPermissionCollection) === false &&
            $this->oGroupCollection->hasItem() &&
            $this->oPermissionCollection->hasItem()
        ) {
            if (($oPermission = $this->oPermissionCollection->search('entity_class', $this->getChildClass())) != null) {
                $oJsonPermissions =  new Json($oPermission->permission);
                return $oJsonPermissions;
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
    protected function loadUserRights()
    {
        /** @var \app\Entities\Collection\GroupCollection oGroupCollection */
        $this->oGroupCollection = $this->oUser->loadMapped(new Group());
        if (is_null($this->oGroupCollection) === false && $this->oGroupCollection->hasItem() === true) {
            # Load mapped permissions
            /** @var Group $oGroup */
            $aPermissions = array();
            foreach ($this->oGroupCollection as $oGroup) {
                /** @var PermissionCollection $oPermissionCollection */
                $oPermissionCollection = $oGroup->loadMapped(new Permission(), array(), array(), array(0,99));
                if (is_null($oPermissionCollection) === false && $oPermissionCollection->count() > 0) {
                    $aPermissions = array_merge($aPermissions, $oPermissionCollection->getAsArray());
                }
            }

            $this->oPermissionCollection = new PermissionCollection();
            foreach ($aPermissions as $oPermission) {
                $this->oPermissionCollection->add($oPermission);
            }

            return (bool) (
                $this->oGroupCollection->hasItem() == true &&
                is_null($this->oPermissionCollection) === false
            );
        }
        return false;
    }

    protected function loadUserAcl()
    {
        try {
            if ($this->oUser->isLoaded() === false) {
                throw new AclException(get_called_class() . ' Empty user instance provided.');
            }

            # Try to load Acl rights for the given User instance
            $this->bIsAclLoaded = $this->loadUserRights();
        } catch (\Exception $oException) {
            $this->bIsAclLoaded = false;
        }
    }

    /**
     * Tell if Acl layer was properly loaded
     * @return bool
     */
    public function isAclLoaded()
    {
        return $this->bIsAclLoaded;
    }

}

class AclException extends CoreException
{
    const ERROR_NOT_LOADED_INSTANCE = 2;

    public static $aErrors = array(
        self::ERROR_NOT_LOADED_INSTANCE => 'Acl not loaded properly for given User instance, probably no mapped group.'
    );
}