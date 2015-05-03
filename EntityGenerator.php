<?php
namespace Library\Core;

use Library\Core\Entity;

/**
 * Entity Generator to generate test data
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
	protected $aEntities = array();
	
	public function __construct()
	{
		// @todo prendre un tableau et bouclé dessus pour generer les entités
	}
	
}

class EntityGeneratorException extends \Exception
{
}
