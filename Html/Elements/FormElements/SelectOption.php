<?php


namespace Core\Html\Elements\FormElements;


use Core\Html\FormElement;

/**
 * Select Option FormElement
 * User: niko
 * Date: 27/05/15
 * Time: 16:24
 */
class SelectOption extends FormElement {

    /**
     * HTML dom node label
     * @var string
     */
    protected $sMarkupTag = 'option';

    protected $aAttributes = array();

}