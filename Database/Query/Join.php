<?php
namespace Library\Core\Database\Query;

/**
 * Query Join component
 *
 * @todo declare this class abstract
 *
 * Class Join
 * @package Library\Core\Database\Query
 */
class Join  extends Where {

    const QUERY_JOIN_TYPE_LEFT    = 'LEFT';
    const QUERY_JOIN_TYPE_INNER   = 'INNER';
    const QUERY_JOIN_TYPE_DEFAULT = 'INNER';

    const QUERY_JOIN_ON      = 'ON';
    const QUERY_JOIN_USING   = 'USING';
    const QUERY_JOIN_DEFAULT = self::QUERY_JOIN_ON;

    /**
     * @var array
     */
    protected $aJoinTypes = array(
        self::QUERY_JOIN_TYPE_LEFT,
        self::QUERY_JOIN_TYPE_INNER
    );

    protected $aJoins = array();

    /**
     * Build the join statement
     */
    protected function buildJoin()
    {
        if (empty($this->aJoins) === false) {
            // @ŧodo
            return self::QUERY_JOIN_DEFAULT . ' ';
        }
        return null;
    }

    /**
     * Add join statement
     *
     * @param string $sColumn
     * @return QueryAbstract
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
     * @return QueryAbstract
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