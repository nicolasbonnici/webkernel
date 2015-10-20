<?php

namespace Library\Core\Tests\Dummy\Entities;

/**
 * Dummy Entity mapped on one to one to Dummy
 *
 * @author infradmin
 */
class Dummy4 extends \Library\Core\Entity\Entity {

    const ENTITY = 'Dummy4';
    const TABLE_NAME = 'dummy4';
    const PRIMARY_KEY = 'iddummy4';

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

