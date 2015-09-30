<?php
namespace Library\Core\Orm;

use Library\Core\Orm\Mapping\EntityMappingException;
use Library\Core\Orm\Mapping\ManyToMany;
use Library\Core\Orm\Mapping\MappingAbstract;
use Library\Core\Orm\Mapping\OneToMany;
use Library\Core\Orm\Mapping\OneToOne;

/**
 * Handle all relationship types between Entities, and provide generic usefull methods to load and store mapped Entities
 *
 * Class EntityMapper
 * @package Library\Core\Orm
 */
class EntityMapper
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
     * @throws \Library\Core\Orm\EntityException
     */
    public function loadMapped(
        Entity $oMappedEntity,
        array $aParameters = array(),
        array $aOrderFields = array(),
        array $aLimit = array(0, 100)
    )
    {
        $aMappingSetup = $this->getMappingConfiguration(get_class($oMappedEntity));
        $oMapper = $this->getMapper($aMappingSetup[MappingAbstract::KEY_MAPPING_TYPE]);
        if (is_null($oMapper) === false && $oMapper instanceof MappingAbstract) {
            return $oMapper->loadMapped($oMappedEntity, $aParameters, $aOrderFields, $aLimit);
        }
        return null;
    }

    /**
     * Store a mapped entity
     *
     * @param $oMappedEntity
     * @return bool
     */
    public function store($oMappedEntity)
    {
        $aMappingSetup = $this->getMappingConfiguration(get_class($oMappedEntity));
        $oMapper = $this->getMapper($aMappingSetup[MappingAbstract::KEY_MAPPING_TYPE]);
        if (is_null($oMapper) === false && $oMapper instanceof MappingAbstract) {
            return $oMapper->store($oMappedEntity);
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
    public function setForceLoad()
    {
        return $this->bForceLoad;
    }

    /**
     * @param boolean $bForceLoad
     */
    public function getForceLoad($bForceLoad)
    {
        $this->bForceLoad = (bool) $bForceLoad;
    }

}
