<?php


namespace Library\Core\Html\Elements\FormElements;


use Library\Core\Html\FormElement;

/**
 * WYSIWYG Editor FormElement
 * User: niko
 * Date: 27/05/15
 * Time: 16:24
 */
class Editor extends FormElement {

    /**
     * HTML dom node label
     * @var string
     */
    protected $sMarkupTag = 'div';

    public function __construct($bAirMode = false)
    {
        parent::__construct();

        $this->setAttributes(array(
            'class' => array('ui-editor'),
            'data-name' => ''
        ));

        if ($bAirMode === true) {
            $this->setAttribute('class', 'air-mode');
        }
    }

}