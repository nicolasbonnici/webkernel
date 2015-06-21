<?php
namespace Library\Core\Scope;

/**
 * EntityScope component to manage Entities parameters with constraints on field support
 * 
 * @author niko <nicolasbonnici@gmail.com>
 *
 */
class EntitiesScope extends Scope
{

    public function __construct()
    {
    }

}

class EntitiesScopeException extends \Exception
{
}
