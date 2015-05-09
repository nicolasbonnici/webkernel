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
			$sWhereCondition = '';
			if (count($aWhereClause) > 0 && (count($aWhereClause) % 2) === 0) {
				$sWhereCondition = ' sWHERE ';
				foreach ($aWhereClause as $sField => $mValue) {
					$sWhereCondition .= ' `' . $sField . '` = :' . $sField;
				}
			}
			
			$sQuery = 'SELECT COUNT(1) FROM `' . $oEntity::TABLE_NAME . '`' . $sWhereCondition;
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