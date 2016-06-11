<?php
/**
 * Created by PhpStorm.
 * User: niko
 * Date: 11/06/16
 * Time: 17:48
 */

namespace Library\Core\Html\Elements\FormElements;


use Library\Core\Html\FormElement;

class Checkbox extends FormElement
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
    );

}