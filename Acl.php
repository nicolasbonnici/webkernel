<?php

namespace Library\Core;

/**
 * @author Nicolas Bonnici <nicolasbonnici@gmail.com>
 *
 * ACL couch layer
 * Manage CRUD access to entities
 */
abstract class Acl { // @todo A la ligne pour PSR

    /**
     * User instance
     * @var \app\Entities\User
     */
    protected $oUser;

    /**
     * Entity
     * @var \app\Entities\Entity
     */
    protected $oEntity;

    /**
     * User instance current permissions
     * @var \app\Entities\Collection\PermissionCollection
     */
    protected $oPermissions;

    /**
     * User instance current role
     * @var \app\Entities\Role
     */
    protected $oRole;

    /**
     * An array to store already parsed rights
     * @todo store directly json objects in cache
     *
     * @var array
     */
    protected $aRights = array();

    /**
     * Availables ressources
     * @param \app\Entities\User $oUser
     * @throws CoreAclException
     */
    protected $aAvailableRessources;

    public function __construct(\app\Entities\User $oUser)
    {
        if (!$oUser->isLoaded()) {
            throw new CoreAclException(__CLASS__ . ' Empty user instance provided.');
        } else {
            $this->oUser = $oUser;
            $this->loadRole();
            if ($this->loadPermissions()) {
                $this->loadRessources();
            }
        }

        return;
    }


    protected function hasCreateAccess($sRessource) { // @todo A la ligne pour PSR
        if (
            !empty($sRessource) &&
            ( ($oRights = $this->getCRUD($sRessource)) !== NULL)
        ){
            return $oRights->create === 1;
        }

        return false;
    }

    protected function hasReadAccess($sRessource) { // @todo A la ligne pour PSR
        if (
            !empty($sRessource) &&
            ( ($oRights = $this->getCRUD($sRessource)) !== NULL)
        ){
            return $oRights->read === 1;
        }

        return false;
    }

    protected function hasUpdateAccess($sRessource) { // @todo A la ligne pour PSR
        if (
            !empty($sRessource) &&
            ( ($oRights = $this->getCRUD($sRessource)) !== NULL)
        ){
            return $oRights->update === 1;
        }

        return false;
    }

    protected function hasDeleteAccess($sRessource) { // @todo A la ligne pour PSR
        if (
            !empty($sRessource) &&
            ( ($oRights = $this->getCRUD($sRessource)) !== NULL)
        ){
            return $oRights->delete === 1;
        }

        return false;
    }

    protected function hasListAccess($sRessource)
    {
        return $this->hasReadAccess($sRessource);
    }

    protected function hasListByUserAccess($sRessource)
    {
        return $this->hasReadAccess($sRessource);
    }

    protected function getCRUD($sRessource)
    {
        if (!empty($sRessource) && $this->oRole->isLoaded() && $this->oPermissions->count() > 0) {
            if ( ($oRessource = $this->oRessources->search('name', $sRessource)) !== NULL ) {
                if ( ($oPermission = $this->oPermissions->search('ressource_idressource', $oRessource->idressource)) !== NULL) {
                    return json_decode($oPermission->permission);
                }
            }
        }

        return NULL;
    }

    /**
     * Load current user instance role
     *
     * @return boolean
     * @throws CoreAclException
     */
    private function loadRole() // @todo Méthode get et non load
    {
        $this->oRole = new \app\Entities\Role();
        try {
            $this->oRole->loadByParameters(array(
                'idrole' => $this->oUser->role_idrole
            ));
        } catch (CoreEntityException $oException) {
            throw new CoreAclException('Error: No role found for user');
        }
        return $this->oRole->isLoaded();
    }

    /**
     * Load user instances current permission
     *
     * @return boolean
     */
    private function loadPermissions() // @todo Méthode get et non load
    {
        assert('$this->oRole->isLoaded()');

        $this->oPermissions = new \app\Entities\Collection\PermissionCollection();
        try {
            $this->oPermissions->loadByParameters(array(
                'role_idrole' => $this->oRole->idrole
            ));
        } catch (CoreEntityException $oException) {}
        return ($this->oPermissions->count() > 0) ? true : false;
    }

    /**
     * Load current user available ressources
     *
     * @throws CoreAclException
     */
    private function loadRessources() // @todo Méthode get et non load
    {
        assert('$this->oRole->isLoaded() && $this->oPermissions->count() > 0');

        $this->oRessources = new \app\Entities\Collection\RessourceCollection();
        $aAvailableRessources = array();
        foreach ($this->oPermissions as $oPermission) {
            $aAvailableRessources[] = $oPermission->getId();
        }
        if (count($aAvailableRessources) > 0) {
            try {
                $this->oRessources->loadByIds($aAvailableRessources);
            } catch (CoreEntityException $oException) {}
        }

        return $this->oRessources->count() > 0;
    }
}

class CoreAclException extends \Exception {}

?> // @todo Supprimer PSR
