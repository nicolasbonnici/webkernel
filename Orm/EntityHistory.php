<?php
namespace Library\Core\Orm;
use app\Entities\History;
use Library\Core\Exception\CoreException;
use Library\Core\Json\Json;

/**
 * EntityHistory
 */

class EntityHistory {


    /**
     * @var Entity
     */
    protected $oOriginalEntity;

    public function __construct(Entity $oOriginalEntity)
    {
        # Set original entity
        $this->oOriginalEntity = $oOriginalEntity;
    }

    /**
     * Save history on update for historized objects
     *
     * @param array $aUpdatedParameters         Associative array of fieldname => value
     */
    public function save(array $aUpdatedParameters)
    {
        $aBefore = array();
        foreach ($this->oOriginalEntity as $sPropertyName => $mValue) {
            $aBefore[$sPropertyName] = $mValue;
        }

        $aDiffBefore = array_diff($aBefore, $aUpdatedParameters);
        $aDiffAfter = array_diff( $aUpdatedParameters, $aBefore);

        # Avoid empty history record when update method called for nothing
        if (empty($aDiffBefore) === false && empty($aDiffAfter) === false) {

            $oBefore = new Json($aDiffBefore);
            $oAfter = new Json($aDiffAfter);

            /** @var Entity $oHistory */
            $oHistory = new History();
            $oHistory->entity = $this->oOriginalEntity->getEntityName();
            $oHistory->entityId = $this->oOriginalEntity->getId();
            $oHistory->pre_modification = $oBefore->__toString();
            $oHistory->post_modification = $oAfter->__toString();
            $oHistory->modification_date = date('Y-m-d H:i:s');

            return $oHistory->add();
        }

        # Return true anyway since if the diff is empty it's not really an error and no need to throw exception
        return true;
    }

}

class EntityHistoryException extends CoreException
{
    const ERROR_ENTITY_TYPE_MISMATCH = 2;

    public static $aErrors = array(
        self::ERROR_ENTITY_TYPE_MISMATCH => 'Orginal and updated and not from the same type.'
    );
}