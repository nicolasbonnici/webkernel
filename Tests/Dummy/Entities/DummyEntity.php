<?php

namespace Library\Core\Tests\Dummy\Entities;

use Library\Core\Orm\EntityMapper;

/**
 * Dummy Entity for unit tests
 *
 * @author infradmin
 */
class DummyEntity extends \Library\Core\Orm\Entity {

    const ENTITY = 'DummyEntity';
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
    protected $aMappedEntities = array(
        '\bundles\tag\Entities\Collection\TagCollection' => array(
            'loadByDefault' => false,
            'relationship' 	=> EntityMapper::MAPPING_ONE_TO_MANY,
            'mappingEntity' => 'bundles\blog\Entities\Mapping\Collection\PostTagCollection',
            'mappedByField' => 'post_idpost',
            'foreignField' 	=> 'tag_idtag'
        )
    );
}

