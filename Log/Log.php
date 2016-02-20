<?php
namespace Library\Core\Log;

/**
 * Log object to pass to the logger instance
 *
 * Class Log
 * @package Library\Core\Log
 */
class Log extends LogAbstract
{

    /**
     * Log accessor
     * @return $this
     */
    protected function get()
    {
        return $this;
    }

}