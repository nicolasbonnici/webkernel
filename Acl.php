<?php
namespace Library\Core;

use bundles\user\Entities\User;
/**
 * ACL couch layer to manage CRUD access to entities using permissions setted to user's groups
 *
 *
 * @author Nicolas Bonnici <nicolasbonnici@gmail.com>
 *
 */
abstract class Acl
{

    /**
     * User instance
     *
     * @var \bundles\user\Entities\User
     */
    protected $oUser;

    /**
     * Entity
     *
     * @var \bundles\user\Entities\Entity
     */
    protected $oEntity;

    /**
     * User instance current permissions
     *
     * Permission's permission attribute structure:
     * { "create": 1, "read": 1,  "update": 1, "delete": 1, "list":1 }
     *
     * @var \bundles\user\Entities\Collection\PermissionCollection
     */
    protected $oPermissions;

    /**
     * User instance current group
     *
     * @var \bundles\user\Entities\Mapping\Collection\UserGroupCollection
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
     * @param \bundles\user\Entities\User $oUser
     * @throws AclException
     */
    protected $aAvailableRessources;

    /**
     * Instance constructor
     * @param \bundles\user\Entities\User $oUser
     * @throws AclException
     */
    public function __construct(User $oUser)
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
        if (
            ! empty($sRessource) &&
            (($oRights = $this->getCRUD($sRessource)) !== NULL) &&
            isset($oRights->create)
        ) {
            return ($oRights->create === 1);
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
        if (
            ! empty($sRessource) &&
            (($oRights = $this->getCRUD($sRessource)) !== NULL) &&
            isset($oRights->read)
        ) {
            return ($oRights->read === 1);
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
        if (
            ! empty($sRessource) &&
            (($oRights = $this->getCRUD($sRessource)) !== NULL) &&
            isset($oRights->update)
        ) {
            return ($oRights->update === 1);
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
        if (
            ! empty($sRessource) &&
            (($oRights = $this->getCRUD($sRessource)) !== NULL) &&
            isset($oRights->delete)
        ) {
            return ($oRights->delete === 1);
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
        if (
            ! empty($sRessource) &&
            (($oRights = $this->getCRUD($sRessource)) !== NULL) &&
            isset($oRights->list)
        ) {
            return ($oRights->list === 1);
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
        $this->oGroups = new \bundles\user\Entities\Mapping\Collection\UserGroupCollection();
        $oUserGroups = new \bundles\user\Entities\Mapping\Collection\UserGroupCollection();
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

        $this->oPermissions = new \bundles\adm\Entities\Collection\PermissionCollection();
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

        $this->oRessources = new \bundles\adm\Entities\Collection\RessourceCollection();
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
