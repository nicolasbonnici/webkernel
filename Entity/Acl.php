<?php
namespace Library\Core\Entity;

use app\Entities\User;
use Library\Core\Acl\AclAbstract;

/**
 * Entities ACL layer
 *
 * @author Nicolas Bonnici <nicolasbonnici@gmail.com>
 */
class Acl extends AclAbstract
{

    /**
     * Ask ACL if user has the right for a given action name
     *
     * @param string $sActionName
     * @return bool
     */
    public function hasAccess($sActionName)
    {
        try {
            if (in_array($sActionName, Crud::$aActionScope) === true) {
                $oRights = $this->getCRUD();
                if (is_null($oRights) === false) {
                    return (bool) ($oRights->get($sActionName) === 1);
                }
            }
            return false;
        } catch (\Exception $oException) {
            return false;
        }
    }
}
