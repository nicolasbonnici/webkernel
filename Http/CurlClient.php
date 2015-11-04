<?php
namespace Library\Core;


/**
 * Simple cURL abstraction
 * 
 * Handle post|put|delete|get|head requests 
 */
class CurlClient
{
	/**
	 * CurlClient instance
	 * @var ressource cURL session id
	 */
    private $_oCurlSession;
    
    /**
     * Last call response
     * @var string
     */
    private $_sLastResponse;
    
	/**
	 * Last call response informations
	 * @var array
	 */    
    private $_aResponseInfo;
    
    /**
     * Instance constructor
     */
    public function __construct()
    {
        $this->reset();
    }
    
    /**
     * Resets the session and sets the defaults.
     */
    public function reset()
    {
        $this->_oCurlSession = curl_init();
        $this->_sLastResponse = '';
        $this->_aResponseInfo = array();
        curl_setopt($this->_oCurlSession, CURLOPT_AUTOREFERER, 1);
        curl_setopt($this->_oCurlSession, CURLOPT_COOKIESESSION, 1);
        curl_setopt($this->_oCurlSession, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($this->_oCurlSession, CURLOPT_MAXREDIRS, 5);
        curl_setopt($this->_oCurlSession, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($this->_oCurlSession, CURLOPT_CONNECTTIMEOUT, 15);
        curl_setopt($this->_oCurlSession, CURLOPT_TIMEOUT, 100);
        return $this;
    }
    
    /**
     * Returns all transfer for debug purpose.
     */
    public function debug()
    {
        curl_setopt($this->_oCurlSession, CURLOPT_HEADER, 1);
        curl_setopt($this->_oCurlSession, CURLINFO_HEADER_OUT, 1);
        curl_setopt($this->_oCurlSession, CURLOPT_VERBOSE, 1);
        return $this;
    }
    
    /**
     * Performs HTTP GET request.
     *
     * @param String url location 
     * @return String response
     */
    public function get($url)
    {
        curl_setopt($this->_oCurlSession, CURLOPT_HTTPGET, 1);
        $this->url($url);
        return $this->exec();
    }
    
    /**
     * Performs HTTP POST request.
     *
     * @param String url location
     * @param Array post params
     * @return String
     */
    public function post($url, $params = array())
    {
        curl_setopt($this->_oCurlSession, CURLOPT_POST, 1);
        $this->url($url);
        curl_setopt($this->_oCurlSession, CURLOPT_POSTFIELDS, $params);
        return $this->exec();
    }
    
    /**
     * Performs HTTP PUT request.
     *
     * @param String url location
     * @param array put params
     * @return String response
     */
    public function put($url, $params = array())
    {
        curl_setopt($this->_oCurlSession, CURLOPT_PUT, 1);
        $this->url($url);
        curl_setopt($this->_oCurlSession, CURLOPT_POSTFIELDS, $params);
        return $this->exec();
    }
    
    /**
     * Performs HTTP DELETE request.
     *
     * @param String url location.
     * @return String response.
     */
    public function delete($url)
    {
        curl_setopt($this->_oCurlSession, CURLOPT_CUSTOMREQUEST, 'DELETE');
        $this->url($url);
        return $this->exec();
    }
    
    /**
     * Performs HTTP HEAD request.
     *
     * @param String url location
     * @return String response
     */
    public function head($url)
    {
        curl_setopt($this->_oCurlSession, CURLOPT_NOBODY, 1);
        $this->url($url);
        return $this->exec();
    }
    
    /**
     * Performs File upload
     *
     * @param String url location
     * @param String full path of the file to be uploaded
     * @return String response
     */
    public function upload($url, $filename)
    {
        curl_setopt($this->_oCurlSession, CURLOPT_UPLOAD, 1);
        $this->url($url);
        curl_setopt($this->_oCurlSession, CURLOPT_INFILE, $filename);
        return $this->exec();
    }
    
    /**
     * Performs basic http authentication.
     *
     * @param String username
     * @param String password
     */
    public function authenticate($username, $password)
    {
        curl_setopt($this->_oCurlSession, CURLOPT_HTTPAUTH, CURLAUTH_ANY);
        curl_setopt($this->_oCurlSession, CURLOPT_USERPWD, "{$username}:{$password}");
        return $this;
    }
    
    /**
     * Sets custom headers.
     *
     * @param Array headers
     */
    public function headers($headers = array())
    {
        curl_setopt($this->_oCurlSession, CURLOPT_HTTPHEADER, $headers);
        return $this;
    }
    
    /**
     * Sets custom port.
     *
     * @param Int port number.
     */
    public function port($port_number = 80)
    {
        curl_setopt($this->_oCurlSession, CURLOPT_PORT, $port_number);
        return $this;
    }
    
    /**
     * Sets custom referer.
     *
     * @param String referer
     */
    public function referer($referer)
    {
        curl_setopt($this->_oCurlSession, CURLOPT_REFERER, $referer);
        return $this;
    }
    
    /**
     * Sets custom useragent.
     *
     * @param String agent
     */
    public function agent($agent)
    {
        curl_setopt($this->_oCurlSession, CURLOPT_USERAGENT, $agent);
        return $this;
    }
    
    /**
     * Sets url location for the request.
     *
     * @param String url
     */
    protected function url($url)
    {
        curl_setopt($this->_oCurlSession, CURLOPT_URL, $url);
    }
    
    /**
     * Closes the current curl session.
     *
     * Call to reset should be used to start new session.
     */
    public function close()
    {
        curl_close($this->_oCurlSession);
        return $this;
    }
    
    /**
     * Fetches info from the last response.
     *
     * @param String key if given returns only the requested param
     * @return Array or String based on the key param
     */
    public function info($key = null)
    {
        if (is_null($key)) {
            return $this->_aResponseInfo;
        } else {
            return $this->_aResponseInfo[$key];
        }
    }
    /**
     * Fetches the last response status code.
     *
     * @return Int
     */
    public function code()
    {
        return $this->info('http_code');
    }
    
    /**
     * Executes the request and populates last_response
     * info and error values.
     *
     * @return String last response
     */
    protected function exec()
    {
        $this->_sLastResponse = curl_exec($this->_oCurlSession);
        $this->_aResponseInfo = curl_getinfo($this->_oCurlSession);
        $this->_error = curl_error($this->_oCurlSession);
        return $this->_sLastResponse;
    }
    
    /**
     * Returns last error message if any.
     *
     * @return String blank if no error occured
     */
    public function error()
    {
        return $this->_error;
    }
    
    /**
     * Housekeeping.
     */
    public function __destruct()
    {
        if ($this->_oCurlSession) curl_close($this->_oCurlSession);
        $this->last_response = '';
    }
}
