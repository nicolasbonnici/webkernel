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
            EntityMapper::KEY_LOAD_BY_DEFAULT   => false,
            EntityMapper::KEY_MAPPING_TYPE 	    => EntityMapper::MAPPING_ONE_TO_MANY,
            EntityMapper::KEY_MAPPED_BY_ENTITY  => 'bundles\blog\Entities\Mapping\Collection\PostTagCollection',
            EntityMapper::KEY_MAPPED_BY_FIELD   => 'post_idpost',
            EntityMapper::KEY_FOREIGN_FIELD 	=> 'tag_idtag'
        )
    );
}

