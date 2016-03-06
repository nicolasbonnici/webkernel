<?php
namespace Library\Core\Html\Elements;

use Library\Core\Html\Element;

/**
 * Class Table
 * @package Library\Core\Html\Elements
 */
class Table extends Element
{
    /**
     * HTML dom node label
     * @var string
     */
    protected $sMarkupTag = 'table';

    protected $oHeader;
    protected $oBody;

    public function __construct()
    {
        parent::__construct();

        $this->oHeader = new TableHeader();
        $this->oBody   = new TableBody();

        $this->addSubElement($this->getHeader());
        $this->addSubElement($this->getBody());
    }

    public function setHeaders(array $aHeaders)
    {
        $oTr = new TableRow();
        foreach ($aHeaders as $sHeaderName) {
            $oTh = new TableHeaderData();
            $oTh->setContent($sHeaderName);
            $oTr->addSubElement($oTh);
        }
        $this->getHeader()->addSubElement($oTr);
    }


    /**
     * @return TableHeader
     */
    public function getHeader()
    {
        return $this->oHeader;
    }

    /**
     * @return TableBody
     */
    public function getBody()
    {
        return $this->oBody;
    }

}