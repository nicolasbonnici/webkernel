<?php
namespace Library\Core\Html\Elements\FormElements;

use Library\Core\Html\Elements\Helpers\FontAwesomeIcon;
use Library\Core\Html\FormElement;

/**
 * Simple HTML5 toggle component
 *
 * Class Toggle
 * @package Library\Core\Html\Elements\FormElements
 */
class Toggle extends FormElement
{
    /**
     * HTML dom node label
     * @var string
     */
    protected $sMarkupTag = 'input';

    protected $bAutoCloseMarkup = true;

    protected $aAttributes = array(
        'type' => 'checkbox',
        'name' => '',
        'value' => self::DEFAULT_VALUE,
        'class' => 'ui-checkbox',
        'data-on-color' => 'info',
        'data-size' => 'large'
    );

    /**
     * Toggle constructor.
     *
     * @param bool $bSelectAll
     * @param string $sClassname If $bSelectAll === true the other toggles class name
     */
    public function __construct($bSelectAll = false, $sClassname)
    {
        parent::__construct();

        $oCheck = new FontAwesomeIcon('fa-check-square-o');
        $oNotCheck =  new FontAwesomeIcon('fa-square-o');

        $this->setAttributes(
            array(
                'class' => 'ui-checkbox ui-select-all',
                'checkbox-selector' => '.' . $sClassname,
                'data-on-text' => 'On',
                'data-off-text' => 'Off'
            )
        );
    }
}