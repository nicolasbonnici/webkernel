<?php
namespace Core\Tests\App;

use Library\Core\App\Configuration;
use Library\Core\Tests\Test;

/**
 * Configuration component unit tests
 *
 * @author Nicolas Bonnici <nicolasbonnici@gmail.com>
 */
class ConfigurationTest extends Test
{
    /**
     * @var Configuration
     */
    protected $oConfigurationInstance;

    protected function setUp()
    {
        if (self::loadUser(true) === false) {
            die('Unable to load Test user');
        }
        $this->oConfigurationInstance = new Configuration('sample', self::$oUser);
    }

    public function testThatControllerBuildAllConfigurations()
    {
        $this->assertTrue(is_array($this->oConfigurationInstance->getConfiguration()));
    }

    public function testAccessors()
    {
        $this->assertInstanceOf(
            get_class($this->oConfigurationInstance),
            $this->oConfigurationInstance->setConfiguration(
                array(
                    'foo'       => 'bar',
                    'some int'  => 12
                )
            )
        );

        $this->assertArrayHasKey(
            'foo',
            $this->oConfigurationInstance->getConfiguration()
        );

        $this->assertArrayHasKey(
            'some int',
            $this->oConfigurationInstance->getConfiguration()
        );

        $this->assertEquals(
            'bar',
            $this->oConfigurationInstance->get('foo')
        );

        $this->assertEquals(
            12,
            $this->oConfigurationInstance->get('some int')
        );
    }

    public function testStoreThenReadFromDatabase()
    {
        $this->assertTrue(
            $this->oConfigurationInstance->set('foo', 'bar'),
            'Unable to store configuration on database'
        );

        $this->assertEquals(
            'bar',
            $this->oConfigurationInstance->get('foo')
        );
    }

    public function testUpdateConfigurationValue()
    {
        $this->assertTrue(
            $this->oConfigurationInstance->set('foo', 'bar2')
        );
    }

    public function testDeleteConfiguration()
    {
        $this->assertTrue($this->oConfigurationInstance->delete('foo'), 'Unable to delete configuration from database');
    }

}