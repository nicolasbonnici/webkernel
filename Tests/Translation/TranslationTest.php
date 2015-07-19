<?php
namespace Library\Core\Tests\Translation;


use Library\Core\Test;
use Library\Core\Translation\Translation;

class TranslationTest extends Test
{
    /** @var Translation $oTranslationInstance */
    protected $oTranslationInstance;

    protected $sTestLang = 'FR_fr';

    protected function setUp()
    {
        $this->oTranslationInstance = new Translation($this->sTestLang);
    }

    public function testConstructor()
    {
        $this->assertTrue($this->oTranslationInstance instanceof Translation);
        $this->assertTrue(is_array($this->oTranslationInstance->getTranslations()));
    }

    public function testSetTranslationsThenAccessToIt()
    {
        $this->assertInstanceOf(
            get_class($this->oTranslationInstance),
            $this->oTranslationInstance->setTranslations(array('test' => 'lorem ipsum'))
        );
        $this->assertEquals('lorem ipsum', $this->oTranslationInstance->get('test'));

    }
}