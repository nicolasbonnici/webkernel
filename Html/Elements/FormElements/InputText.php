<?php


namespace Core\Html\Elements\FormElements;


use Core\Html\FormElement;

/**
 * Created by PhpStorm.
 * User: niko
 * Date: 27/05/15
 * Time: 16:24
 */
class InputText extends FormElement {

    /**
     * HTML dom node label
     * @var string
     */
    protected $sMarkupTag = 'input';

    protected $bAutoCloseMarkup = true;

    protected $aAttributes = array(
        'type' => 'text',
        'name' => '',
        'value' => self::DEFAULT_VALUE,
    );

}