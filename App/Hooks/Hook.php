<?php
namespace Library\Core\App\Hooks;
use Library\Core\App\Widgets\WidgetAbstract;

/**
 * Hook component to manage hooks on a template layout
 *
 * Class Hook
 * @package Library\Core\App\Hooks
 */
class Hook
{

    /**
     * Hooks to store widgets
     * @var array
     */
    protected $aHooks = array();

    /**
     * Add a new Hook
     *
     * @param string $sHookName
     * @return Hook
     */
    public function add($sHookName)
    {
        if (is_string($sHookName) === true && empty($sHookName) === false) {
            $this->aHooks[$sHookName] = array();
        }
        return $this;
    }

    /**
     * Register a Widget to a Hook
     *
     * @param string $sHookName
     * @param WidgetAbstract $oWidget
     * @return Hook
     */
    public function registerWidget($sHookName, WidgetAbstract $oWidget)
    {
        if (isset($this->aHooks[$sHookName]) === false) {
            $this->add($sHookName);
        }

        $this->aHooks[$sHookName][] = $oWidget;
        return $this;
    }

    /**
     * Get available hooks
     *
     * @return array
     */
    public function getHooksName()
    {
        return array_keys($this->aHooks);
    }

    /**
     * Retrieve widgets for a given hook name
     *
     * @param $sHookName
     * @return array
     */
    public function getHookWidgets($sHookName)
    {
        return (isset($this->aHooks[$sHookName]) === true)
            ? $this->aHooks[$sHookName]
            : null;
    }

    /**
     * Get an associative array of hook_name => [oWidget, oWidget, oWidget...]
     * @return array
     */
    public function get()
    {
        return $this->aHooks;
    }
}