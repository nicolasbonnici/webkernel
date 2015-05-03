<?php
namespace Library\Core;

/**
 * Dashboard common couch
 * Useful generic methods to build a dashboard
 * 
 * @author niko
 *
 */

class Dashboard extends Crud {
	
	/**
	 * Count the current number of records for a given Entity
	 * @todo terminer cette methode en mode generique
	 * @return integer
	 */
	/**
	 * 
	 * @param Entity $oEntity
	 * @param array $aWhereClause
	 * @return number
	 */
	public function count(Entity $oEntity, array $aWhereClause = array())
	{
		try {
			$sWhereCondition = '';
			foreach ($aWhereClause as $sField => $mValue) {
				$sWhereCondition .= ' `' . $sField . '` = :' . $sField;
			}
			
			$sQuery = 'SELECT COUNT(1) FROM `' . $oEntity::TABLE_NAME . '` WHERE' . $sWhereCondition;
			$oStatement = Database::dbQuery($sQuery, $aWhereClause);
			if ($oStatement !== false) {
				return $oStatement->fetchColumn();
			}
			return 0;
		} catch (\Exception $oException) {
			return 0;
		}
	}	
	
}

?>