<?php
namespace Library\Core\Database\Query;

class Select extends Query {

    /**
     * Select constructor
     */
    public function __construct()
    {
        $this->setQueryType(Query::QUERY_TYPE_SELECT);
    }

    /**
     * Query build strategy factory
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