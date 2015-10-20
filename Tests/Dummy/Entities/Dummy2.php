<?php

namespace Library\Core\Tests\Dummy\Entities;

/**
 * Dummy Entity mapped on many to one to Dummy
 *
 * @author infradmin
 */
class Dummy2 extends \Library\Core\Entity\Entity
{

    const ENTITY = 'Dummy2';
    const TABLE_NAME = 'dummy2';
    const PRIMARY_KEY = 'iddummy2';

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
    protected $bIsDeletable = true;
    protected $bIsCacheable = true;
    protected $bIsHistorized = false;

}