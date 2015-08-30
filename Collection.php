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
     * Current \Iterator key
     * @var unknown
     */
    protected $iIndex = 0;

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
        return $this->iIndex;
    }

    /**
     *
     * @see Iterator::next()
     */
    public function next()
    {
        next($this->aElements);
        ++$this->iIndex;
    }

    /**
     *
     * @see Iterator::rewind()
     */
    public function rewind()
    {
        $this->iIndex = 0;
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
     * Add element to collection
     *
     * @param mixed $mValue Item value
     * @param integer|string $mKey Item's key (omit it to use Iterator index)
     */
    public function add($mValue, $mKey = null)
    {
        if (is_int($mKey) === true || is_string($mKey) === true) {
            $this->aElements[$mKey] = $mValue;
        } elseif(is_null($mKey) === true) {
            $this->aElements[] = $mValue;
        } else {
            return false;
        }
        return true;
    }

    /**
     * @todo remove member
     */
    public function delete()
    {

    }

    /**
     * Tell if the collection contain at least one item
     * @return boolean
     *              TRUE if the collection has at least one item otherwhise FALSE
     */
    public function hasItem()
    {
        return ($this->count() > 0);
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
