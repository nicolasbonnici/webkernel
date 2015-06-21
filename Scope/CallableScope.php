<?php
namespace Library\Core\Scope;

/**
 * Callable Scope
 * 
 * @author niko <nicolasbonnici@gmail.com>
 *
 */
class CallableScope extends Scope
{

    /**
     * @param callable $mValue
     * @param mixed string|int $mKey (optional)
     * @return CallableScope
     */
    public function add(callable $mValue, $mKey = null)
    {
        if (is_null($mKey) === false) {
            $this->aScope[$mKey] = $mValue;
        } else {
            // By default use the Iterator index
            $this->aScope[] = $mValue;
        }
        return $this;
    }

}

class BundlesScopeException extends \Exception
{
}
