<?php
namespace Library\Core\Dashboard;

use Library\Core\Database\Pdo;
use Library\Core\Database\Query\Select;
use Library\Core\Entity\Entity;

/**
 * Dashboard common layer, useful generic methods to build a dashboard
 *
 * Class Dashboard
 * @package Library\Core
 */
class Dashboard {

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
