<?php
namespace Library\Core;

/**
 * Email wrapper class
 *
 * @dependancy \Library\Swift
 * @author Nicolas Bonnci <nicolasbonnici@gmail.com>
 *
 */
class Mail extends \Swift_Message
{

    /**
     * @var \Swift_Message
     */
    protected $oMessage;

    /**
     *
     * @var string
     */
    private $sRecipient;

    /**
     *
     * @var string
     */
    private $sExpeditor;

    /**
     *
     * @var string
     */
    private $sSubject;

    /**
     *
     * @var string
     */
    private $sMessage;

    protected $aRequiredAttributes = array(
        'sTo',
        'sFrom',
        'sSubject',
        'sContent'
    );

    /**
     * Constructeur de la classe \kernel\Email
     * @param type $iEmailId
     * @param type $sEmail
     */
    public function __construct(array $aEmailParam)
    {
        require_once ROOT_PATH . 'Library/Swift/swift_required.php';


        die('ok');

    }

    public function setRecipîent($mRecipientEmail)
    {
        if (is_string($mRecipientEmail)) {

        } elseif (is_array($mRecipientEmail) {

        } else {

        }
    }

}

class MailException extends \Exception
{}
