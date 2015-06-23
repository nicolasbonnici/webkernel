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

        $aBundles = $this->getScope();
        foreach($aBundles as $sBundlesName => $aMvc) {
            if (Directory::exists(BUNDLES_PATH . $sBundlesName . '/Entities/')) {
                $oEntityParser = new EntityParser(BUNDLES_PATH . $sBundlesName . '/Entities/');
                foreach ($oEntityParser->getEntities() as $oEntity) {
                    $this->aScope[$sBundlesName][] = $oEntity;
                }
            }
        }
    }
}

class BundlesEntitiesScopeException extends \Exception
{
}
