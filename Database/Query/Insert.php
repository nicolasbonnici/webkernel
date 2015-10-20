<?php
namespace Library\Core\Database\Query;

class Insert extends QueryAbstract {

    const QUERY_INSERT_VALUE               = 'VALUES';
    const QUERY_INSERT_TABLE_PREFIX        = 'INTO';
    const QUERY_INSERT_ON_DUPLICATE_UPDATE = 'ON DUPLICATE KEY UPDATE';

    /**
     * insert query fields names and values
     * @var array
     */
    protected $aParameters = array();

    /**
     * Flag for the ON DUPLICATE KEY UPDATE mode
     * @var bool
     */
    protected $bUpdateOnDuplicate = false;

    /**
     * Select constructor
     */
    public function __construct()
    {
        $this->setQueryType(QueryAbstract::QUERY_TYPE_INSERT);
    }

    /**
     * QueryAbstract build strategy factory
     * @return array
     */
    protected function buildQuery()
    {
        $aFactory =  array(
            $this->getQueryType(),
            $this->prefixTable(),
            $this->getFrom(),
            $this->buildParameters(),
            $this->buildUpdateOnDuplicate()
        );
        return array_diff($aFactory, array(null));
    }

    /**
     * Build insert query parameters
     * @return string
     */
    protected function buildParameters()
    {
        return '(' . implode(', ', array_keys($this->getParameters())) . ') ' .
            self::QUERY_INSERT_VALUE . '(' . implode(', ', array_values($this->getParameters())) . ')';
    }

    /**
     * Handle and build the UPDATE ON DUPLICATE mode
     * @return string
     */
    protected function buildUpdateOnDuplicate()
    {
        $aWhereConditions = $this->getWhere();
        if ($this->bUpdateOnDuplicate === true && empty($aWhereConditions) === false) {
            return ' ' . self::QUERY_INSERT_ON_DUPLICATE_UPDATE . ' ' . $this->buildWhereParameters();
        }
        return '';
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
        $this->aParameters['`' . $sFieldName . '`'] = ((is_int($mValue) === true) ? $mValue : "'" . $mValue . "'");
        return $this;
    }

    /**
     * QueryAbstract insert parameters setter
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
     * QueryAbstract insert parameters getter
     * @return array
     */
    public function getParameters()
    {
        return $this->aParameters;
    }

    /**
     * Activate the UPDATE ON DUPLICATE mode
     *
     * @param boolean $bUpdateOnDuplicate
     */
    public function setUpdateOnDuplicate($bUpdateOnDuplicate)
    {
        $this->bUpdateOnDuplicate = (bool) $bUpdateOnDuplicate;
    }

    /**
     * @return string
     */
    private function prefixTable()
    {
        return self::QUERY_INSERT_TABLE_PREFIX;
    }
}