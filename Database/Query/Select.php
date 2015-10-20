<?php
namespace Library\Core\Database\Query;

class Select extends QueryAbstract {

    /**
     * Select constructor
     */
    public function __construct()
    {
        $this->setQueryType(QueryAbstract::QUERY_TYPE_SELECT);
    }

    /**
     * QueryAbstract build strategy factory
     * @return array
     */
    protected function buildQuery()
    {
        $aFactory = array(
            $this->getQueryType(),
            $this->buildColumns(),
            $this->buildFrom(),
            $this->buildJoin(),
            $this->buildWhere(),
            $this->buildGroupBy(),
            $this->buildOrderBy(),
            $this->buildLimit()
        );
        return array_diff($aFactory, array(null));
    }
}