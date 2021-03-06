<?php
namespace Library\Core\Database\Query;


class Delete extends QueryAbstract {

    /**
     * Delete constructor
     */
    public function __construct()
    {
        $this->setQueryType(QueryAbstract::QUERY_TYPE_DELETE);
    }

    /**
     * QueryAbstract build strategy factory
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