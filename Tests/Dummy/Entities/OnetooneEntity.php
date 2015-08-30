<?php

namespace Library\Core\Tests\Dummy\Entities;

/**
 * Dummy Entity for unit tests
 *
 * @author infradmin
 */
class OnetooneEntity extends \Library\Core\Orm\Entity
{

    const ENTITY = 'OnetooneEntity';
    const TABLE_NAME = 'onetoone';
    const PRIMARY_KEY = 'idonetoone';

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