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
     * Array of constraints to exclude from Scope
     *
     * @var array
     */
    protected $aConstraints = array();

    /**
     * Instance constructor
     */
    public function __construct()
    {

    }

    /**
     * Generic filter method on Scope keys
     * @see ScopeConstraints parent
     */
    public function filter()
    {
        $aScope = $this->getScope();
        if (count($this->aConstraints) > 0) {
            foreach ($aScope as $mScopeKey => $mScopeItem) {
                if (in_array($mScopeKey, $this->aConstraints) === true) {
                    $this->delete($mScopeKey);
                }
            }
        }
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
     * Delete from Scope
     *
     * @param mixed string|int $mKey
     * @return bool
     */
    public function delete($mKey)
    {
        if (isset($this->aScope[$mKey]) === true) {
            unset($this->aScope[$mKey]);
            return true;
        }
        return false;
    }

    /**
     * Set constraints for scope
     *
     * @param array $aConstraints
     * @return Scope
     */
    public function setConstraints(array $aConstraints)
    {
        $this->aConstraints = $aConstraints;

        // Directly refresh scope
        $this->filter();
        
        return $this;
    }

    /**
     * Get scope constraint
     *
     * @return array
     */
    public function getConstraints()
    {
        return $this->aConstraints;
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
