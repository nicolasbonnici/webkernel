<?php
namespace Library\Core\App\Widgets;

use Library\Core\Exception\CoreException;
use Library\Core\App\Mvc\View\View;
use Library\Core\Json\Json;


/**
 * Common layer for widgets
 *
 * Class WidgetAbstract
 * @package Library\Core\App\Plugins
 */
abstract class WidgetAbstract
{

    /**
     * Widget's paths
     */
    const PATH_ASSETS      = 'Assets';
    const PATH_TRANSLATION = 'Translations';
    const PATH_MODELS      = 'Models';
    const PATH_VIEWS       = 'Views';

    /**
     * Generic plugins translation key
     */
    const TRANSLATION_KEY_WIDGET_NAME = 'widget_name';
    const TRANSLATION_KEY_WIDGET_DESC = 'widget_description';

    /**
     * Supported render modes
     */
    const RENDER_MODE_NORMAL  = 'normal';
    const RENDER_MODE_EDITON  = 'edition';
    const DEFAULT_RENDER_MODE = 'edition';

    /**
     * Widgets supported render modes
     * @var array
     */
    protected $aRenderModes = array(
        self::RENDER_MODE_NORMAL,
        self::RENDER_MODE_EDITON
    );

    /**
     * Vendor name
     * @var string
     */
    protected $sVendorName = '';

    /**
     * Plugin name
     * @var string
     */
    protected $sName = '';

    /**
     * Plugin version
     * @var string
     */
    protected $sVersion = '';

    /**
     * Widget markup template file full absolute path
     * @var string
     */
    protected $sPath;

    /**
     * Translation key for plugin name
     * @var string
     */
    protected $sDisplayName = self::TRANSLATION_KEY_WIDGET_NAME;

    /**
     * Translation key for plugin description
     * @var string
     */
    protected $sDescription = self::TRANSLATION_KEY_WIDGET_NAME;

    /**
     * Widget rendering mode (default: normal render mode)
     * @var string
     */
    protected $sRenderMode = self::DEFAULT_RENDER_MODE;

    /*
     * View component instance to render widgets
     * @var Library\Core\View
     */
    protected $oViewInstance;

    /**
     * Widgets parameters
     * @var array
     */
    protected $aParameters = array();

    /**
     * Required parameters to build the Widget
     * @var array
     */
    protected $aRequiredParameters = array();

    /**
     * Widget Core and bundles version dependencies
     * @var array
     */
    protected $aDependencies = array();

    /**
     * Flag to tell if the Widget was already built
     * @var bool
     */
    protected $bIsLoaded = false;

    /**
     * Widget constructor
     *
     * @param string $sTplPath
     * @param string $sRenderMode
     * @throws WidgetException
     */
    public function __construct()
    {
        if ($this->checkRenderMode() === false) {
            throw new WidgetException(
                sprintf(
                    WidgetException::getError(
                        WidgetException::ERROR_RENDER_MODE_NOT_SUPPORTED
                    ),
                    $this->getRenderMode()
                ),
                WidgetException::ERROR_RENDER_MODE_NOT_SUPPORTED
            );
        } else {
            # Resolve path, vendor name and plugin name from namespace
            $aNamespace = $this->getNamespaceAsArray();
            $this->sVendorName = $this->resolveVendorName($aNamespace);
            $this->sName = $this->resolveWidgetName($aNamespace);
            $this->sPath = $this->resoleWidgetPath($aNamespace);

            # View component instance
            $this->oViewInstance = new View(
                false,
                array(
                    $this->getPath() . self::PATH_VIEWS
                )
            );
        }

    }

    /**
     * Load and build widget data
     *
     * @return bool                 The $this->bIsloaded value
     */
    abstract protected function build();

    /**
     * Resolve widget vendor name from namespace
     *
     * @param array $aNamespace
     * @return string
     */
    protected function resolveVendorName(array $aNamespace)
    {
        return $aNamespace[count($aNamespace) - 2];
    }

    /**
     * Resolve widget name from namespace
     *
     * @param array $aNamespace
     * @return string
     */
    protected function resolveWidgetName(array $aNamespace)
    {
        return $aNamespace[count($aNamespace) - 1];
    }

