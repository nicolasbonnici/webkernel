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
     * string representation of the json object
     * @var string
     */
    protected $sJson;

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
     * @param bool|false $bPrettyPrint  Flag to use the pretty print mode to render Json as string
     */
    public function __construct($mJson, $bPrettyPrint = false)
    {
		try {
			if (is_array($mJson) === true && count($mJson) > 0) {
                $this->bIsLoaded = $this->encode($mJson, $bPrettyPrint);
			} elseif (is_string($mJson) === true && empty($mJson) === false) {
                $this->bIsLoaded = $this->decode($mJson, $bPrettyPrint);
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
        return $this->sJson;
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
            return (object) $this->oJson;
        }
        return null;
    }

    /**
     *  Decode a json encoded string
     *
     * @param string $sJson		Json encoded string
     * @param bool|false $bPrettyPrint
     * @return bool
     */
    private function decode($sJson, $bPrettyPrint = false)
    {
        $aJson = json_decode($sJson, true);
        if (empty($aJson) === false) {
            $this->setJson($aJson, $bPrettyPrint);
            return $this->isValid();
        }
        return false;
    }

    /**
     *  Encode an array
     *
     * @param array $aJson
     * @param bool|false $bPrettyPrint
     * @return bool
     */
    private function encode(array $aJson, $bPrettyPrint = false)
    {
        if (empty($aJson) === false) {
            $this->setJson($aJson, $bPrettyPrint);
            return $this->isValid();
        }
        return false;
    }

    /**
     * Set instance Json data
     *
     * @param array $aJson
     * @param bool|false $bPrettyPrint
     */
    public function setJson(array $aJson, $bPrettyPrint = false)
    {
        $this->aJson = $aJson;
        if ($bPrettyPrint === true) {
            $this->sJson = json_encode($this->aJson, JSON_PRETTY_PRINT);
        } else {
            $this->sJson = json_encode($this->aJson);
        }
        $this->oJson = json_decode($this->sJson, true);
    }

}

class JsonException extends \Exception
{}
