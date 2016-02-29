<?php
namespace Library\Core\App\Widgets;


use Library\Core\Html\Elements\Div;

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
    protected $oWidgetDiv;

    /**
     * The widget data content wrapper
     * @var Div
     */
    protected $oWidgetDataContainer;

    /**
     * The widget header
     * @var Div
     */
    protected $oWidgetHeader;

    /**
     * The widget content
     * @var Div
     */
    protected $oWidgetContent;

    /**
     * The widget footer
     * @var Div
     */
    protected $oWidgetFooter;

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
        $this->oWidgetDiv = new Div();
        $this->oWidgetDataContainer = new Div();
        $this->oWidgetHeader = new Div();
        $this->oWidgetContent= new Div();
        $this->oWidgetFooter = new Div();
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
        $this->getWidgetDiv()->setAttribute('class', self::WIDGET_CLASS);

        # Build widget data wrap
        $this->getWidgetDataContainer()->setAttribute('class', self::WIDGET_DATA_CONTAINER_CLASS);


        # YAGNI at this level but we can have an abstract factory here
        if ($this->isLoadable() === true || $this->isScrollLoadable() === true) {

            if ($this->isLoadable() === true) {
                $this->buildLoadableWidget();
            }

            if ($this->isScrollLoadable() === true) {
                $this->buildScrollLoadableWidget();
            }

            $this->getWidgetDataContainer()->setAttribute(self::URL_DATA_ATTR, $this->getUrl());

        } else {
            # Build simple default widget
            $this->buildWidget();
        }

        # Put the widget data inside the widget wrapper
        $this->getWidgetDiv()->addSubElement($this->getWidgetDataContainer());

        # Append the widget data under the root widget dom node
        $this->getNode()->addElement($this->getWidgetDiv());

        $this->bIsLoaded = true;

        return $this;
    }

    /**
     * Build default widget structure
     */
    private function buildWidget()
    {
        # Build widgets sections
        $this->getWidgetHeader()->setAttribute('class', self::WIDGET_HEADER_CLASS);
        $this->getWidgetContent()->setAttribute('class', self::WIDGET_CONTENT_CLASS);
        $this->getWidgetFooter()->setAttribute('class', self::WIDGET_FOOTER_CLASS);

        $this->getWidgetDataContainer()
            ->addSubElement($this->getWidgetHeader())
            ->addSubElement($this->getWidgetContent())
            ->addSubElement($this->getWidgetFooter());
    }

    /**
     * Build the loadable widget structure
     */
    private function buildLoadableWidget()
    {
        # We just populate some attributes (loadable class and url param) then left the widget empty
        $this->getWidgetDataContainer()->setAttribute('class', self::LOADABLE_CLASS);
    }

    /**
     * Build the scroll loadable widget structure
     */
    private function buildScrollLoadableWidget()
    {
        # We just populate some attributes (loadable class and url param) then left the widget empty
        $this->getWidgetDataContainer()->setAttribute('class', self::SCROLL_LOADABLE_CLASS);
    }

    /**
     * @return Div
     */
    public function getWidgetDiv()
    {
        return $this->oWidgetDiv;
    }

    /**
     * @return Div
     */
    public function getWidgetDataContainer()
    {
        return $this->oWidgetDataContainer;
    }

    /**
     * @return Div
     */
    public function getWidgetHeader()
    {
        return $this->oWidgetHeader;
    }

    /**
     * @return Div
     */
    public function getWidgetContent()
    {
        return $this->oWidgetContent;
    }

    /**
     * @return Div
     */
    public function getWidgetFooter()
    {
        return $this->oWidgetFooter;
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
     * Set Widget parameter
     * @param string $sUrl
     */
    public function setUrl($sUrl)
    {
        $this->sUrl = $sUrl;
        return $this;
    }

    public function getUrl()
    {
        return $this->sUrl;
    }

}