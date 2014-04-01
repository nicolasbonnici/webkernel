<?php
namespace Library\Core;

/**
 * Gestion des CoreExceptions
 *
 * @author Nicolas BONNICI
 */
class CoreException extends \Exception
{

    public function __construct($message, $code = 0, Exception $previous = null)
    {
        
        // traitement
        parent::__construct($message, $code, $previous);
    }

    public function __toString()
    {
        return __CLASS__ . ": [{$this->code}]: {$this->message}\n";
    }
}

?>
