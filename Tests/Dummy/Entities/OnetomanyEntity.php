<?php

namespace Library\Core\Tests\Dummy\Entities;

/**
 * Dummy Entity for unit tests
 *
 * @author infradmin
 */
class OnetomanyEntity extends \Library\Core\Orm\Entity {

    const ENTITY = 'OnetomanyEntity';
    const TABLE_NAME = 'onetomany';
    const PRIMARY_KEY = 'idOnetomany';

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

}

