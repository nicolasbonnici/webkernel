<?php
namespace Library\Core\Scope;
use Library\Core\Bundles;

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
    }
}

class BundlesScopeException extends \Exception
{
}
