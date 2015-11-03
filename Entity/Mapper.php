<?php
namespace Library\Core\Entity;

use Library\Core\Entity\Mapping\EntityMappingException;
use Library\Core\Entity\Mapping\ManyToMany;
use Library\Core\Entity\Mapping\MappingAbstract;
use Library\Core\Entity\Mapping\OneToMany;
use Library\Core\Entity\Mapping\OneToOne;

/**
 * Handle all relationship types between Entities, and provide generic usefull methods to load and store mapped Entities
 *
 * Class Mapper
 * @package Library\Core\Orm
 */
class Mapper
{

    /**
     * Source Entity instance
     *
     * @var Entity
     */
    protected $oSourceEntity;

    /**
     * Flag to bypass Entity configuration for the MappingAbstract::KEY_LOAD_BY_DEFAULT value
     *
     * @var bool
     */
    protected $bForceLoad = false;

    /**
     * Source entity mapping configuration
     * @var array
     */
    protected $aMappingConfiguration;

    /**
     * Instance constructor
     */
    public function __construct($bForceLoad = false)
    {
        $this->bForceLoad = $bForceLoad;
    }

    /**
     * Load all mapped entities
     * @return array
     */
    public function load()
    {
        $aMappedEntities = array();
        foreach ($this->aMappingConfiguration as $sLinkedEntity => $aMappingSetup) {
            $oMapper = $this->getMapper($aMappingSetup[MappingAbstract::KEY_MAPPING_TYPE]);
            if (is_null($oMapper) === false && $oMapper instanceof MappingAbstract) {
                $oMapper->load();
                $aMappedEntities[$sLinkedEntity] = $oMapper->getMapping();
            }
        }
        return $aMappedEntities;
    }

    /**
     * Find specific mapped Entity or EntityCollection with parameters
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
        $mLimit = null
    )
    {
        $aMappingSetup = $this->getMappingConfiguration(get_class($oMappedEntity));
        $oMapper = $this->getMapper($aMappingSetup[MappingAbstract::KEY_MAPPING_TYPE]);
        if (is_null($oMapper) === false && $oMapper instanceof MappingAbstract) {
            return $oMapper->loadMapped($oMappedEntity, $aParameters, $aOrderFields, $mLimit);
        }
        return null;
    }

    /**
     * Delete all mapped entities
     */
    public function deleteMapped()
    {
        $aReturns = array();
        foreach ($this->aMappingConfiguration as $sLinkedEntity => $aMappingSetup) {
            $oMapped = $this->loadMapped(new $sLinkedEntity());
            if ($oMapped instanceof Entity) {
                $aReturns[] = $this->delete($oMapped);
            } elseif ($oMapped instanceof EntityCollection) {
                foreach ($oMapped as $oEntity) {
                    $aReturns[] = $this->delete($oEntity);
                }
            } else {
                # NULL case no mapped Entities found
                continue;
            }
        }
        return (bool) (in_array(false, $aReturns) === false);
    }

    /**
     * Store a mapped entity
     *
     * @param $oMappedEntity
     * @return bool
     */
    public function store(Entity $oMappedEntity)
    {
        $aMappingSetup = $this->getMappingConfiguration($oMappedEntity->getChildClass());
        $oMapper = $this->getMapper($aMappingSetup[MappingAbstract::KEY_MAPPING_TYPE]);
        if (is_null($oMapper) === false && $oMapper instanceof MappingAbstract) {
            return $oMapper->store($oMappedEntity);
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
        $aMappingSetup = $this->getMappingConfiguration($oMappedEntity->getChildClass());
        $oMapper = $this->getMapper($aMappingSetup[MappingAbstract::KEY_MAPPING_TYPE]);
        if (is_null($oMapper) === false && $oMapper instanceof MappingAbstract) {
            return $oMapper->delete($oMappedEntity);
        }
        return false;
    }

    /**
     * Retrieve an Entity mapping configuration
     *
     * @param $sEntityClassName
     * @return mixed Entity|EntityCollection
     */
    private function getMappingConfiguration($sEntityClassName = null)
    {
        return (isset($this->aMappingConfiguration[$sEntityClassName]) === true )
            ? $this->aMappingConfiguration[$sEntityClassName]
            : null;
    }

    /**
     * Factory to instantiated the correct mapper for a given relationship type
     *
     * @param string $sMappingType
     * @return MappingAbstract|null
     */
    private function getMapper($sMappingType)
    {
        $oMapper = null;
        switch ($sMappingType) {
            case (MappingAbstract::MAPPING_ONE_TO_ONE) :
                $oMapper = new OneToOne($this->oSourceEntity);
                break;
            case (MappingAbstract::MAPPING_ONE_TO_MANY) :
                $oMapper = new OneToMany($this->oSourceEntity);
                break;
            case (MappingAbstract::MAPPING_MANY_TO_MANY) :
                $oMapper = new ManyToMany($this->oSourceEntity);
                break;
        }
        return $oMapper;
    }

    /**
     * @return Entity
     */
    public function getSourceEntity()
    {
        return $this->oSourceEntity;
    }

    /**
     * @param Entity $oSourceEntity
     */
    public function setSourceEntity(Entity $oSourceEntity)
    {
        $this->oSourceEntity = $oSourceEntity;
        $this->aMappingConfiguration = $this->oSourceEntity->getMappingConfiguration();
    }

    /**
     * @return boolean
     */
    public function getForceLoad()
    {
        return $this->bForceLoad;
    }

    /**
     * @param boolean $bForceLoad
     */
    public function setForceLoad($bForceLoad)
    {
        $this->bForceLoad = (bool) $bForceLoad;
    }

}
