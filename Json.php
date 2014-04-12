<?php
namespace Library\Core;
/**
 * Json managment class
 *
 *
 * @author Nicolas Bonnici <nicolasbonnici@gmail.com>
 */
class Json
{
    /**
     * A valid json encoded object
     * @var \Library\Core\Json
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
     * @param string $sJson         Json decoded string
     * @throws JsonException
     */
    public function __construct($sJson)
    {
        if(! $this->decode($sJson)) {
            throw new JsonException('Invalid json error code: ' . json_last_error());
        }
    }

    /**
     * Return json object decoded string
     * @return string
     */
    public function __toString()
    {
        assert('$this->isLoaded() === true');
        return $this->encode();
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
        return $this->bIsLoaded;
    }

    /**
     * Return Standalone Json object or attribute value
     * @return object
     */
    public function get($sAttribute = null)
    {
        assert('$this->isLoaded() === true');
        if (is_null($sAttribute)) {
            return $this->oJson;
        } else {
            return $this->oJson->{$sAttribute};
        }
    }

    /**
     * Return Standalone Json object or attribute value
     * @return object
     */
    public function getAsArray()
    {
        assert('$this->isLoaded() === true');
        return $this->convertToArray($this->oJson);
    }

    private function convertToArray($oJsonObject)
    {
        if(!is_object($oJsonObject) && !is_array($oJsonObject)) {
            return $oJsonObject;
        }

        return array_map(array($this, 'convertToArray'), (array) $oJsonObject);
    }

    /**
     *  Return decoded json standalone object
     *
     * @param string $sJson
     * @return boolean
     */
    private function decode($sJson)
    {
        $this->oJson = json_decode($sJson);
        if (is_object($this->oJson)) {
            $this->bIsLoaded = true;
        }
        return $this->bIsLoaded;
    }

    /**
     *  Return encoded json object
     *
     * @return boolean
     */
    private function encode()
    {
        return json_encode($this->oJson);
    }
}

class JsonException extends \Exception
{}
