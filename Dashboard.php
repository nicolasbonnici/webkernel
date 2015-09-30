<?php
namespace Library\Core;

use Library\Core\Database\Database;
use Library\Core\Orm\Entity;

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
     * @todo implement query component
     *
	 * @param Entity $oEntity
	 * @param array $aWhereClause
	 * @return number
	 */
	public function count(Entity $oEntity, array $aWhereClause = array())
	{
		try {


            /**
             * @todo Implement Query Select
             */

			$sWhereCondition = '';
			if (count($aWhereClause) > 0) {
				$sWhereCondition = ' WHERE ';
				foreach ($aWhereClause as $sField => $mValue) {
					$sWhereCondition .= ' `' . $sField . '` = :' . $sField;
				}
			}

			$sQuery = 'SELECT COUNT(1) FROM `' . $oEntity->getTableName() . '`' . $sWhereCondition;
			$oStatement = Database::dbQuery($sQuery, $aWhereClause);
			if ($oStatement !== false) {
				return (int) $oStatement->fetchColumn();
			}
			return 0;
		} catch (\Exception $oException) {
			return 0;
		}
	}	
	
}
