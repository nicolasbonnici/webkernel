<?php
namespace Library\Core\App\Widgets;

use Library\Core\Html\Elements\Button;
use Library\Core\Html\Elements\Div;
use Library\Core\Html\Elements\Helpers\FontAwesomeIcon;

/**
 * Widget component
 *
 * Class Widget
 * @package Library\Core\App\Widgets
 */
class Widget extends WidgetAbstract
{
    /**
     * Widget class structure
     */
    const WIDGET_CLASS                  = 'ui-widget';
    const WIDGET_TOOLBAR                = 'ui-widget-toolbar';
    const WIDGET_DATA_CONTAINER_CLASS   = 'ui-widget-data';
    const WIDGET_HEADER_CLASS           = 'ui-widget-header';
    const WIDGET_TOOLBAR_CLASS          = 'ui-widget-toolbar';
    const WIDGET_CONTENT_CLASS          = 'ui-widget-content';
    const WIDGET_FOOTER_CLASS           = 'ui-widget-footer';

    /**
     * Widget asynch loadable class
     */
    const LOADABLE_CLASS          = 'ui-loadable';
    const SCROLL_LOADABLE_CLASS   = 'ui-scroll-loadable';

    /**
     * Widget data attribute for url when widget is asynch loadable
     */
    const URL_DATA_ATTR    = 'data-url';

    /**
     * The Widget Node container
     * @var Div
     */
    protected $oWidgetContainer;

    /**
     * The widget header toolbar
     * @var Div
     */
    protected $oToolbar;

    /**
     * The widget data content wrapper
     * @var Div
     */
    protected $oDataContainer;

    /**
     * The widget header
     * @var Div
     */
    protected $oHeader;

    /**
     * The widget content
     * @var Div
     */
    protected $oContent;

    /**
     * The widget footer
     * @var Div
     */
    protected $oFooter;

    /**
     * Display widget toolbar flag
     * @var bool
     */
    protected $bToolbar = false;

    /**
     * Is widget asynch loadable
     * @var bool
     */
    protected $bLoadable = false;

    /**
     * Is widget asynch scroll loadable
     * @var bool
     */
    protected $bScrollLoadable = false;

    /**
     * The widget url (only if the widget is loadable)
     * @var string
     */
    protected $sUrl = '';

    /**
     * Instantiate default widget markup elements
     */
    private function initMarkup()
    {
        $this->oWidgetContainer = new Div();
        $this->oDataContainer = new Div();

        if ($this->isLoadable() === false && $this->isScrollLoadable() === false) {
            $this->oHeader = new Div();
            $this->oContent = new Div();
            $this->oFooter = new Div();
        }

        if ($this->hasToolbar() === true) {
            $this->oToolbar = new Div();
        }
    }
    /**
     * Load and build widget data
     *
     * @return Widget
     */
    public function build()
    {
        # Instantiate Div widget markup
        $this->initMarkup();

        # Build widget wrap
        $this->getWidgetContainer()
            ->setAttribute('class', self::WIDGET_CLASS)
            ->setAttribute('data-snap-ignore', 'true');

        # Build toolbar if needed
        if ($this->hasToolbar() === true) {
            $this->buildToolbar();

            # Append the widget toolbar
            $this->getWidgetContainer()
                ->addSubElement($this->getToolbar());
        }

        # Build widget data wrap
        $this->getDataContainer()->setAttribute('class', self::WIDGET_DATA_CONTAINER_CLASS);


        # Build widget content (no need for a builder pattern at this level YAGNI)
        if ($this->isLoadable() === true || $this->isScrollLoadable() === true) {

            if ($this->isLoadable() === true) {
                $this->buildLoadableWidget();
            }

            if ($this->isScrollLoadable() === true) {
                $this->buildScrollLoadableWidget();
            }

            # Set url for asynch calls
            $this->getDataContainer()->setAttribute(self::URL_DATA_ATTR, $this->getUrl());

        } else {
            # Build simple default widget
            $this->buildDefaultWidget();
        }

        # Put the widget data inside the widget container
        $this->getWidgetContainer()->addSubElement($this->getDataContainer());

        # Append the widget data under the root widget dom node
        $this->getNode()->addElement($this->getWidgetContainer());

        $this->bIsLoaded = true;

        return $this;
    }

