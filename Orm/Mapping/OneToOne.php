<?php
namespace Library\Core\Orm\Mapping;

use Library\Core\Orm\Entity;
/**
 * That class perform the 1 - 1 mapping relationship type between two Entity
 *
 * The foreign key to the mapped Entity is stored on the source Entity
 *
 * Class OneToOne
 * @package Library\Core\Orm\Mapping
 */
class OneToOne extends MappingAbstract
{

    protected $aRequiredMappingConfigurationFields = array(
        MappingAbstract::KEY_MAPPING_TYPE,
        MappingAbstract::KEY_LOAD_BY_DEFAULT,
        MappingAbstract::KEY_SOURCE_ENTITY_REFERENCE
    );

    /**
     * Find specific mapped entity
     *
     * @param Entity $oMappedEntity
     * @param array $aParameters
     * @param array $aOrderFields
     * @param array $aLimit
     * @return Entity|null
     * @throws \Library\Core\Orm\EntityException
     */
    public function loadMapped(
        Entity $oMappedEntity,
        array $aParameters = array(),
        array $aOrderFields = array(),
        array $aLimit = array(0, 100)
    )
    {
        $aMappingConf = $this->loadMappingConfiguration(get_class($oMappedEntity));
        if (is_null($aMappingConf) === false && $this->checkMappingConfiguration($aMappingConf) === true) {
            /** @var Entity $oMappedEntity */
            $oMappedEntity = new $oMappedEntity;
            $oMappedEntity->loadByParameters(
                array(
                    $oMappedEntity->getPrimaryKeyName() =>
                        $this->oSourceEntity->$aMappingConf[MappingAbstract::KEY_SOURCE_ENTITY_REFERENCE]
                )
            );

            return $oMappedEntity;
        }
        return null;
    }

    /**
     * Store a mapped entity
     *
     * @param Entity $oMappedEntity
     * @return bool
     */
    public function store(Entity $oMappedEntity)
    {
        $aMappingConf = $this->loadMappingConfiguration(get_class($oMappedEntity));
        if (is_null($aMappingConf) === false && $this->checkMappingConfiguration($aMappingConf) === true) {
            if ($oMappedEntity->add() === true) {
                # Persist reference to create Entity on source Entity
                $this->oSourceEntity->$aMappingConf[MappingAbstract::KEY_SOURCE_ENTITY_REFERENCE] = $oMappedEntity->getId();
                return $this->oSourceEntity->update();
            }
        }
        return false;
    }

}