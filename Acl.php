<?php
namespace Library\Core;

use app\Entities\Collection\PermissionCollection;
use app\Entities\Collection\RessourceCollection;
use app\Entities\Mapping\Collection\UserGroupCollection;
use app\Entities\User;

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
     * @throws AclException
     * @return boolean
     */
    private function getUserGroups()
    {
        assert('$this->oUser->isLoaded()');
        $this->oGroups = new UserGroupCollection();
        try {
            $this->oGroups->loadByParameters(array(
                'user_iduser' => $this->oUser->getId()
            ));
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
        try {
        	$this->oPermissions = new PermissionCollection();
	        if ($this->oGroups->hasItem()) {
		        
	            $aGroups = array();
	            foreach ($this->oGroups as $oGroup) {
	                $aGroups[] = (int) $oGroup->group_idgroup;
	            }
	            $this->oPermissions->loadByParameters(array(
	                'group_idgroup' => $aGroups
	            ));
	        }
	        return ($this->oPermissions->count() > 0) ? true : false;
        } catch (CoreEntityException $oException) {
        	return false;
        }
    }

    /**
     * Load current user available ressources
     *
     * @throws AclException
     */
    private function getRessources()
    {
        assert('$this->oGroups->hasItem() && $this->oPermissions->count() > 0');

        $this->oRessources = new RessourceCollection();
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
