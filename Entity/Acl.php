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

    const ACTION_CREATE = 'create';
    const ACTION_READ   = 'read';
    const ACTION_UPDATE = 'update';
    const ACTION_DELETE = 'delete';
    const ACTION_LIST   = 'list';

    public static $aActionScope = array(
        self::ACTION_CREATE,
        self::ACTION_READ,
        self::ACTION_UPDATE,
        self::ACTION_DELETE,
        self::ACTION_LIST,
    );

    public function hasAccess($sActionName)
    {
        try {
            if (in_array($sActionName, self::$aActionScope) === true) {
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
