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
     * @param Entity $oUpdatedEntity
     */
    public function save(Entity $oUpdatedEntity)
    {
        if ($this->oOriginalEntity->getEntityName() !== $oUpdatedEntity->getEntityName()) {
            throw new EntityHistoryException(
                EntityHistoryException::getError(EntityHistoryException::ERROR_ENTITY_TYPE_MISMATCH),
                EntityHistoryException::ERROR_ENTITY_TYPE_MISMATCH
            );
        }

        $aBefore = array();
        $aAfter = array();

        foreach ($this->oOriginalEntity as $sPropertyName => $mValue) {
            if ($mValue != $oUpdatedEntity->{$sPropertyName}) {
                $aBefore[$sPropertyName] = $mValue;
                $aAfter[$sPropertyName] = $oUpdatedEntity->{$sPropertyName};
            }
        }

        $oHistory = new History();
        $oHistory->entity = $this->oOriginalEntity->getEntityName();
        $oHistory->entityId = $this->oOriginalEntity->getId();
        $oHistory->before = new Json($aBefore);
        $oHistory->after = new Json($aAfter);
        $oHistory->modification_date = date('Y-m-d');
        $oHistory->modification_time = date('H:i:s');

        return $oHistory->add();
    }

}

class EntityHistoryException extends CoreException
{
    const ERROR_ENTITY_TYPE_MISMATCH = 2;

    public static $aErrors = array(
        self::ERROR_ENTITY_TYPE_MISMATCH => 'Orginal and updated and not from the same type.'
    );
}