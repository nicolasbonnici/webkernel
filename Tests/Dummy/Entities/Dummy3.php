<?php

namespace Library\Core\Tests\Dummy\Entities;

/**
 * Dummy Entity mapped on many to many to Dummy
 *
 * @author infradmin
 */
class Dummy3 extends \Library\Core\Orm\Entity {

    const ENTITY = 'Dummy3';
    const TABLE_NAME = 'dummy3';
    const PRIMARY_KEY = 'iddummy3';

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

