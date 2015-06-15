<?php
namespace Library\Core\Orm;

/**
 * Entity importer

 */
class EntityImporter
{
	/**
	 * Latest found schema version for Entity import file
	 * @var string
	 */
	private $sLatestSchemaVersionAvailable = '';
	
	
	public function _construct($sEntityname, $sAbsoluteDeployPath = 'app/Entities/Deploy/') 
	{
		if (empty($sEntityname) === true) {
			throw new EntityImporterException('No entity name provided.');
		} elseif (Directory::exists($sAbsoluteDeployPath) === true) {
			throw new EntityImporterException('Dump path not found.');			
		} else {
			
			$aAvailableEntities = Directory::scan($sAbsoluteDeployPath);
			foreach ($aAvailableEntities as $sEntityDump) {
				die(var_dump($sEntityDump));
			}
			
		}
	}
	
	private function getlatestSchemaVersionAvailable()
	{
		
	}
	
	private function isTableExists()
	{
		
	}
	
	private function getDatabaseSchemaVersion()
	{
		
	}
	
	private function import()
	{
		
	}
	
}

class EntityImporterException extends \Exception
{
}

