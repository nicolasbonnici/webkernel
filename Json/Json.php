<?php
namespace Library\Core\Json;
/**
 * Json managment class
 *
 * @author Nicolas Bonnici <nicolasbonnici@gmail.com>
 */
class Json
{
    /**
     * A valid json encoded object
     * @var \Library\Core\Json\Json
     */
    protected $oJson;

    /**
     * Flag to tell if Json object is currently loaded
     * @var boolean
     */
    protected $bIsLoaded = false;

    /**
     *  Instance constructor
     *
     * @param mixed string|array $mJson	Array or a Json encoded string
     * @throws JsonException
     */
    public function __construct($mJson)
    {
		try {
			if (is_array($mJson) === true && count($mJson) > 0) {
				if($this->encode($mJson) === false) {
					throw new JsonException('Unable to encode Array, json error code: ' . $this->getLastError());
				}
			} elseif (is_string($mJson) === true && empty($mJson) === false) {
				if($this->decode($mJson) === false) {
					throw new JsonException('Unable to decode JSON, json error code: ' . $this->getLastError());
				}
			} else {
				throw new JsonException('Invalid constructor parameter type, must be: Array|String');
			}
			return $this->oJson;			
		} catch (\Exception $oException) {
			return null;
		}        
    }

    /**
     * Return json object decoded string
     * @return string
     */
    public function __toString()
    {
        assert('$this->isLoaded() === true');
        return $this->oJson;
    }

    /**
     * Last json error code
     * @return integer
     */
    public function getLastError()
    {
    	return json_last_error();
    }
    
    /**
     * Tell if the json code provided at instance is valid
     * @return boolean
     */
    public function isValid()
    {
        // @todo also return the parse error line and character
        return (json_last_error() === 0);
    }

    /**
     * Tell if the current instance is already load with some Json
     * @return boolean
     */
    public function isLoaded()
    {
        return $this->bIsLoaded;
    }

    /**
     * Return Standalone Json object or attribute value
     * @return mixed
     */
    public function get($sAttribute = null)
    {
        assert('$this->isLoaded() === true');
        if (is_null($sAttribute)) {
            return $this->aJson;
        } else {
            return isset($this->aJson[$sAttribute]) === true ? $this->aJson[$sAttribute] : null;
        }
    }

    /**
     * Return array reprensatation of the json object
     *
     * @return object
     */
    public function getAsArray()
    {
        assert('$this->isLoaded() === true');
        return $this->aJson;
    }

    /**
     *  Decode a json encoded string
     *
     * @param string $sJson		Json encoded string
     * @return boolean
     */
    private function decode($sJson)
    {
        $this->aJson = json_decode($sJson, JSON_PRETTY_PRINT);
        if (is_array($this->aJson) === true) {
	    	$this->oJson = $sJson;
            $this->bIsLoaded = true;
        }
        return $this->bIsLoaded;
    }

    /**
     *  Encode an array
     *
     * @param boolean
     */
    private function encode($aJson)
    {
    	$this->aJson = $aJson;
        $this->oJson = json_encode($this->aJson, JSON_PRETTY_PRINT);
       	$this->bIsLoaded = (is_string($this->oJson) === true);
        return $this->bIsLoaded;        
    }
}

class JsonException extends \Exception
{}