    /**
     * Compute widget path from namespace
     *
     * @param array $aNamespace
     * @return string
     */
    protected function resoleWidgetPath(array $aNamespace)
    {
        return implode(DIRECTORY_SEPARATOR, $aNamespace) . DIRECTORY_SEPARATOR;
    }

    /**
     * Return the current called class with full namespace as an array
     * @return array
     */
    protected function getNamespaceAsArray()
    {
        $aNamespace = explode('\\', get_called_class());
        # Remove the "Widget" class name from namespace
        return array_slice($aNamespace, 0, count($aNamespace) - 1);
    }

    /**
     * Render the widget
     *
     * @return string
     */
    public function render()
    {
        if ($this->isLoaded() === false) {
            $this->build();
        }

        # Simulate a XHR request for the view component
        $this->aParameters['bIsXhr'] = true;
        # Set render mode for view
        $this->aParameters['sRenderMode'] = $this->getRenderMode();

        $sRendered = $this->oViewInstance->render(
            $this->getParameters(),
            'widget.tpl',
            200,
            true
        );

        $oRendered = new Json($sRendered);
        return $oRendered->get('content');
    }

    /**
     * Check the widget render mode
     *
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
     *
     * @return string
     */
    public function getRenderMode()
    {
        return $this->sRenderMode;
    }

    /**
     * Render mode setter
     *
     * @param string $sRenderMode
     * @return WidgetAbstract
     */
    public function setRenderMode($sRenderMode)
    {
        if (! empty($sRenderMode) && $this->checkRenderMode($sRenderMode)) {
            $this->sRenderMode = $sRenderMode;
            return true;
        }
        return false;
    }

    /**
     * Template file path accessor
     *
     * @return string
     */
    public function getPath()
    {
        return $this->sPath;
    }

    /**
     * Get widget parameters
     *
     * @return array
     */
    public function getParameters()
    {
        return $this->aParameters;
    }

    /**
     * Add a widget parameter
     *
     * @param mixed int|string $mKey
     * @param mixed int|string|array|object $mValue
     * @return WidgetAbstract
     */
    public function addParameter($mKey, $mValue)
    {
        $this->aParameters[$mKey] = $mValue;
        return $this;
    }

    /**
     * Add widget parameters
     *
     * @param array $aParameters
     * @return WidgetAbstract
     */
    public function addParameters(array $aParameters)
    {
        $this->aParameters = array_merge($this->aParameters, $aParameters);
        return $this;
    }

    /**
     * Get widget name
     *
     * @return string
     */
    public function getName()
    {
        return $this->sName;
    }

    /**
     * Get widget vendor name
     *
     * @return string
     */
    public function getVendorName()
    {
        return $this->sVendorName;
    }

    /**
     * Get widget version
     *
     * @return string
     */
    public function getVersion()
    {
        return $this->sVersion;
    }


    /**
     * Get widget display name
     *
     * @return string
     */
    public function getDisplayName()
    {
        return $this->sDisplayName;
    }

    /**
     * Get widget display description
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->sDescription;
    }


    /**
     * @return array
     */
    public function getDependencies()
    {
        return $this->aDependencies;
    }

    /**
     * @param array $aDependencies
     */
    public function setDependencies(array $aDependencies)
    {
        $this->aDependencies = $aDependencies;
    }

    /**
     * @return array
     */
    public function getRequiredParameters()
    {
        return $this->aRequiredParameters;
    }

    /**
     * Tell if the widget was already loaded
     * @return bool
     */
    public function isLoaded()
    {
        return $this->bIsLoaded;
    }
}

class WidgetException extends CoreException
{

    /**
     * Error codes
     *
     * @var integer
     */
    const ERROR_RENDER_MODE_NOT_SUPPORTED = 2;
    const ERROR_MISSING_DEPENDENCIES      = 3;

    /**
     * Error message
     *
     * @var array
     */
    public static $aErrors = array(
        self::ERROR_RENDER_MODE_NOT_SUPPORTED => 'Render mode %s is not supported.',
        self::ERROR_MISSING_DEPENDENCIES      => 'Missing dependency: %s.',
    );

}