<?php
namespace Library\Core\Scope;

/**
 * Scope component abstract layer
 * 
 * @author niko <nicolasbonnici@gmail.com>
 *
 */
abstract class Scope
{
    /**
     * Array to store entities scope
     *
     * @var array
     */
    protected $aScope = array();

    /**
     * Instance constructor
     */
    public function __construct()
    {

    }

    /**
     * Add item to the scope (overload for more specific treatment)
     *
     * @param $mItem
     * @return Scope
     */
    public function add($mItem)
    {
        $this->aScope[] = $mItem;
        return $this;
    }

    /**
     * Scope accessor
     * @return array
     */
    public function getScope()
    {
        return $this->aScope;
    }

}

class ScopeException extends \Exception
{
}
