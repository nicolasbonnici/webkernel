<?php
/**
 * Query component
 */

namespace Library\Core\Query;


abstract class Query {

    const QUERY_ORDER_DESC = 'DESC';
    const QUERY_ORDER_ASC  = 'ASC';
    const QUERY_DEFAULT_ORDER = self::QUERY_ORDER_DESC;

    const QUERY_TYPE_SELECT = 'SELECT';
    const QUERY_TYPE_UPDATE = 'UPDATE';
    const QUERY_TYPE_DELETE = 'DELETE';

    /**
     * Query types scope
     * @var array
     */
    protected $aQueryTypes = array(
        self::QUERY_TYPE_SELECT,
        self::QUERY_TYPE_UPDATE,
        self::QUERY_TYPE_DELETE,
    );

    /**
     * Query order types scope
     * @var array
     */
    protected $aQueryOrders = array(
        self::QUERY_ORDER_ASC,
        self::QUERY_ORDER_DESC
    );

    /**
     * Query targeted table
     * @var string
     */
    protected $sFrom = '';

    /**
     * Query order
     * @var string
     */
    protected $sOrder = self::QUERY_DEFAULT_ORDER;
    /**
     * Query order by fields
     * @var array
     */
    protected $aOrderBy = array();

    /**
     * Group by fields
     * @var array
     */
    protected $aGroupBy = array();

    /**
     * Query limit
     * @var array
     */
    protected $aLimit = array();

    public function __construct()
    {

    }

    public function build()
    {

    }

    /**
     * Set query from clause
     *
     * @param $sFromTable
     * @return $this
     */
    public function setFrom($sFromTable)
    {
        $this->sFrom = $sFromTable;
        return $this;
    }

    /**
     * Query from clause getter
     * @return string
     */
    public function getFrom()
    {
        return $this->sFrom;
    }

    /**
     * Add column
     *
     * @param string $sOrderBy
     * @return Query
     */
    public function setOrderBy(array $aOrderBy)
    {
        $this->aOrderBy = array_merge($this->aOrderBy, $aOrderBy);
        return $this;
    }

    /**
     * Get query columns
     * @return array
     */
    public function getOrderBy()
    {
        return $this->aOrderBy;
    }

    /**
     * Set query order type
     * @param string $sOrder
     * @return $this|bool
     */
    public function setOrder($sOrder)
    {
        if (in_array($sOrder, $this->aQueryOrders)) {
            $this->sOrder = $sOrder;
            return $this;
        }
        return false;
    }

    /**
     * Query oder type accessor
     * @return string
     */
    public function getOrder()
    {
        return $this->sOrder;
    }

    /**
     * Add columns
     *
     * @param array $aGroupBy
     * @return Query
     */
    public function setGroupBy(array $aGroupBy)
    {
        $this->aGroupBy = array_merge($this->aGroupBy, $aGroupBy);
        return $this;
    }

    /**
     * Get query columns
     * @return array
     */
    public function getGroupBy()
    {
        return $this->aGroupBy;
    }

    /**
     * Query limit setter
     *
     * @param array $aLimit     array([int step], [int offset]);
     * @return $this
     */
    public function setLimit(array $aLimit)
    {
        $this->aLimit = $aLimit;
        return $this;
    }

    /**
     * Query limit accessor
     *
     * @return array
     */
    public function getLimit()
    {
        return $this->aLimit;
    }
}