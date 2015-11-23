<?php

namespace Library\Core\Tests\Dummy\Entities;

use Library\Core\Entity\Entity;
use Library\Core\Entity\Mapping\MappingAbstract;


/**
 * Dummy Entity
 *
 * Class Dummy
 * @package Library\Core\Tests\Dummy\Entities
 */
class Dummy extends Entity {

    const ENTITY = 'Dummy';
    const TABLE_NAME = 'dummy';
    const PRIMARY_KEY = 'iddummy';

    /**
     * Object caching duration in seconds
     * @var integer
     */
    protected $iCacheDuration = 50;

    /**
     * Entity properties
     * @var bool
     */
    protected $bIsSearchable = true;
    protected $bIsDeletable  = true;
    protected $bIsCacheable  = true;
    protected $bIsHistorized = false;

    /**
     * Mapping configuration
     * @var array
     */
    protected $aMappingConfiguration = array(
        'Library\Core\Tests\Dummy\Entities\Dummy4' => array(
            /**
             * Mandatory parameters
             */
            MappingAbstract::KEY_MAPPING_TYPE => MappingAbstract::MAPPING_ONE_TO_ONE,
            /**
             * @todo optional
             */
            MappingAbstract::KEY_LOAD_BY_DEFAULT => false,
            MappingAbstract::KEY_MAPPED_ENTITY_REFERENCE => 'dummy4_iddummy4'
        ),
        'Library\Core\Tests\Dummy\Entities\Dummy2' => array(
            /**
             * Mandatory parameters
             */
            MappingAbstract::KEY_MAPPING_TYPE => MappingAbstract::MAPPING_ONE_TO_MANY,
            /**
             * @todo optional
             */
            MappingAbstract::KEY_LOAD_BY_DEFAULT         => false,
            MappingAbstract::KEY_SOURCE_ENTITY_REFERENCE => 'dummy_iddummy'
        ),
        'Library\Core\Tests\Dummy\Entities\Dummy3' => array(
            /**
             * Mandatory parameters
             */
            MappingAbstract::KEY_MAPPING_TYPE => MappingAbstract::MAPPING_MANY_TO_MANY,
            MappingAbstract::KEY_MAPPING_TABLE => 'dummyDummy3',
            /**
             * @todo those are optional use Entity::computeForeignKeyName by default if not set on manytoMany
             */
            MappingAbstract::KEY_LOAD_BY_DEFAULT         => false,
            MappingAbstract::KEY_SOURCE_ENTITY_REFERENCE => 'dummy_iddummy',
            MappingAbstract::KEY_MAPPED_ENTITY_REFERENCE => 'dummy3_iddummy3'
        )
    );
}

