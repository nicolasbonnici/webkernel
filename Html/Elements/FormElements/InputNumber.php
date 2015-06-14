<?php


namespace Core\Html\Elements\FormElements;


use Core\Html\FormElement;

/**
 * Created by PhpStorm.
 * User: niko
 * Date: 27/05/15
 * Time: 16:24
 */
class InputNumber extends FormElement {

    const DEFAULT_VALUE = 0;

    /**
     * HTML dom node label
     * @var string
     */
    protected $sMarkupTag = 'input';

    protected $bAutoCloseMarkup = true;

    protected $aAttributes = array(
        'type' => 'number',
        'name' => '',
        'value' => self::DEFAULT_VALUE,
    );

}