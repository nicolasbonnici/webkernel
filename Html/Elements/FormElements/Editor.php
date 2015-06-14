<?php


namespace Core\Html\Elements\FormElements;


use Core\Html\FormElement;

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

    protected $aAttributes = array(
        'class' => array('ui-editor'),
        'contenteditable' => 'true'
    );

}