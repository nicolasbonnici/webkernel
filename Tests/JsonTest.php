<?php
namespace Library\Core\Tests;

use \Library\Core\Test as Test;

use Library\Core\Json;

/**
 * Json component unit tests
 * 
 * @author Nicolas Bonnici <nicolasbonnici@gmail.com>
 */
class JsonTest extends Test
{
    protected static $oJsonInstance;

    protected $aTestDataArray = array(
    	'prop1' => 1,
        'prop2' => 2,
        'prop3' => 3
    );
    
    protected $sTestDataString = '{"prop1":1,"prop2":2,"prop3":3}';
    
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

    public function testConstructorWithArray()
    {
    	self::$oJsonInstance = new Json($this->aTestDataArray); 
        $this->assertTrue(self::$oJsonInstance->isLoaded());
    }

    public function testConstructorWithString()
    {
    	self::$oJsonInstance = new Json($this->sTestDataString);    	 
        $this->assertTrue(self::$oJsonInstance->isLoaded());
    }
    
    public function testToString()
    {
    	self::$oJsonInstance = new Json($this->sTestDataString);
    	$this->assertTrue(is_string(self::$oJsonInstance->__toString()));
    	$this->assertEquals(self::$oJsonInstance->__toString(), $this->sTestDataString);    	
    }
    
    public function testGet()
    {
    	self::$oJsonInstance = new Json($this->sTestDataString);
        $this->assertEquals(self::$oJsonInstance->get(), self::$oJsonInstance->getAsArray());
    }
    
	public function testGetWithKey()
	{
		self::$oJsonInstance = new Json($this->sTestDataString);
		$this->assertEquals(self::$oJsonInstance->get('prop1'), $this->aTestDataArray['prop1']);		
		$this->assertEquals(self::$oJsonInstance->get('prop2'), $this->aTestDataArray['prop2']);		
		$this->assertEquals(self::$oJsonInstance->get('prop3'), $this->aTestDataArray['prop3']);		
	}
	
	public function testIsLoaded() 
	{
		self::$oJsonInstance = new Json($this->sTestDataString);
        $this->assertTrue(self::$oJsonInstance->isLoaded());
		self::$oJsonInstance = new Json('malformed json');
        $this->assertFalse(self::$oJsonInstance->isLoaded());
	}
	
	public function testIsValid()
	{
		self::$oJsonInstance = new Json($this->sTestDataString);
		$this->assertTrue(self::$oJsonInstance->isValid());
		self::$oJsonInstance = new Json('malformed json');
		$this->assertFalse(self::$oJsonInstance->isValid());
	}    

}