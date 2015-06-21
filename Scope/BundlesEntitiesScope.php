<?php
namespace Library\Core\Scope;
use Library\Core\Bundles;
use Library\Core\Directory;
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
                foreach ($oEntityParser->getEntities() as $sEntity) {
                    $sEntityClass = '\bundles\\' . $sBundlesName . '\\Entities\\' . $sEntity;
                    $this->aScope[$sBundlesName][] = new $sEntityClass;

                }
            }
        }
    }
}

class BundlesEntitiesScopeException extends \Exception
{
}
