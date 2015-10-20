<?php
namespace Library\Core;

use Library\Core\Database\Pdo;
use Library\Core\Database\Query\Select;
use Library\Core\Entity\Crud;
use Library\Core\Entity\Entity;

/**
 * Dashboard common couch
 * Useful generic methods to build a dashboard
 * 
 * @author niko
 *
 */

class Dashboard extends Crud {
	

    public function __construct($sEntityClassName, $sEntityCollectionClassName, $iPrimaryKey = 0, $mUser = null)	
    {
   		parent::__construct(
				$sEntityClassName,
   				$sEntityCollectionClassName, 
   				$iPrimaryKey, 
   				$mUser
		);
    }
	
	/**
	 * Count any entity with or without parameters
	 *
	 * @param Entity $oEntity
	 * @param array $aWhereClause
	 * @return number
	 */
	public function count(Entity $oEntity, array $aWhereClause = array())
	{
		try {

            $oSelectQuery = new Select();
            $oSelectQuery->addColumn('COUNT(1)')
                ->setFrom($oEntity->getTableName())
                ->addWhereConditions($aWhereClause);
			$oStatement = Pdo::dbQuery($oSelectQuery->build(), $aWhereClause);
			if ($oStatement !== false) {
				return (int) $oStatement->fetchColumn();
			}
			return 0;
		} catch (\Exception $oException) {
			return 0;
		}
	}	
	
}
