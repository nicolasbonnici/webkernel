<?php
namespace Library\Core;

/**
 * Command line interface wrapper
 *
 * @todo restrict usage to a scope of bash function
 *  
 * @author Nicolas Bonnici <nicolasbonnici@gmail.com>
 */
class Cli
{
	/**
	 * CLI current username
	 * @var string
	 */
	private $sUser;
	
	public function __construct()
	{
		$this->detectUser();
	}
	
	/**
	 * Run a command on the CLI interface and return result as a string
	 *
	 * @param string $sCommand
	 * @return string
	 */
	private function run($sCommand)
	{
		return shell_exec($sCommand);
	}
	
	/**
	 * Detect the current user 
	 * @return string
	 */
	private function detectUser()
	{
		return $this->sUser = $this->run('whoami');
	}

    public function getUser()
    {
        return $this->sUser;
    }
}

class CliException extends \Exception
{
}
