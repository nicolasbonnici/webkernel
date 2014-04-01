<?php
namespace Library\Core;

/**
 *
 * @author Antoine <antoine.preveaux@bazarchic.com>
 * @author niko <nicolasbonnici@gmail.com>
 *        
 */
class Collection implements \Iterator
{

    /**
     * List of elements in collection
     *
     * @var array
     */
    protected $aElements = array();

    /**
     * Constructor
     *
     * @param array $aElements
     *            Collection elements
     */
    public function __construct(array $aElements = array())
    {
        $this->aElements = $aElements;
    }

    /**
     *
     * @see Iterator::current()
     */
    public function current()
    {
        return current($this->aElements);
    }

    /**
     *
     * @see Iterator::key()
     */
    public function key()
    {
        return keyreset($this->aElements);
    }

    /**
     *
     * @see Iterator::next()
     */
    public function next()
    {
        next($this->aElements);
    }

    /**
     *
     * @see Iterator::rewind()
     */
    public function rewind()
    {
        reset($this->aElements);
    }

    /**
     *
     * @see Iterator::valid()
     */
    public function valid()
    {
        return ($this->current() !== false);
    }

    /**
     * Count the number of elements in collection
     *
     * @return integer Number of elements contained in collection
     */
    public function count()
    {
        return count($this->aElements);
    }

    /**
     * Sort collection elements by value
     *
     * @return boolean TRUE on success, otherwise FALSE
     */
    public function sort()
    {
        return asort($this->aElements);
    }

    /**
     * Sort collection elements by value
     *
     * @return boolean TRUE on success, otherwise FALSE
     */
    public function ksort()
    {
        return ksort($this->aElements);
    }

    /**
     * Add element to collection
     *
     * @param integer|string $mKey
     *            Element's key
     * @param mixed $mValue
     *            Element's value
     */
    public function add($mKey, $mValue)
    {
        if (is_int($mKey) || ! empty($mKey)) {
            $this->aElements[$mKey] = $mValue;
        }
    }

    /**
     * Retrieve an element of the collection by its key
     *
     * @param integer|string $mKey
     *            Element's key
     * @return mixed Element if existing, otherwise null
     */
    public function get($mKey)
    {
        assert('is_int($mKey) || !empty($mKey)');
        
        return isset($this->aElements[$mKey]) ? $this->aElements[$mKey] : null;
    }

    /**
     * Reset collection
     */
    public function reset()
    {
        $this->aElements = array();
    }
}

?>
