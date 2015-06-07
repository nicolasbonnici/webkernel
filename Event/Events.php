<?php
namespace Library\Core\Event;

/**
 * Events managment abstract component
 *
 * Class Event
 * @package Library\Core\Event
 */

abstract class Events {

    /**
     * Registered events
     *
     * @var array
     */
    protected $aEvents = array();

    /**
     * Instance constructor
     */
    public function __construct()
    {

    }

    /**
     * Register event
     *
     * @param string $sEventName
     * @param callable $function
     * @return Events
     */
    public function on($sEventName, callable $function)
    {
        $this->aEvents[$sEventName] = $function;
        return $this;
    }

    /**
     * Emit an event
     *
     * @param string $sEventName
     * @param array $aParameters (optional)
     * @return bool
     */
    public function emit($sEventName, array $aParameters = array())
    {
        return (
            (isset($this->aEvents[$sEventName]) === true &&
                is_callable($this->aEvents[$sEventName]) === true)
                ? $this->aEvents[$sEventName]($aParameters)
                : false
        );
    }

}