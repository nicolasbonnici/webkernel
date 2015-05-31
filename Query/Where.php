<?php
/**
 * Created by PhpStorm.
 * User: niko
 * Date: 01/06/15
 * Time: 00:31
 */

namespace Library\Core\Query;


class Where {

    protected $aWhere = array();

    /**
     * Add query where condition
     *
     * @param string $sWhere
     * @return Query
     */
    public function addWhere($sWhere)
    {
        $this->aWhere[] = $sWhere;
        return $this;
    }

    /**
     * Get where conditions
     * @return array
     */
    public function getWhere()
    {
        return $this->aWhere;
    }


}