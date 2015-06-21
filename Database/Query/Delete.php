<?php
namespace Library\Core\Database\Query;


class Delete extends Query {

    /**
     * Delete constructor
     */
    public function __construct()
    {
        $this->setQueryType(Query::QUERY_TYPE_DELETE);
    }

    /**
     * Query build strategy factory
     * @return array
     */
    protected function buildQuery()
    {
        $aFactory = array(
            $this->getQueryType(),
            $this->buildFrom(),
            $this->buildWhere()
        );
        return array_diff($aFactory, array(null));
    }
}