<?php
/**
 * Query component
 */

namespace Library\Core\Query;


abstract class Query {

    const QUERY_TYPE_SELECT = 'SELECT';
    const QUERY_TYPE_UPDATE = 'UPDATE';
    const QUERY_TYPE_DELETE = 'DELETE';

    protected $aQueryTypes = array(
        self::QUERY_TYPE_SELECT,
        self::QUERY_TYPE_UPDATE,
        self::QUERY_TYPE_DELETE,
    );

    protected $sFrom = '';
    protected $aOrderBy = array();
    protected $aGroupBy = array();
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