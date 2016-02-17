<?php
namespace Library\Core\Collection;

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
     * @var int
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
     * @param bool $bReverse
     * @return bool
     */
    public function sort($bReverse = false)
    {
        if ($bReverse === true) {
            return rsort($this->aElements);
        } else {
            return asort($this->aElements);
        }
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
     * Add several elements from a given array
     *
     * @param array $aItems
     * @return bool
     */
    public function addItems(array $aItems)
    {
        $aLog = array();
        foreach ($aItems as $mKey => $mValue) {
            $aLog[] = $this->add($mValue, $mKey);
        }
        return (bool) (in_array(false, $aLog) === false);
    }

    /**
     * @param Collection $oCollection
     * @param bool $bIgnoreIndex          TRUE to ignore indexes, FALSE to keep them and merge with existent
     */
    public function merge(Collection $oCollection, $bIgnoreIndex = false)
    {
        if ($bIgnoreIndex === true) {
            foreach ($oCollection->getAsArray() as $mIndex => $mValue) {
                $this->add($mValue);
            }
        } else {
            $this->aElements = array_merge($this->aElements, $oCollection->getAsArray());
        }

        # Resort collection
        return $this->sort();

    }

    /**
     * Delete an item from Collection instance
     *
     * @param mixed string|int $mKey
     * @return bool
     */
    public function delete($mKey)
    {
        if (isset($this->aElements[$mKey]) === true) {
            unset($this->aElements[$mKey]);
            return true;
        }
        return false;
    }

    /**
     * Tell if the collection contain at least one item
     * @return boolean      TRUE if the collection has at least one item otherwise FALSE
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
        return (bool) (empty($this->aElements) === true);
    }

    /**
     * Get Entities collection
     * @return array
     */
    public function getAsArray()
    {
        return $this->aElements;
    }
}
