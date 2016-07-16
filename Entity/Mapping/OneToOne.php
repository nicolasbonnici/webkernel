<?php
namespace Library\Core\Entity\Mapping;

use Library\Core\Entity\Entity;
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
        MappingAbstract::KEY_MAPPING_TYPE
    );

    /**
     * Find specific mapped entity
     *
     * @param Entity $oMappedEntity
     * @param array $aParameters
     * @param array $aOrderFields
     * @param array $mLimit
     * @return Entity|null
     * @throws \Library\Core\Entity\EntityException
     */
    public function loadMapped(
        Entity $oMappedEntity,
        array $aParameters = array(),
        array $aOrderFields = array(),
        $mLimit = null
    )
    {
        $aMappingConf = $this->loadMappingConfiguration(get_class($oMappedEntity));
        if (is_null($aMappingConf) === false && $this->checkMappingConfiguration($aMappingConf) === true) {
            /** @var Entity $oMappedEntity */
            $oMappedEntity = new $oMappedEntity;

            $oMappedEntity->loadByParameters(
                array(
                    $oMappedEntity->getPrimaryKeyName() =>
                    (isset($aMappingConf[MappingAbstract::KEY_MAPPED_ENTITY_REFERENCE]) === true)
                        ? $this->oSourceEntity->{$aMappingConf[MappingAbstract::KEY_MAPPED_ENTITY_REFERENCE]}
                        : $this->oSourceEntity->{$oMappedEntity->computeForeignKeyName()}
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
            if ($oMappedEntity->create() === true) {
                # Build optional mapped foreign key name parameter
                if (isset($aMappingConf[MappingAbstract::KEY_MAPPED_ENTITY_REFERENCE]) === true) {
                    $sForeignKeyName = $aMappingConf[MappingAbstract::KEY_MAPPED_ENTITY_REFERENCE];
                } else {
                    $sForeignKeyName = $this->oSourceEntity->computeForeignKeyName();
                }

                # Persist reference to create Entity on source Entity
                $this->oSourceEntity->$sForeignKeyName = $oMappedEntity->getId();

                return $this->oSourceEntity->update();
            }
        }
        return false;
    }

    /**
     * Delete a mapped Entity
     *
     * @param Entity $oMappedEntity
     * @return bool
     */
    public function delete(Entity $oMappedEntity)
    {
        $aMappingConf = $this->loadMappingConfiguration($oMappedEntity->getChildClass());
        if (is_null($aMappingConf) === false && $this->checkMappingConfiguration($aMappingConf) === true) {
            if ($oMappedEntity->delete() === true) {

                # Build optional mapped foreign key name parameter
                if (isset($aMappingConf[MappingAbstract::KEY_MAPPED_ENTITY_REFERENCE]) === true) {
                    $sForeignKeyName = $aMappingConf[MappingAbstract::KEY_MAPPED_ENTITY_REFERENCE];
                } else {
                    $sForeignKeyName = $this->oSourceEntity->computeForeignKeyName();
                }

                # If mapped entity was deleted then update source Entity to remove reference
                $this->oSourceEntity->$sForeignKeyName = null;
                return $this->oSourceEntity->update();
            }
        }
        return false;
    }


}