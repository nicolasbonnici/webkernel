<?php
namespace Library\Core\Tests\Router;

use Library\Core\Tests\Test;
use Library\Core\Router\Router;


/**
 * This class allow to perform Benchmark on framework components
 *
 * Class Benchmark
 * @package Library\Core
 */
class RouterTest extends Test
{
    /**
     * @var Router
     */
    protected $oRouterInstance;

    protected $aConfiguration = array(
        'routing' => array(
            'default_bundle' => 'test',
            'default_controller' => 'toto',
            'default_action' => 'titi',
        )
    );

    /**
     * Sets up the fixture, for example, open a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {

    }

    private function loadRouter($sUrl)
    {
        $_SERVER['REQUEST_URI'] = $sUrl;
        Router::init($this->aConfiguration);
        $this->oRouterInstance = Router::getInstance();
    }

    public function testRouterConstructor()
    {
        $this->loadRouter('/some/url');


        $this->assertInstanceOf(
            get_class(Router::getInstance()),
            $this->oRouterInstance,
            'Unable to construct Router'
        );

    }

    public function testRouterAccessors()
    {
        $this->loadRouter('/some/url');

        # Test setted Router configuration
        $this->assertEquals(
            $this->oRouterInstance->getDefaultBundle(),
            $this->aConfiguration['routing']['default_bundle'],
            'Unable to set configuration Router on default bundle'
        );
        $this->assertEquals(
            $this->oRouterInstance->getDefaultController(),
            $this->aConfiguration['routing']['default_controller'],
            'Unable to set configuration Router on default controller'
        );
        $this->assertEquals(
            $this->oRouterInstance->getDefaultAction(),
            $this->aConfiguration['routing']['default_action'],
            'Unable to set configuration Router on default action'
        );

    }

    public function testRouterDispatch()
    {
        $this->loadRouter('/some/url');

        # Assert that the url setted for $_SERVER['REQUEST_URI'] is correctly parsed
        $this->assertEquals(
            $this->oRouterInstance->getBundle(),
            'some',
            'Unable for Router to parse url query string'
        );

        $this->assertEquals(
            $this->oRouterInstance->getController(),
            'url',
            'Unable for Router to parse url query string'
        );

        # Action not setted but must be the default setted on configuration
        $this->assertEquals(
            $this->oRouterInstance->getAction(),
            $this->aConfiguration['routing']['default_action'],
            'Default action not correctly setted on Router instance'
        );

        $this->oRouterInstance = null;
        $this->loadRouter('/some/url/randomaction');
        Router::init($this->aConfiguration);

        # Action not setted but must be the default setted on configuration
        $this->assertEquals(
            $this->oRouterInstance->getAction(),
            'randomaction',
            'Action not correctly parsed by Router instance'
        );
    }

    public function testCustomRouteMatching()
    {
        $this->loadRouter('/login');

        $this->assertEquals(
            $this->oRouterInstance->getBundle(),
            'auth',
            'Unable to set bundle from a custom route'
        );
        $this->assertEquals(
            $this->oRouterInstance->getDefaultController(),
            $this->aConfiguration['routing']['default_controller'],
            'Unable to set controller from a custom route'
        );
        $this->assertEquals(
            $this->oRouterInstance->getDefaultAction(),
            $this->aConfiguration['routing']['default_action'],
            'Unable to set action from a custom route'
        );
    }

    public function testRouterWithParameters()
    {
        $this->loadRouter('/app/article/read/param1/ok/param2/333/');

        $this->assertEquals(
            $this->oRouterInstance->getBundle(),
            'app',
            'Unable to set configuration Router on default bundle'
        );
        $this->assertEquals(
            $this->oRouterInstance->getController(),
            'article',
            'Unable to set configuration Router on default controller'
        );
        $this->assertEquals(
            $this->oRouterInstance->getAction(),
            'read',
            'Unable to set configuration Router on default action'
        );

        $this->assertEquals(
            $this->oRouterInstance->getParams(),
            array(
                'param1' => 'ok',
                'param2' => '333'
            )
        );
    }
}