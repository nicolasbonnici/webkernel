<?php
namespace Library\Core\Scope;

use Library\Core\FileSystem\Directory;
use Library\Core\Orm\EntityParser;

/**
 * Scope Bundles component
 * 
 * @author niko <nicolasbonnici@gmail.com>
 *
 */
class BundlesEntitiesScope extends BundlesScope
{
    public function __construct()
    {
        parent::__construct();
        $this->build();
    }

    protected function build()
    {
        foreach($this->getScope() as $sBundlesName => $mFreeDimension) {
            if (Directory::exists(BUNDLES_PATH . $sBundlesName . '/Entities/')) {
                $oEntityParser = new EntityParser(BUNDLES_PATH . $sBundlesName . '/Entities/');
                /** @var \Library\Core\Orm\Entity $oEntity */
                foreach ($oEntityParser->getEntities() as $oEntity) {
                    if ($oEntity->isSearchable()) {
                        $this->aScope[$sBundlesName][] = $oEntity;
                    }
                }
            }
        }
    }
}

class BundlesEntitiesScopeException extends \Exception
{
}
