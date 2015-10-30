<?php
namespace Library\Core;

use app\Entities\Collection\PermissionCollection;
use app\Entities\Collection\ResourceCollection;
use app\Entities\Group;
use app\Entities\Permission;
use app\Entities\Ressource;
use app\Entities\User;

/**
 * ACL couch layer to manage CRUD access to entities using permissions setted to user's groups
 *
 *
 * @author Nicolas Bonnici <nicolasbonnici@gmail.com>
 *
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
     * User instance current group
     *
     * @var GroupCollection
     */
    protected $oGroupCollection;

    /**
     * Permissions mapped resources
     * @var ResourceCollection
     */
    protected $oResourceCollection;

    /**
     * An array to store already parsed rights
     *
     * @var array
     */
    protected $aRights = array();

    /**
     * Availables ressources
     *
     * @param \app\Entities\User $oUser
     * @throws AclException
     */
    protected $aAvailableRessources;

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
            $this->loadUserRights();
        }
    }

    /**
     * Tell if current user has create access on a given ressource name
     *
     * @param unknown $sRessource
     * @return boolean
     */
    public function hasCreateAccess($sRessource)
    {
        if (
            ! empty($sRessource) &&
            (($oRights = $this->getCRUD($sRessource)) !== NULL) &&
            isset($oRights->create)
        ) {
            return (bool) ($oRights->create === 1);
        }

        return false;
    }

    /**
     * Tell if current user has read access on a given ressource name
     * @param unknown $sRessource
     * @return boolean
     */
    public function hasReadAccess($sRessource)
    {
        if (
            ! empty($sRessource) &&
            (($oRights = $this->getCRUD($sRessource)) !== NULL) &&
            isset($oRights->read)
        ) {
            return (bool) ($oRights->read === 1);
        }

        return false;
    }

    /**
     * Tell if current user has update access on a given ressource name
     * @param string $sRessource        Ressource label name
     * @return boolean
     */
    public function hasUpdateAccess($sRessource)
    {
        if (
            ! empty($sRessource) &&
            (($oRights = $this->getCRUD($sRessource)) !== NULL) &&
            isset($oRights->update)
        ) {
            return (bool) ($oRights->update === 1);
        }

        return false;
    }

    /**
     * Tell if user has delete access on a given entity name
     * @param unknown $sRessource
     * @return boolean
     */
    public function hasDeleteAccess($sRessource)
    {
        if (
            ! empty($sRessource) &&
            (($oRights = $this->getCRUD($sRessource)) !== NULL) &&
            isset($oRights->delete)
        ) {
            return (bool) ($oRights->delete === 1);
        }

        return false;
    }

    /**
     * Tell if user has list access on a given entity name
     * @param unknown $sRessource
     * @return boolean
     */
    public function hasListAccess($sRessource)
    {
        if (
            ! empty($sRessource) &&
            (($oRights = $this->getCRUD($sRessource)) !== NULL) &&
            isset($oRights->list)
        ) {
            return (bool) ($oRights->list === 1);
        }

        return false;
    }

    /**
     * Tell if user has list access on a given entity name
     * @param unknown $sRessource
     * @return boolean
     */
    public function hasListByUserAccess($sRessource)
    {
        return $this->hasListAccess($sRessource);
    }

    /**
     * Get user's CRUD rights
     * @param unknown $sRessource
     * @return mixed|NULL
     */
    private function getCRUD($sRessource)
    {
        $sRessource = strtolower($sRessource);
        if (! empty($sRessource) && $this->oGroupCollection->hasItem() && $this->oPermissionCollection->count() > 0) {
            if (($oRessource = $this->oRessources->search('name', $sRessource)) !== NULL) {
                if (($oPermission = $this->oPermissionCollection->search('ressource_idressource', $oRessource->idressource)) !== NULL) {
                    return json_decode($oPermission->permission);
                }
            }
        }

        return NULL;
    }

    /**
     * Load current user instance group
     *
     * @throws AclException
     * @return boolean
     */
    private function loadUserRights()
    {
        assert('$this->oUser->isLoaded()');

        $this->oGroupCollection = $this->oUser->loadMapped(new Group());

        # Load mapped permissions
        foreach ($this->oGroupCollection as $oGroup) {
            $this->oPermissionCollection = $oGroup->loadMapped(new Permission());
        }

        # Load mapped resources
        $this->oResourceCollection = new ResourceCollection();
        foreach ($this->oPermissionCollection as $oPermission) {
            $this->oResourceCollection->add(
                $oPermission->loadMapped(new Ressource()),
                $this->oResourceCollection->count() + 1
            );
        }

        return (bool) (
            $this->oGroupCollection->hasItem() === true &&
            $this->oPermissionCollection->hasItem() === true &&
            $this->oResourceCollection->hasItem() === true
        );
    }

}

class AclException extends \Exception
{
}