    /**
     * Build default widget structure
     */
    private function buildDefaultWidget()
    {
        # Build widgets sections
        $this->getHeader()
            ->setAttribute('class', self::WIDGET_HEADER_CLASS);
        $this->getContent()
            ->setAttribute('class', self::WIDGET_CONTENT_CLASS);
        $this->getFooter()
            ->setAttribute('class', self::WIDGET_FOOTER_CLASS);

        $this->getDataContainer()
            ->addSubElements(
                array(
                    $this->getHeader(),
                    $this->getContent(),
                    $this->getFooter()
                )
            );
    }

    /**
     * Build the loadable widget structure
     */
    private function buildLoadableWidget()
    {
        # We just populate some attributes (loadable class and url param) then left the widget empty
        $this->getDataContainer()->setAttribute('class', self::LOADABLE_CLASS);
    }

    /**
     * Build the scroll loadable widget structure
     */
    private function buildScrollLoadableWidget()
    {
        # We just populate some attributes (loadable class and url param) then left the widget empty
        $this->getDataContainer()->setAttribute('class', self::SCROLL_LOADABLE_CLASS);
    }

    /**
     * Build the widget toolbar content
     */
    private function buildToolbar()
    {
        # Buttons group container
        $oBtnGroup = new Div();
        $oBtnGroup->setAttribute('class', array('btn-group',  'btn-group-sm'));

        # Buttons
        $oMinimizeBtn = new Button();
        $oMinimizeBtn->setContent(new FontAwesomeIcon('compress'))
            ->setAttribute('class', array('btn', 'btn-sm', 'btn-default', 'ui-grid-min'));

        $oExpandBtn = new Button();
        $oExpandBtn->setContent(new FontAwesomeIcon('expand'))
            ->setAttribute('class', array('btn', 'btn-sm', 'btn-default', 'ui-grid-expand'));

        $oCloseBtn = new Button();
        $oCloseBtn->setContent(new FontAwesomeIcon('close'))
            ->setAttribute('class', array('btn', 'btn-sm', 'btn-default', 'ui-grid-delete'));

        # Wrap button under btn-group
        $oBtnGroup->addSubElements(
            array(
                $oMinimizeBtn,
                $oExpandBtn,
                $oCloseBtn
            )
        );

        # Build the widget toolbar
        $this->getToolbar()
            ->setAttribute('class', self::WIDGET_TOOLBAR_CLASS)
            ->addSubElement($oBtnGroup);

    }

    /**
     * Get a unique id
     * @return string
     */
    public function getUniqueId()
    {
        return uniqid('widget');
    }

    /**
     * @return Div
     */
    public function getWidgetContainer()
    {
        return $this->oWidgetContainer;
    }

    /**
     * @return Div
     */
    public function getToolbar()
    {
        return $this->oToolbar;
    }

    /**
     * @return Div
     */
    public function getDataContainer()
    {
        return $this->oDataContainer;
    }

    /**
     * @return Div
     */
    public function getHeader()
    {
        return $this->oHeader;
    }

    /**
     * @return Div
     */
    public function getContent()
    {
        return $this->oContent;
    }

    /**
     * @return Div
     */
    public function getFooter()
    {
        return $this->oFooter;
    }

    /**
     * Tell if the widget is asynch loadable
     * @return boolean
     */
    public function isLoadable()
    {
        return $this->bLoadable;
    }

    /**
     * Set widget loadable mode on
     * @param boolean $bIsLoadable
     * @return Widget
     */
    public function setLoadable($bIsLoadable)
    {
        $this->bLoadable = $bIsLoadable;
        return $this;
    }

    /**
     * Tell if the widget is asynch scroll loadable
     * @return boolean
     */
    public function isScrollLoadable()
    {
        return $this->bScrollLoadable;
    }

    /**
     * @param $sScrollLoadable
     * @return Widget
     */
    public function setScrollLoadable($sScrollLoadable)
    {
        $this->bScrollLoadable = $sScrollLoadable;
        return $this;
    }

    /**
     * Return true when Widet use toolbar
     * @return bool
     */
    public function hasToolbar()
    {
        return $this->bToolbar;
    }

    /**
     * Toolbar flag
     * @param $bToolbar
     * @return Widget
     */
    public function setToolbar($bToolbar)
    {
        $this->bToolbar = $bToolbar;
        return $this;
    }

    /**
     * Set Widget parameter
     * @param string $sUrl
     */
    public function setUrl($sUrl)
    {
        $this->sUrl = $sUrl;
        return $this;
    }

    /**
     * Get widget url (only if the widget is loadable ou scroll loadable)
     * @return string
     */
    public function getUrl()
    {
        return $this->sUrl;
    }

}