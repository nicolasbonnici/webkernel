<?php
namespace Library\Core\Database\Query;

abstract class Query  extends  Join {

    const QUERY_TYPE_SELECT = 'SELECT';
    const QUERY_TYPE_INSERT = 'INSERT';
    const QUERY_TYPE_UPDATE = 'UPDATE';
    const QUERY_TYPE_DELETE = 'DELETE';

    const QUERY_FROM        = 'FROM';
    const QUERY_GROUP_BY    = 'GROUP BY';
    const QUERY_ORDER_BY    = 'ORDER BY';
    const QUERY_LIMIT       = 'LIMIT';

    const QUERY_ORDER_DESC = 'DESC';
    const QUERY_ORDER_ASC  = 'ASC';
    const QUERY_DEFAULT_ORDER = self::QUERY_ORDER_DESC;

    /**
     * Query types scope
     * @var array
     */
    protected $aQueryTypes = array(
        self::QUERY_TYPE_SELECT,
        self::QUERY_TYPE_INSERT,
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
     * Query type
     * @var string
     */
    protected $sQueryType;

    /**
     * Query requested columns fields
     * @var array
     */
    protected $aColumns = array();

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
     * @var mixed int|array
     */
    protected $mLimit = array();

    /**
     * instance constructor for child class
     */
    public function __construct()
    {}

    /**
     * __toString overload to directly render the Query
     * @return string
     */
    public function __toString()
    {
        return $this->build();
    }

    /**
     * Query builder
     * @return string
     */
    public function build()
    {
        return implode(' ', $this->buildQuery());
    }

    /**
     * Query build strategy factory
     * @return array            Array of callable methods from instance
     */
    abstract protected function buildQuery();

    /**
     * Query from clause builder
     * @return string
     */
    protected function buildFrom()
    {
        return self::QUERY_FROM .  ' ' . $this->sFrom . '';
    }

    /**
     * Query group by condition builder
     * @return string
     */
    protected function buildGroupBy()
    {
        if (empty($this->aGroupBy) === false) {
            return self::QUERY_GROUP_BY . ' ' . implode(', ', $this->aGroupBy);
        }
        return null;
    }

    /**
     * Query order by condition builder
     * @return string
     */
    protected function buildOrderBy()
    {
        if (empty($this->aOrderBy) === false) {
            return self::QUERY_ORDER_BY . ' ' . implode(', ', $this->aOrderBy) . ' ' . $this->sOrder;
        }
        return null;
    }

    /**
     * Build query limit
     *
     * @return mixed|null|string
     */
    protected function buildLimit()
    {
        $sLimit = '';
        if (empty($this->mLimit) === false) {
            $sLimit .= self::QUERY_LIMIT . ' ';
            $sLimit .= (is_array($this->mLimit) === true)
                ? implode(', ', $this->mLimit)
                : (string) $this->mLimit;
        }
        return $sLimit;
    }

    /**
     * Build Query columns
     * @return string
     */
    protected function buildColumns()
    {
        return implode(', ', $this->aColumns);
    }

    /**
     * Add column
     *
     * @param string $sGroupBy
     * @return Query
     */
    public function addColumn($sColumn)
    {
        $this->aColumns[] = $sColumn;
        return $this;
    }

    /**
     * Add columns
     *
     * @param array $aColumns
     * @return Query
     */
    public function addColumns(array $aColumns)
    {
        $this->aColumns = array_merge($this->aColumns, $aColumns);
        return $this;
    }

    /**
     * Get query columns
     * @return array
     */
    public function getColumns()
    {
        return $this->aColumns;
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
     * Query order by setter
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
     * Get query order by clause
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
     * @param mixed int|array $mLimit     array([int step], [int offset]);
     * @return $this
     */
    public function setLimit($mLimit)
    {
        $this->mLimit = $mLimit;
        return $this;
    }

    /**
     * Query limit accessor
     *
     * @return array
     */
    public function getLimit()
    {
        return $this->mLimit;
    }

    /**
     * Set the Query type
     *
     * @param string $sQueryType        Restricted by $aQueryType array scope
     * @return mixed Query|boolean      The Query instance if saved otherwise FALSE
     */
    public function setQueryType($sQueryType)
    {
        if (in_array($sQueryType, $this->aQueryTypes) === true) {
            $this->sQueryType = $sQueryType;
            return $this;
        } else {
            return false;
        }
    }

    /**
     * Query type accessor
     * @return string
     */
    public function getQueryType()
    {
        return $this->sQueryType;
    }
}