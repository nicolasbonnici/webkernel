<?php
namespace Library\Core;

use Library\Core\Entity;

/**
 * Entity Generator to generate random data records
 * 
 * @author niko <nicolasbonnici@gmail.com>
 *
 */
class EntityGenerator
{

	/**
	 * Entities to generate
	 * @var array
	 */
	protected $aEntitiesToGenerate = array();
	
	public function __construct(array $aEntities = array())
	{
		if (count($aEntities) > 0) {
			$this->aEntitiesToGenerate = $aEntities;
		} else {
			// Per default grab all entities
			$this->aEntitiesToGenerate = App::buildEntities();
		}
		// @todo prendre un tableau et bouclé dessus pour generer les entités
	}
	
	public function generate($iRecordNumber = 100)
	{
		foreach ($this->aEntitiesToGenerate as $sEntity) {
			$oEntity = new $sEntity();
			foreach ($oEntity->loadFields() as $sField=>$aDbInfo) {
				
				// Selon le type de champs ou le nom du champs setter des données de demo
				
				switch ($sFieldType) {
					case 'string':
							// @todo decliner avec constantes et en profiter pour decliner un composant EntityField
						break;
				}
				
			}
		}
	}	
	
}

class EntityGeneratorException extends \Exception
{
}
