<?php
namespace Library\Core\Entity\Mapping;
use Library\Core\Database\Pdo;
use Library\Core\Database\Query\Delete;
use Library\Core\Database\Query\Insert;
use Library\Core\Database\Query\Operators;
use Library\Core\Database\Query\Select;
use Library\Core\Entity\Entity;
use Library\Core\Entity\EntityCollection;


/**
 * That handle the n - n relationship between entities
 *
 * The foreign keys are store a mapping table
 *
 * Class ManyToMany
 * @package Library\Core\Orm\Mapping
 */
class ManyToMany extends MappingAbstract
{

    protected $aRequiredMappingConfigurationFields = array(
        MappingAbstract::KEY_MAPPING_TYPE,
        MappingAbstract::KEY_LOAD_BY_DEFAULT,
        MappingAbstract::KEY_MAPPING_TABLE,
        MappingAbstract::KEY_SOURCE_ENTITY_REFERENCE
    );

    /**
     * Find specific mapped entities using a mapping table
     *
     * @param Entity $oMappedEntity
     * @param array $aParameters
     * @param array $aOrderFields
     * @param array $aLimit
     * @return Entity|null
     * @throws \Library\Core\Entity\EntityException
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

            # Build the mapping table query
            $oSelect = new Select();
            $oSelect->addColumn($aMappingConf[self::KEY_MAPPED_ENTITY_REFERENCE])
                ->setFrom($aMappingConf[self::KEY_MAPPING_TABLE])
                ->addWhereCondition(Operators::equal($aMappingConf[self::KEY_SOURCE_ENTITY_REFERENCE]));
            $oStatement = Pdo::dbQuery(
                $oSelect->build(),
                array($aMappingConf[self::KEY_SOURCE_ENTITY_REFERENCE] => $this->oSourceEntity->getId())
            );

            if ($oStatement !== false) {

                $aIds = $oStatement->fetchAll(\PDO::FETCH_COLUMN, 0);
                if (count($aIds) === 0) {
                    return null;
                }

                /** @var Entity $oMappedEntity */
                $oMappedEntity = new $oMappedEntity;
                $sMappedEntityCollectionClassName = $oMappedEntity->computeCollectionClassName();
                /** @var EntityCollection $oMappedCollection */
                $oMappedCollection = new $sMappedEntityCollectionClassName;

                # Build parameters
                $aParameters[$oMappedEntity->getPrimaryKeyName()] = $aIds;

                $oMappedCollection->loadByParameters(
                    $aParameters,
                    $aOrderFields,
                    $aLimit
                );

                return $oMappedCollection;
            }

        }
        return null;
    }

    /**
     * Store a mapped entity
     *
     * @todo Transactional mode needed here
     *
     * @param Entity $oMappedEntity
     * @return bool
     */
    public function store(Entity $oMappedEntity)
    {
        $aMappingConf = $this->loadMappingConfiguration(get_class($oMappedEntity));
        if (is_null($aMappingConf) === false && $this->checkMappingConfiguration($aMappingConf) === true) {
            if($oMappedEntity->add() === true) {
                $oInsert = new Insert();
                $oInsert->setFrom($aMappingConf[self::KEY_MAPPING_TABLE])
                    ->addParameter(
                        $aMappingConf[self::KEY_SOURCE_ENTITY_REFERENCE],
                        $this->oSourceEntity->getId()
                    )
                    ->addParameter(
                        $aMappingConf[self::KEY_MAPPED_ENTITY_REFERENCE],
                        $oMappedEntity->getId()
                    );

                $oStatement = Pdo::dbQuery(
                    $oInsert->build(),
                    array(
                        $aMappingConf[self::KEY_SOURCE_ENTITY_REFERENCE] => $this->oSourceEntity->getId(),
                        $aMappingConf[self::KEY_MAPPED_ENTITY_REFERENCE] => $oMappedEntity->getId()
                    )
                );

                if ($oStatement !== false) {
                    return true;
                }
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

            # Start mysql transactional mode
            Pdo::beginTransaction();
            try {
                # First delete mapping record then the Entity itself
                $oDelete = new Delete();
                $oDelete->setFrom($aMappingConf[MappingAbstract::KEY_MAPPING_TABLE], true)
                    ->addWhereCondition(Operators::equal($aMappingConf[MappingAbstract::KEY_SOURCE_ENTITY_REFERENCE], false))
                    ->addWhereCondition(Operators::equal($aMappingConf[MappingAbstract::KEY_MAPPED_ENTITY_REFERENCE], false));

                $oStatement = Pdo::dbQuery(
                    $oDelete->build(),
                    array(
                        $aMappingConf[MappingAbstract::KEY_SOURCE_ENTITY_REFERENCE],
                        $aMappingConf[MappingAbstract::KEY_MAPPED_ENTITY_REFERENCE]
                    )
                );

                if ($oStatement !== false && $oMappedEntity->delete() === true) {
                    return Pdo::commit();
                }
                Pdo::rollback();
            } catch (\Exception $oException) {
                Pdo::rollback();
                return false;
            }
        }
        return false;
    }

}