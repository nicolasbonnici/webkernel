<?php
namespace Library\Core\Html\Elements\FormElements;

use Library\Core\Html\FormElement;

/**
 * Created by PhpStorm.
 * User: niko
 * Date: 27/05/15
 * Time: 16:24
 */
class InputHidden extends FormElement {

    /**
     * HTML dom node label
     * @var string
     */
    protected $sMarkupTag = 'input';

    protected $bAutoCloseMarkup = true;

    protected $aAttributes = array(
        'type' => 'hidden',
        'name' => '',
        'value' => self::DEFAULT_VALUE,
    );

}