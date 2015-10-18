<?php
namespace Library\Core\Html\Elements\Helpers;

use Library\Core\Html\Element;

/**
 * Font Awesome icon helper
 *
 * Class FontAwesomeIcon
 * @package Library\Core\Html\Elements\Helpers
 */
class FontAwesomeIcon extends Element
{
    /**
     * HTML dom node label
     * @var string
     */
    protected $sMarkupTag = 'span';

    public function __construct($sIcon)
    {
        parent::__construct();

        $this->setAttribute('class', array('fa', 'fa-' . $sIcon));
    }
}