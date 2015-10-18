<?php
namespace Library\Core\Tests\Html\Elements\Helpers;

use Library\Core\Html\Elements\Helpers\FontAwesomeIcon;
use \Library\Core\Test as Test;

/**
 * Font Awesome Icon Helper test
 * 
 * @author Nicolas Bonnici <nicolasbonnici@gmail.com>
 */
class FontAwesomeIconTest extends Test
{

    /**
     * @var FontAwesomeIcon
     */
    protected $oFontAwesomeIconInstance;

    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();
    }

    public function setUp()
    {
        $this->oFontAwesomeIconInstance = new FontAwesomeIcon('thumbs-o-up');
    }

    public function tearDown()
    {
    }

    public function testConstructor()
    {
        $this->assertTrue(
            $this->oFontAwesomeIconInstance instanceof FontAwesomeIcon
        );
    }

    public function testRenderIcon()
    {
        $this->assertEquals(
            '<span class="fa fa-thumbs-o-up"></span>',
            $this->oFontAwesomeIconInstance->render()
        );
    }
}