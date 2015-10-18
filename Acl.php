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
    protected $oPermissions;

    /**
     * User instance current group
     *
     * @var UserGroupCollection
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
            throw new AclException(get_called_class() . ' Empty user instance provided.');
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
        } catch (\Exception $oException) {
            throw new AclException('Error: Unable to load group for given user');
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
        } catch (AclException $oException) {
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
            } catch (AclException $oException) {}
        }

        return $this->oRessources->count() > 0;
    }
}

class AclException extends \Exception
{
}
