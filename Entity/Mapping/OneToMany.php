<?php
namespace Library\Core\Entity\Mapping;


use Library\Core\Entity\Entity;
use Library\Core\Entity\EntityCollection;

/**
 * That class perform the 1 - n mapping relationship
 *
 * The foreign key is store on the mapped object
 *
 * Class OneToMany
 * @package Library\Core\Orm\Mapping
 */
class OneToMany extends MappingAbstract
{

    protected $aRequiredMappingConfigurationFields = array(
        MappingAbstract::KEY_MAPPING_TYPE
    );

    /**
     * Find specific mapped entities
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
            $sMappedCollectionClassName = $oMappedEntity->computeCollectionClassName();
            /** @var EntityCollection $oMappedEntityCollection */
            $oMappedEntityCollection = new $sMappedCollectionClassName;

            # Build optional source foreign key name parameter
            if (isset($aMappingConf[MappingAbstract::KEY_SOURCE_ENTITY_REFERENCE]) === true) {
                $sForeignKeyName = $aMappingConf[MappingAbstract::KEY_SOURCE_ENTITY_REFERENCE];
            } else {
                $sForeignKeyName = $this->oSourceEntity->computeForeignKeyName();
            }

            $aParameter = array_merge(
                $aParameters,
                array(
                    $sForeignKeyName => $this->oSourceEntity->getId()
                )
            );

            $oMappedEntityCollection->loadByParameters(
                $aParameter,
                $aOrderFields,
                $mLimit
            );

            return $oMappedEntityCollection;
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

            # Build optional source foreign key name parameter
            if (isset($aMappingConf[MappingAbstract::KEY_SOURCE_ENTITY_REFERENCE]) === true) {
                $sForeignKeyName = $aMappingConf[MappingAbstract::KEY_SOURCE_ENTITY_REFERENCE];
            } else {
                $sForeignKeyName = $this->oSourceEntity->computeForeignKeyName();
            }

            $oMappedEntity->$sForeignKeyName = $this->oSourceEntity->getId();
            return $oMappedEntity->add();
        }
        return false;
    }

    /**
     * Delete a mapped entity
     *
     * @param Entity $oMappedEntity
     * @return bool
     * @throws \Library\Core\Entity\EntityException
     */
    public function delete(Entity $oMappedEntity)
    {
        # On this mapping simply delete mapped entity since the reference is store on it
        return $oMappedEntity->delete();
    }

}