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
	private $sUser;
	
	public function __construct()
	{
		$this->detectUser();
	}
	
	/**
	 * Run a command on the CLI interface and return result as a string
	 *
     * @todo seems like a big security hole :p
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
