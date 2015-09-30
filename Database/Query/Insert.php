<?php
namespace Library\Core\Database\Query;

class Insert extends Query {

    const QUERY_INSERT_VALUE = 'VALUES';
    const QUERY_INSERT_TABLE_PREFIX = 'INTO';

    /**
     * insert query fields names and values
     * @var array
     */
    protected $aParameters = array();

    /**
     * Select constructor
     */
    public function __construct()
    {
        $this->setQueryType(Query::QUERY_TYPE_INSERT);
    }

    /**
     * Query build strategy factory
     * @return array
     */
    protected function buildQuery()
    {
        $aFactory =  array(
            $this->getQueryType(),
            $this->prefixFrom(),
            $this->getFrom(),
            $this->buildParameters(),
            $this->buildWhere()
        );
        return array_diff($aFactory, array(null));
    }

    protected function buildParameters()
    {
        return '(' . implode(', ', array_keys($this->getParameters())) . ') ' .
            self::QUERY_INSERT_VALUE . '(' . implode(', ', array_values($this->getParameters())) . ')';
    }

    /**
     * Add a parameter to the insert query
     *
     * @todo handle properly all possible SGBD related data types for value parameter
     *
     * @param string $sFieldName
     * @param mixed int|string $mValue
     * @return Insert
     */
    public function addParameter($sFieldName, $mValue)
    {
        $this->aParameters['`' . $sFieldName . '`'] = ((is_int($mValue) === true) ? $mValue : '"' . $mValue . '"');
        return $this;
    }

    /**
     * Query insert parameters setter
     * @param $aParameters
     * @return Insert
     */
    public function setParameters($aParameters)
    {
        foreach ($aParameters as $sFieldName => $mValue) {
            $this->addParameter($sFieldName, $mValue);
        }
        return $this;
    }

    /**
     * Query insert parameters getter
     * @return array
     */
    public function getParameters()
    {
        return $this->aParameters;
    }

    /**
     * @return string
     */
    private function prefixFrom()
    {
        return ' ' . self::QUERY_INSERT_TABLE_PREFIX . ' ';
    }
}