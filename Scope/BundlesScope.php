<?php
namespace Library\Core\Scope;
use Library\Core\App\Bundles\Bundles;

/**
 * Scope Bundles component
 * 
 * @author niko <nicolasbonnici@gmail.com>
 *
 */
class BundlesScope extends Scope
{
    public function __construct()
    {
        $oBundles = new Bundles();
        $this->addItems($oBundles->get());

        parent::__construct();
    }
}

class BundlesScopeException extends \Exception
{
}
