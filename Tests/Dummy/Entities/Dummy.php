<?php

namespace Library\Core\Tests\Dummy\Entities;

use Library\Core\Entity\Mapping\MappingAbstract;

/**
 * Dummy Entity for unit tests
 *
 * @author infradmin
 */
class Dummy extends \Library\Core\Entity\Entity {

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
            MappingAbstract::KEY_MAPPING_TYPE    => MappingAbstract::MAPPING_ONE_TO_ONE,
            MappingAbstract::KEY_LOAD_BY_DEFAULT => false,
            MappingAbstract::KEY_MAPPED_ENTITY_REFERENCE => 'dummy4_iddummy4'
        ),
        'Library\Core\Tests\Dummy\Entities\Dummy2' => array(
            MappingAbstract::KEY_MAPPING_TYPE            => MappingAbstract::MAPPING_ONE_TO_MANY,
            MappingAbstract::KEY_LOAD_BY_DEFAULT         => false,
            MappingAbstract::KEY_SOURCE_ENTITY_REFERENCE => 'dummy_iddummy'
        ),
        'Library\Core\Tests\Dummy\Entities\Dummy3' => array(
            MappingAbstract::KEY_MAPPING_TYPE 	         => MappingAbstract::MAPPING_MANY_TO_MANY,
            MappingAbstract::KEY_LOAD_BY_DEFAULT         => false,
            MappingAbstract::KEY_MAPPING_TABLE           => 'dummyDummy3',
            MappingAbstract::KEY_SOURCE_ENTITY_REFERENCE => 'dummy_iddummy',
            MappingAbstract::KEY_MAPPED_ENTITY_REFERENCE => 'dummy3_iddummy3'
        )
    );
}

