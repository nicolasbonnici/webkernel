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
     * @var string
     */
    protected $oJson;

    /**
     * Array to store Json decoded data
     * @var array
     */
    protected $aJson;

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
                $this->bIsLoaded = $this->encode($mJson);
			} elseif (is_string($mJson) === true && empty($mJson) === false) {
                $this->bIsLoaded = $this->decode($mJson);
			} else {
				throw new JsonException('Invalid constructor parameter type, must be: Array|String');
			}

			return $this->oJson;
		} catch (\Exception $oException) {
            echo $oException->getMessage();
			return null;
		}
    }

    /**
     * Return json object decoded string
     * @return string
     */
    public function __toString()
    {
        return (string) $this->getAsObject();
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
        return (json_last_error() === 0);
    }

    /**
     * Tell if the current instance is already load with some Json
     * @return boolean
     */
    public function isLoaded()
    {
        return (bool) $this->bIsLoaded;
    }

    /**
     * Return Standalone Json object or attribute value
     * @return mixed
     */
    public function get($sAttribute = null)
    {
        assert('$this->isLoaded() === true');
        if (is_null($sAttribute)) {
            return $this->getAsArray();
        } else {
            return isset($this->aJson[$sAttribute]) === true ? $this->aJson[$sAttribute] : null;
        }
    }

    /**
     * Return array representation of the json object
     *
     * @return array
     */
    public function getAsArray()
    {
        if ($this->isLoaded() === true) {
            return $this->aJson;
        }
        return null;
    }

    /**
     * Return json object
     *
     * @return object
     */
    public function getAsObject()
    {
        if ($this->isLoaded() === true) {
            return $this->oJson;
        }
        return null;
    }

    /**
     *  Decode a json encoded string
     *
     * @param string $sJson		Json encoded string
     * @return boolean
     */
    private function decode($sJson)
    {
        $aJson = json_decode($sJson, true);
        if (empty($aJson) === false) {
            $this->setJson($aJson);
            return $this->isValid();
        }
        return false;
    }

    /**
     *  Encode an array
     *
     * @param boolean
     */
    private function encode(array $aJson)
    {
        if (empty($aJson) === false) {
            $this->setJson($aJson);
            return $this->isValid();
        }
        return false;
    }

    /**
     * @param array $aJson
     */
    public function setJson(array $aJson)
    {
        $this->aJson = $aJson;
        $this->oJson = json_encode($this->aJson, JSON_PRETTY_PRINT);
    }

}

class JsonException extends \Exception
{}
