<?php
namespace Library\Core;

/**
 *
 * @author Nicolas Bonnici <nicolasbonnici@gmail.com>
 *
 *         ACL couch layer
 *         Manage CRUD access to entities
 */
abstract class Acl
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
     * @var \app\Entities\Entity
     */
    protected $oEntity;

    /**
     * User instance current permissions
     *
     * Permission's permission attribute structure:
     * { "create": 1, "read": 1,  "update": 1, "delete": 1, "list":1 }
     *
     * @var \app\Entities\Collection\PermissionCollection
     */
    protected $oPermissions;

    /**
     * User instance current group
     *
     * @var \app\Entities\Collection\UserGroupCollection
     */
    protected $oGroups;

    /**
     * An array to store already parsed rights
     *
     * @todo store directly json objects in cache
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
    public function __construct(\app\Entities\User $oUser)
    {
        if (! $oUser->isLoaded()) {
            throw new AclException(__CLASS__ . ' Empty user instance provided.');
        } else {
            $this->oUser = $oUser;
            $this->getUserGroups();
            if ($this->getPermissions()) {
                $this->getRessources();
            }
        }
    }

    /**
     * Tell if current user has create access on a given ressource name
     *
     * @param unknown $sRessource
     * @return boolean
     */
    protected function hasCreateAccess($sRessource)
    {
        if (! empty($sRessource) && (($oRights = $this->getCRUD($sRessource)) !== NULL)) {
            return $oRights->create === 1;
        }

        return false;
    }

    /**
     * Tell if current user has read access on a given ressource name
     * @param unknown $sRessource
     * @return boolean
     */
    protected function hasReadAccess($sRessource)
    {
        if (! empty($sRessource) && (($oRights = $this->getCRUD($sRessource)) !== NULL)) {
            return $oRights->read === 1;
        }

        return false;
    }

    /**
     * Tell if current user has update access on a given ressource name
     * @param unknown $sRessource
     * @return boolean
     */
    protected function hasUpdateAccess($sRessource)
    {
        if (! empty($sRessource) && (($oRights = $this->getCRUD($sRessource)) !== NULL)) {
            return $oRights->update === 1;
        }

        return false;
    }

    /**
     * Tell if user has delete access on a given entity name
     * @param unknown $sRessource
     * @return boolean
     */
    protected function hasDeleteAccess($sRessource)
    {
        if (! empty($sRessource) && (($oRights = $this->getCRUD($sRessource)) !== NULL)) {
            return $oRights->delete === 1;
        }

        return false;
    }

    /**
     * Tell if user has list access on a given entity name
     * @param unknown $sRessource
     * @return boolean
     */
    protected function hasListAccess($sRessource)
    {
        if (! empty($sRessource) && (($oRights = $this->getCRUD($sRessource)) !== NULL)) {
            return $oRights->list === 1;
        }

        return false;
    }

    /**
     * Tell if user has list access on a given entity name
     * @param unknown $sRessource
     * @return boolean
     */
    protected function hasListByUserAccess($sRessource)
    {
        return $this->hasListAccess($sRessource);
    }

    /**
     * Get user's CRUD rights
     * @param unknown $sRessource
     * @return mixed|NULL
     */
    protected function getCRUD($sRessource)
    {
        $sRessource = strtolower($sRessource);
        if (! empty($sRessource) && $this->oGroups->hasItem() && $this->oPermissions->count() > 0) {
            if (($oRessource = $this->oRessources->search('name', $sRessource)) !== NULL) {
                if (($oPermission = $this->oPermissions->search('ressource_idressource', $oRessource->idressource)) !== NULL) {
                    return json_decode($oPermission->permission);
                }
            }
        }

        return NULL;
    }

    /**
     * Load current user instance group
     *
     * @return boolean
     * @throws AclException
     */
    private function getUserGroups()
    {
        assert('$this->oUser->isLoaded()');
        $this->oGroups = new \app\Entities\Collection\GroupCollection();
        $oUserGroups = new \app\Entities\Collection\UserGroupCollection();
        try {
            $oUserGroups->loadByParameters(array(
                'user_iduser' => $this->oUser->getId()
            ));
            foreach ($oUserGroups as $oGroup) {
                $this->oGroups->add($this->oGroups->count() + 1, $oGroup);
            }
        } catch (CoreEntityException $oException) {
            throw new AclException('Error: No group found for user');
        }
        return $this->oGroups->hasItem();
    }

    /**
     * Load user instances current permission
     *
     * @return boolean
     */
    private function getPermissions()
    {
        assert('$this->oGroups->hasItem()');

        $this->oPermissions = new \app\Entities\Collection\PermissionCollection();
        try {
            $aGroups = array();
            foreach ($this->oGroups as $oGroup) {
                $aGroups[] = (int) $oGroup->group_idgroup;
            }
            $this->oPermissions->loadByParameters(array(
                'group_idgroup' => $aGroups
            ));
        } catch (CoreEntityException $oException) {}
        return ($this->oPermissions->count() > 0) ? true : false;
    }

    /**
     * Load current user available ressources
     *
     * @throws AclException
     */
    private function getRessources()
    {
        assert('$this->oGroups->hasItem() && $this->oPermissions->count() > 0');

        $this->oRessources = new \app\Entities\Collection\RessourceCollection();
        $aAvailableRessources = array();
        foreach ($this->oPermissions as $oPermission) {
            $aAvailableRessources[] = (int) $oPermission->ressource_idressource;
        }
        if (count($aAvailableRessources) > 0) {
            try {
                $this->oRessources->loadByParameters(array(
                    'idressource' => $aAvailableRessources
                ));
            } catch (CoreEntityException $oException) {}
        }

        return $this->oRessources->count() > 0;
    }
}

class AclException extends \Exception
{
}
