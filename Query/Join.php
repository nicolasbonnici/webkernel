<?php
/**
 * Created by PhpStorm.
 * User: niko
 * Date: 01/06/15
 * Time: 00:31
 */

namespace Library\Core\Query;


class Join {

    protected $aJoins = array();


    /**
     * Add join statement
     *
     * @param string $sColumn
     * @return Query
     */
    public function addJoin($sJoin)
    {
        $this->aJoins[] = $sJoin;
        return $this;
    }

    /**
     * Add join statements
     *
     * @param array $aJoins
     * @return Query
     */
    public function addJoins(array $aJoins)
    {
        $this->aJoins = array_merge($this->aJoins, $aJoins);
        return $this;
    }

    /**
     * Get join statements
     * @return array
     */
    public function getJoins()
    {
        return $this->aJoins;
    }

}