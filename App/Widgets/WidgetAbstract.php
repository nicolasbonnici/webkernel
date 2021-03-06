<?php
namespace Library\Core\App\Widgets;

use Library\Core\Exception\CoreException;
use Library\Core\Html\Element;
use Library\Core\Html\Node;


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

    /*
     * Html Dom Node instance to store markup
     * @var Node
     */
    protected $oNode;

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
        # Resolve path, vendor name and plugin name from namespace
        $aNamespace = $this->getNamespaceAsArray();
        $this->sVendorName = $this->resolveVendorName($aNamespace);
        $this->sName = $this->resolveWidgetName($aNamespace);
        $this->sPath = $this->resoleWidgetPath($aNamespace);

        # View component instance
        $this->oNode = new Node();
    }

    /**
     * Load and build widget data
     *
     * @return bool                 The $this->bIsloaded value
     */
    abstract public function build();

    /**
     * Render the widget
     *
     * @return string
     */
    public function render()
    {
        # Build Widget if not already done
        if ($this->isLoaded() === false) {
            $this->build();
        }
        return $this->oNode->render();
    }

    /**
     * Render the widget markup on __toString magic method
     * @return string
     */
    public function __toString()
    {
        return $this->render();
    }

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
     * Get widget specific parameter
     *
     * @param $mKey
     * @return mixed
     */
    public function getParameter($mKey)
    {
        return (isset($this->aParameters) === true)
            ? $this->aParameters[$mKey]
            : null;
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
        foreach ($aParameters as $mKey => $mValue) {
            $this->addParameter($mKey, $mValue);
        }
        return $this;
    }

    public function addChildElement(Element $oElement)
    {
        $this->getNode()->addElement($oElement);
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
    public function getRequiredParameters()
    {
        return $this->aRequiredParameters;
    }

    /**
     * @return Node
     */
    public function getNode()
    {
        return $this->oNode;
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
    const ERROR_MISSING_DEPENDENCIES      = 2;
    const ERROR_UNABLE_TO_BUILD_WIDGET    = 3;

    /**
     * Error message
     *
     * @var array
     */
    public static $aErrors = array(
        self::ERROR_MISSING_DEPENDENCIES      => 'Missing dependency: %s.',
        self::ERROR_UNABLE_TO_BUILD_WIDGET    => 'Unable to build Widget, please check logs.',
    );

}