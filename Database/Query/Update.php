<?php
namespace Library\Core\Database\Query;

class Update extends QueryAbstract {

    const QUERY_UPDATE_SET    = 'SET';
    const QUERY_UPDATE_VALUES = 'VALUES';

    /**
     * Update query fields names and values
     * @var array
     */
    protected $aParameters = array();

    /**
     * Select constructor
     */
    public function __construct()
    {
        $this->setQueryType(QueryAbstract::QUERY_TYPE_UPDATE);
    }

    /**
     * QueryAbstract build strategy factory
     * @return array
     */
    protected function buildQuery()
    {
        $aFactory =  array(
            $this->getQueryType(),
            $this->getFrom(),
            $this->buildParameters(),
            $this->buildWhere()
        );
        return array_diff($aFactory, array(null));
    }

    /**
     * Build Update query parameters
     *
     * @return string
     */
    protected function buildParameters()
    {
        $aOutput = array();
        foreach ($this->getParameters() as $sKey => $mValue) {
            $aOutput[] = '`' . $sKey . '` = ' . self::QUERY_WHERE_BOUNDED_PARAMETER;
        }
        return self::QUERY_UPDATE_SET . ' ' . implode(', ', $aOutput);
    }

    /**
     * Add a parameter the update query
     *
     * @todo handle properly all possible SGBD related data types for value parameter
     *
     * @param string $sFieldName
     * @param mixed int|string $mValue
     * @return Update
     */
    public function addParameter($sFieldName, $mValue)
    {
        $this->aParameters[$sFieldName] = ((is_int($mValue) === true) ? $mValue : '"' . $mValue . '"');
        return $this;
    }

    /**
     * QueryAbstract update parameters setter
     * @param $aParameters
     * @return Update
     */
    public function setParameters($aParameters)
    {
        foreach ($aParameters as $sFieldName => $mValue) {
            $this->addParameter($sFieldName, $mValue);
        }
        return $this;
    }

    /**
     * QueryAbstract update parameters getter
     * @return array
     */
    public function getParameters()
    {
        return $this->aParameters;
    }

}