<?php


namespace Core\Html\Elements\FormElements;


use Core\Html\FormElement;

/**
 * Autocomplete FormElement
 * User: niko
 * Date: 27/05/15
 * Time: 16:24
 */
class Autocomplete extends FormElement {

    /**
     * HTML dom node label
     * @var string
     */
    protected $sMarkupTag = 'select';

    protected $aAttributes = array(
        'class' => array('form-control', 'ui-autocomplete')
    );

}