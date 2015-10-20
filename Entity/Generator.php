<?php
namespace Library\Core\Entity;

use Library\Core\Database\Pdo;
use Library\Core\Database\Query\Operators;
use Library\Core\Database\Query\Select;
use Library\Core\Exception\CoreException;


/**
 * This class can generate large amount of Entities for testing and benchmark purposes
 *
 * Class Generator
 * @package Library\Core\Orm
 */
class Generator
{

    /**
     * Default iteration number when generating entities
     *
     * @var int
     */
    const DEFAULT_ITERATION_NUMBER = 100;

    /**
     * Buffer to store generated entities (Reset at each process call)
     *
     * @var array
     */
    protected $aGeneratedEntities = array();

    /**
     * If generated entity is mapped on other Entity we store the detected mapped Entity id for cache at call purposes
     *
     * @var int
     */
    protected $iForeignEntityId = 0;

    /**
     * Process Entities generation
     *
     * @param Entity $oEntity
     * @param int $iIterationNumber
     * @return bool
     */
    public function process(Entity $oEntity, $iIterationNumber = self::DEFAULT_ITERATION_NUMBER)
    {
        $this->reset();

        $aErrorLog = array();
        for ($i = 0; $i < $iIterationNumber; $i++) {
            $oGeneratedEntity = clone $oEntity;

            foreach ($oGeneratedEntity->getAttributes() as $sAttributeName) {


                # Handle primary and foreign key cases
                if (substr($sAttributeName, 0, 2) === 'id') {
                    # Primary key case
                    $oGeneratedEntity->$sAttributeName = null;
                    continue;
                } elseif (strstr($sAttributeName, '_id') !== false) {

                    # Foreign key case
                    if ($this->iForeignEntityId !== 0) {
                        $aForeignKey = explode('_', $sAttributeName);
                        $sTableName = $aForeignKey[0];
                        $sPrimaryKeyName = $aForeignKey[1];

                        # Find an existent record for foreign key
                        $oSelectQuery = new Select();
                        $oSelectQuery->addColumn($sPrimaryKeyName)
                            ->setFrom($sTableName)
                            ->setLimit(1)
                            ->addWhereCondition(Operators::smaller($sPrimaryKeyName));
                        $oStatement = Pdo::dbQuery($oSelectQuery->build(), array(':' . $sPrimaryKeyName => '100000'));

                        $this->iForeignEntityId = $oStatement->fetchColumn();

                        if ($this->iForeignEntityId === false) {
                            # No foreign Entity Record found we need to store one manually with Mapper in this case
                            die('No mapped entities found on table: ' . $sTableName);
                        }
                    }

                    $oGeneratedEntity->$sAttributeName = $this->iForeignEntityId;
                    continue;

                }

                $oGeneratedEntity->$sAttributeName = $this->getRandomData(
                    $oGeneratedEntity->getDataType($sAttributeName),
                    $sAttributeName
                );
            }

            $aErrorLog[] = $oGeneratedEntity->add();
            $this->aGeneratedEntities[] = $oGeneratedEntity;
        }
        return (in_array(false, $aErrorLog) === false);
    }

    /**
     * Return a random data for a given type
     *
     * @param $sDataType
     * @return mixed
     */
    protected function getRandomData($sDataType, $sFieldName)
    {

        # Handle 'created' and 'lastupdate' fields to directly return current Unix timestamp
        if (
            $sDataType === Attributes::DATA_TYPE_INTEGER &&
            in_array($sFieldName, array('created', 'lastupdate')) === true
        ) {
            return time();
        }

        switch ($sDataType) {
            case Attributes::DATA_TYPE_STRING :
                return $this->getRandomString();
                break;
            case Attributes::DATA_TYPE_INTEGER :
                return $this->getRandomInteger();
                break;
            case Attributes::DATA_TYPE_FLOAT:
                return $this->getRandomFloat();
                break;
            case Attributes::DATA_TYPE_ARRAY:
                return $this->getRandomArray();
                break;
            default :
                return 1;
                break;
        }
    }

    /**
     * Generate a random string value
     *
     * @return string
     */
    protected function getRandomString()
    {
        $iWordLength = rand(2, 32);
        $sAlphabet = 'abcdefghijklmnopqrstuvwyz';
        $sOutput = '';
        for ($i = 0; $i < $iWordLength; $i++) {
            $iPos = rand(0, strlen($sAlphabet) - 1);
            $sOutput .= $sAlphabet[$iPos];
        }
        return (string) $sOutput;
    }

    /**
     * @return int
     */
    protected function getRandomInteger()
    {
        return (int) 1;
    }

    /**
     * Generate a random float value
     * @return float
     */
    protected function getRandomFloat()
    {
        return (float) 3.14;
    }

    /**
     * Generate a random array
     *
     * @return array
     */
    protected function getRandomArray()
    {
        return array(
            $this->getRandomString(),
            $this->getRandomInteger(),
            $this->getRandomFloat()
        );
    }

    /**
     * Flush the generated Entities buffer and detected foreign key value
     *
     * @return bool
     */
    protected function reset()
    {
        $this->aGeneratedEntities = array();
        $this->iForeignEntityId = 0;
        return (bool) (empty($this->aGeneratedEntities) === true);
    }

    /**
     * Generated entities accessor
     *
     * @return array
     */
    public function getGeneratedEntities()
    {
        return $this->aGeneratedEntities;
    }

}
class GeneratorException extends CoreException
{
}
