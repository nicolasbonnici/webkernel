<?php
namespace Library\Core;

use Library\Core\View;
use Library\Core\Json;

/**
 * Website builder widget abstract class
 *
 * @author Nicolas Bonnici <nicolasbonnici@gmail.com>
 */

abstract class Widget
{
    /**
     * Widgets render modes
     * @var array
     */
    protected $aRenderModes = array(
        'normal',
        'edition',
        'menu'
    );

    protected $sWidgetName;

    /**
     * Widget markup template file full absolute path
     * @var string
     */
    protected $sTplPath;

    /**
     * Widget rendering mode (default: normal render mode)
     * @var string
     */
    protected $sRenderMode = 'normal';

    /**
     * Widget version
     * @var string
     */
    protected $sVersion;

    /**
     * Widget author
     * @var string
     */
    protected $sAuthor;

    /*
     * View component instance to render widgets
     * @var Library\Core\View
     */
    protected $oViewInstance;

    /**
     * Widget constructor
     *
     * @param string $sTplPath
     * @param string $sRenderMode
     * @throws WidgetException
     */
    public function __construct()
    {
        if (! $this->checkRenderMode()) {
            throw new WidgetException('Render mode ' . $this->sRenderMode . ' is not supported.');
        } else {
            $this->oViewInstance = new View();
        }

    }

    /**
     * Render the widget
     * @return string
     */
    public function render()
    {
       $sRender = $this->oViewInstance->render(
            array(
                'sWidgetRenderMode' => $this->getRenderMode()
            ),
            $this->getTplPath(),
            200,
            true
        );
       $oRendered = new Json($sRender);
       return $oRendered->get('content');
    }

    /**
     * Check the widget render mode
     * @param string $sRenderMode
     * @return boolean
     */
    private function checkRenderMode($sRenderMode = null)
    {
        if (is_null($sRenderMode)) {
            $sRenderMode = $this->sRenderMode;
        }
        return (in_array($sRenderMode, $this->aRenderModes));
    }

    /**
     * Render mode accessor
     * @return string
     */
    public function getRenderMode()
    {
        return $this->sRenderMode;
    }

    /**
     * Render mode setter
     * @param string $sRenderMode
     * @return \Library\Core\Widget
     */
    public function setRenderMode($sRenderMode)
    {
        if (! empty($sRenderMode) && $this->checkRenderMode($sRenderMode)) {
            $this->sRenderMode = $sRenderMode;
            return $this;
        }
    }

    /**
     * Template file path accessor
     * @return string
     */
    public function getTplPath()
    {
        return $this->sTplPath;
    }

    /**
     * Get widget name on current instance
     * @return string
     */
    public function getName()
    {
        return get_called_class();
    }
}

class WidgetException extends \Exception
{

}
