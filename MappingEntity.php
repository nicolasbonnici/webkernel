<?php
namespace Library\Core;

/**
 * Mapping entities component for on the fly ORM Crud managment of the Entity component
 *
 * @author niko <nicolasbonnici@gmail.com>
 *
 * @dependancy \Library\Core\Entity
 * @dependancy \Library\Core\EntitiesCollection
 * @dependancy \Library\Core\Validator
 * @dependancy \Library\Core\Cache
 * @dependancy \Library\Core\Database
 */
abstract class MappingEntity extends Entity
{
    /**
     * Source Entity
     * @var Library\Core\Entity
     */
    private $oSourceEntity;

    /**
     * Mapping Entity
     * @var Library\Core\MappingEntity
     */
    private $oMappingEntity;

    /**
     * Mapped entities collection to the instantiated source Entity
     * @var Library\Core\EntitiesCollection
     */
    protected $oMappedEntities;

    /**
     * Mapping types between entities
     * @var array
     */
    protected $AllowedaMappingTypes = array(
        'oneToOne',
        'oneToMany'
    );

    /**
     * Extended class attributes
     */

    /**
     * The mapping type must be handle in the $this->AllowedaMappingTypes
     * /!\ This attribute MUST be declared on extended class
     * @var string
     */
    protected $sMappingType;

    /**
     * Optional: Just for the "oneTonOne" mapping type
     * This attribute is mostly for the foreign key attribute on one mapped Entities
     * Must be formated like: [entityname]_id[entityname]
     *
     * @var string
     */
    protected $sSourceEntityMappingAttributeField;

    /**
     * Instance constructor
     * @param \Library\Core\Entity $oSourceEntity
     * @param \Library\Core\Entity $oMappedEntity
     * @throws MappingEntityException
     */
    public function __construct($oSourceEntity, $oMappedEntity)
    {

        if($oSourceEntity instanceof \Library\Core\Entity || ! $oSourceEntity->isLoaded()) {

            throw new MappingEntityException('The source Entity is invalid instance of the Library\Core\Entity componentor OR has no data loaded.');

        } elseif (! $oMappedEntity instanceof \Library\Core\Entity) {

            throw new MappingEntityException('The mapped Entity is invalid instance of the Library\Core\Entity component.');

        } elseif (
            ($sMappedEntityCollectionClassName = App::ENTITIES_COLLECTION_NAMESPACE . get_class($oMappedEntity) . 'Collection') &&
            ! class_exists($sMappedEntityCollectionClassName)
        )  {

            throw new MappingEntityException('No EntitiesCollection found for the mapped Entity instance.');

        } elseif (
            ($sMappingEntity = App::MAPPING_ENTITIES_NAMESPACE . get_class($oSourceEntity) . get_class($oMappedEntity)) &&
            ! class_exists($sMappingEntity)
        ) {

            throw new MappingEntityException('No MappingEntity found for the mapping between those two entities.');

        } else {

            // @todo usefull ??
            $this->oSourceEntity = $oSourceEntity;

            // Instantiate MappingEntity then query for relationship between those two entities
            $this->oMappingEntity = new $sMappingEntity;
        }
    }

    /**
     * Load Mapped entities to the source Entity provided at instance
     * @throws MappingEntityException
     */
    private function loadMappedEntities()
    {
        if (empty($this->sMappingType)) {
            throw new MappingEntityException('No mapping type founded, you must set an allowed mapping type.');
        } elseif (! in_array($this->sMappingType, $this->AllowedaMappingTypes)){
            throw new MappingEntityException('The mapping type is not currently supported: ' . $this->sMappingType);
        } elseif ($this->sMappingType === 'oneToOne' && empty($this->sSourceEntityMappingAttributeField)) {
            throw new MappingEntityException('The mapping type "oneToOne" need a foreign key attribute on source entity.');
        } else {

            if ($this->sMappingType === 'oneToOne') {
                return $this->loadMappedEntity();
            } elseif ($this->sMappingType === 'oneToMany') {

                // @todo

            }

        }
    }

    private function loadMappedEntity()
    {
        if (empty($this->sSourceEntityMappingAttributeField)) {
            throw new MappingEntityException('The mapping type "oneToOne" need a foreign key attribute on source entity.');
        } else {

            // @todo

        }
    }

    /**
     * Mapped entities accessor
     * @return \Library\Core\Library\Core\EntitiesCollection
     */
    public function getMappedEntities()
    {
        return $this->oMappedEntities;
    }
}

class MappingEntityException extends \Exception
{
}
