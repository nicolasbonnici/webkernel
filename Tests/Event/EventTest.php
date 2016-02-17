<?php
namespace Library\Core\Tests\Event;

use Library\Core\Tests\Test;


/**
 * DummyEvent component unit tests
 * 
 * @author Nicolas Bonnici <nicolasbonnici@gmail.com>
 */
class EventTest extends Test
{
    protected static $oDummyEventInstance;

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

    public function testConstructor()
    {
        self::$oDummyEventInstance = new DummyEvent();
        $this->assertTrue(self::$oDummyEventInstance instanceof DummyEvent);

    }

    public function testOn()
    {
        $this->assertTrue(self::$oDummyEventInstance->on('fooEvent', function() {
                return true;
            }
        ) instanceof DummyEvent);
    }

    public function testEmit()
    {
        $this->assertTrue(self::$oDummyEventInstance->emit('fooEvent'));
    }

    public function testEmitWithParameters()
    {
        $parameter = array('boundedTestParameter' => true);
        // Register an event with parameter
        $this->assertTrue(self::$oDummyEventInstance->on(
            'testEvent',
            function($aParameters) {
                return $aParameters['boundedTestParameter'];
            }
        ) instanceof DummyEvent);

        // Emit the event with parameters
        $this->assertTrue(self::$oDummyEventInstance->emit('fooEvent', $parameter));
    }
}