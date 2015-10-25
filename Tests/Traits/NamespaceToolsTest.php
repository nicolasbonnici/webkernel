<?php
namespace Library\Core\Tests\Scaffold;

use Library\Core\Bootstrap;
use Library\Core\Test;
use Library\Core\Traits\NamespaceTools;

class NamespaceToolsTest extends Test
{
    # Register namespaces toolbox trait
    use NamespaceTools;

    public function testComputeAbsolutePathFromNamespace()
    {
        $this->assertEquals(
            Bootstrap::getRootPath() . 'Test' . DIRECTORY_SEPARATOR . 'toto' . DIRECTORY_SEPARATOR . 'Titi' . DIRECTORY_SEPARATOR . 'Haha',
            $this->computeAbsolutePathFromNamespace('Test\\toto\\Titi\\Haha')
        );
    }
}