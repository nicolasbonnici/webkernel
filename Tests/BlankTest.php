<?php
namespace Core\Tests;

use \Core\Test as Test;

/**
 * Blank unit tests
 *
 * @author Nicolas Bonnici <nicolasbonnici@gmail.com>
 */
class BlankTest extends Test
{
    protected static $oJsonInstance;

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

    public function testBlank()
    {
        $this->assertTrue(true);
    }

}