<?php
/**
 * Created by PhpStorm.
 * User: niko
 * Date: 01/06/15
 * Time: 01:15
 */

namespace Library\Core\Query;


class Select extends Query {

    /**
     * Query type
     * @var string
     */
    protected $sQueryType = Query::QUERY_TYPE_SELECT;

    /**
     * Query columns
     * @var array
     */
    protected $aColumns = array();

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

}