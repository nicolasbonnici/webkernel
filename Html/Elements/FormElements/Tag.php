<?php


namespace Library\Core\Html\Elements\FormElements;


use Library\Core\Html\FormElement;

/**
 * TagModel FormElement
 * User: niko
 * Date: 27/05/15
 * Time: 16:24
 */
class Tag extends FormElement {

    /**
     * HTML dom node label
     * @var string
     */
    protected $sMarkupTag = 'select';

    protected $aAttributes = array(
        'class' => array('form-control', 'ui-tag')
    );

}