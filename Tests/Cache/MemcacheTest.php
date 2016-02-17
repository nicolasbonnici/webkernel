<?php
namespace Library\Core\Tests\Cache;

use Library\Core\Cache\Drivers\Memcache;
use Library\Core\Tests\Test;

/**
 * Json component unit tests
 * 
 * @author Nicolas Bonnici <nicolasbonnici@gmail.com>
 */
class MemcacheTest extends Test
{
    protected static $oMemcacheInstance;

    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();
    }

    public function setUp()
    {
    }

    public function tearDown()
    {
    }

    public function testInstance()
    {
        $this->assertTrue(Memcache::getInstance() instanceof Memcache);
    }

    public function testIsConnected()
    {
        $this->assertTrue(Memcache::isConnected());
    }

    public function testSet()
    {
        Memcache::set('test', 'value');
        Memcache::set('otherTest', 'otherValue');
    }

    public function testGet()
    {
        $this->assertEquals('value', Memcache::get('test'));
        $this->assertEquals('otherValue', Memcache::get('otherTest'));
    }


    public function testDelete()
    {
        Memcache::delete('test');
        $this->assertEquals(null, Memcache::get('test'));
    }

    public function testFlush()
    {
        Memcache::set('test', 'value');
        $this->assertEquals('value', Memcache::get('test'));

        Memcache::flush();

        $this->assertEquals(null, Memcache::get('test'));
        $this->assertEquals(null, Memcache::get('otherTest'));
    }
}