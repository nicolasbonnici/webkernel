<?php


namespace Core\Html\Elements\FormElements;


use Core\Html\FormElement;

/**
 * Textarea FormElement
 * User: niko
 * Date: 27/05/15
 * Time: 16:24
 */
class Textarea extends FormElement {

    /**
     * HTML dom node label
     * @var string
     */
    protected $sMarkupTag = 'textarea';

    protected $aAttributes = array(
        'class' => 'form-control',
        'row' => 3,
        'name' => '',
    );

}