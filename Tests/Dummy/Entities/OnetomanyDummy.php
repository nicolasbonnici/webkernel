<?php

namespace Library\Core\Tests\Dummy\Entities;

/**
 * Dummy Entity for unit tests
 *
 * @author infradmin
 */
class OnetomanyDummy extends \Library\Core\Orm\Entity {

    const ENTITY = 'OnetomanyDummy';
    const TABLE_NAME = 'onetomanyDummy';
    const PRIMARY_KEY = 'idonetomanydummy';

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

