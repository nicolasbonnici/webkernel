<?php
namespace Library\Core;

/**
 * Simple SOAP web service wrapper
 *
 * @author Nicolas Bonnici <nicolasbonnici@gmail.com>
 */
abstract class WebService
{
    /**
     * SOAP server
     * @var \SoapServer
     */
    private $oSoapServer;

    public function __construct($sWsdl, $sServiceClassName, $bDebug = false)
    {
        if (empty($sWsdl)) {
            throw new WebServiceException('No WSDL definition provided.');
        } elseif (empty($sServiceClassName) || ! class_exists($sServiceClassName)) {
            throw new WebServiceException('Service not found..');
        } else {
            if ($bDebug) {
                ini_set('soap.wsdl_cache_enabled', 0);
            }

            $this->oSoapServer = new \SoapServer($sWsdl);
            $this->oSoapServer->setClass($sServiceClassName);
            $this->oSoapServer->handle();
        }

    }
}

class WebServiceException extends \Exception
{
}
