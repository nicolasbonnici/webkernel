<?php
namespace Library\Core;

/**
 * Command line interface wrapper 
 *  
 * @author Nicolas Bonnici <nicolasbonnici@gmail.com>
 */
class Cli
{
	/**
	 * CLI current username
	 * @var string
	 */
	private $sUsername;
	
	public function __construct()
	{
		$this->detectUser();
	}
	
	/**
	 * Run a command on the CLI interface with the apache2 user
	 * and return result as a string
	 * 
	 * @param string $sCommand
	 * @return string
	 */
	private function run($sCommand)
	{
		return passthru($sCommand);
	}
	
	/**
	 * Detect the current user 
	 * @return string
	 */
	private function detectUser()
	{
		return $this->sUsername = passthru('whoami');
	}
	
}

class CliException extends \Exception
{
}
