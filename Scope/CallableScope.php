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
     *
     * @todo find a way to overload parent method signature and direclty cast callable values
     *
     * @param callable $mValue
     * @param mixed string|int $mKey (optional)
     * @return CallableScope
     */
    public function add($mValue, $mKey = null)
    {
        if (empty($mValue) === false || is_callable($mValue) === true) {
            if (is_null($mKey) === false) {
                $this->aScope[$mKey] = $mValue;
            } else {
                // By default use the Iterator index
                $this->aScope[] = $mValue;
            }
            return $this;
        }
        return null;
    }

}

class BundlesScopeException extends \Exception
{
}
