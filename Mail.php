<?php
namespace Library\Core;

/**
 * Email managment class that implement Swift mailer
 *
 * @dependancy \Library\Swift
 *
 * @author Nicolas Bonnci <nicolasbonnici@gmail.com>
 *        
 */
class Mail
{

    /**
     * Swift Mailer instance
     *
     * @var \Core\Library\Swift
     */
    protected $oSwiftMailer;

    public function __construct()
    {
        // Instantiate swiftMailer with a custom transport agent
    }
}