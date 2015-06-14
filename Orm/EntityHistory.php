<?php
namespace Core\Orm;

/**
 * EntityHistory
 */

class EntityHistory {


    /**
     * Save history on update for historized objects
     *
     * @ŧodo
     *
     * @param $oOriginalObject          Original object before update
     */
    protected function saveHistory($oOriginalObject)
    {
        $aBefore = array();
        $aAfter = array();

        foreach ($this as $sPropertyName => $mValue) {
            if ($mValue != $oOriginalObject->{$sPropertyName}) {
                $aBefore[$sPropertyName] = $oOriginalObject->{$sPropertyName};
                $aAfter[$sPropertyName] = $mValue;
            }
        }

        $oEntityHistory = new \app\Entities\EntityHistory();
        $oEntityHistory->classe = substr($this->sChildClass, 3);
        $oEntityHistory->idobjet = $this->{static::PRIMARY_KEY};
        $oEntityHistory->avant = json_encode($aBefore);
        $oEntityHistory->apres = json_encode($aAfter);
        $oEntityHistory->date_modif = date('Y-m-d');
        $oEntityHistory->time_modif = date('H:i:s');
        //$oEntityHistory->iduser = \model\UserSession::getInstance()->getUserId();
        $oEntityHistory->add();
    }

}