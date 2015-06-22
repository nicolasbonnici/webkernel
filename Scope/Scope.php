<?php
namespace Library\Core\Scope;

/**
 * Generic Scope component
 * 
 * @author niko <nicolasbonnici@gmail.com>
 *
 */
class Scope
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
     * Add item to the scope
     *
     * @param mixed int|float|string|array|object $mValue
     * @param mixed int|float|string|array|object $mKey     NULL to use the Iterator index
     * @return Scope
     */
    public function add($mValue, $mKey = null)
    {


        if (is_null($mKey) === false) {
            $this->aScope[$mKey] = $mValue;
        } else {
            // By default use the Iterator index
            $this->aScope[] = $mValue;
        }
        return $this;
    }

    /**
     * Bulk add items to the scope
     *
     * @param array $mItem
     * @return Scope
     */
    public function addItems(array $aItems)
    {
        foreach ($aItems as $mKey => $mValue) {
            $this->add($mValue, $mKey);
        }
        return $this;
    }

    /**
     * Get Scope items
     *
     * @param mixed int|float|string|array|object $mKey
     * @return mixed int|float|string|array|object
     */
    public function get($mKey = null)
    {
        if (is_null($mKey) === true) {
            return $this->getScope();
        } else {
            return ((isset($this->aScope[$mKey]) === true) ? $this->aScope[$mKey] : null);
        }
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
