<?php

namespace Library\Core;

/**
 * Manage SGBD with PDO
 */
class Database {

    /**
     * @var object Library\Core\Database instance
     */
    static private $_instance;

    /**
     * @var object PDO instance
     */
    static protected $_link;

    /**
     * @var string
     */
    static protected $_driver = '';
    static protected $_host = '';
    static protected $_name = '';
    static protected $_user = '';
    static protected $_pass = '';

    static protected $_errors = array();

    static protected $_sLastLink = array();
    static protected $_aBenchmark = array(
        'master' => array(
            'time'          => 0.0,
            'queries_count' => 0,
            'queries_list'  => array()
        ),
        'slave' => array(
            'time'          => 0.0,
            'queries_count' => 0,
            'queries_list'  => array()
        )
    );



    /**
     * Constructeur
     *
     * @param void
     * @return void
     * @see PDO::__construct()
     * @access private
     */
    public function __construct() {

        $this->setLink();

        return;
    }


    public function getInstance() {
        if (! self::$_instance instanceof self) {
            self::$_instance = new self();
        }

        return self::$_instance;
    }

    final public function __clone() {
        return;
    }

    private static function setLink() {

        try {

            $aConfig = \Bootstrap::getConfig();

            self::$_driver = $aConfig['database']['driver'];
            self::$_host = $aConfig['database']['host'];
            self::$_name = $aConfig['database']['name'];
            self::$_user = $aConfig['database']['user'];
            self::$_pass = $aConfig['database']['pass'];

            self::$_link = new \PDO(self::$_driver.':dbname='.self::$_name.';host='.self::$_host,self::$_user ,self::$_pass);


        } catch(Exception $log) {
            throw new DatabaseException($log);
        }

        return;

    }

    /**
     * Execute an SQL query
     * @param   string  $sQuery         SQL query to execute
     * @param   array   $aValues        Binded values
     * @param   string  $sLink          Database link (master or slave)
     * @return  PDOStatement|boolean    Query's result PDO statement
     */
    public static function dbQuery($sQuery, array $aValues = array(), $sLink = 'slave')
    {
        assert('is_string($sQuery)');

//        if ($sLink === 'slave' && self::isMasterQuery($sQuery)) {
//            $sLink = 'master';
//        }

        try {
            if (!isset(self::$_link)) {
                self::setLink();
            }

            $fStart = microtime(true);
            $oStatement = self::$_link->prepare($sQuery, array(\PDO::MYSQL_ATTR_USE_BUFFERED_QUERY => true));
            $oStatement->execute($aValues);

            // Must be after query successful execution
            self::$_sLastLink = $sLink;
            self::$_aBenchmark[$sLink]['time'] += microtime(true) - $fStart;
            self::$_aBenchmark[$sLink]['queries_count']++;
            self::$_aBenchmark[$sLink]['queries_list'][] = $sQuery;

            return $oStatement;
        } catch (Exception $oException) {
            self::$_errors[] = array(
                'query'     => $sQuery,
                'server'    => $sLink,
                'error'     => $oException->getMessage()
            );

            if (defined('ENV') && ENV === 'dev') {
                echo $oException->getMessage();
            }

            return false;
        }
    }


    /**
     * Retrieve last inserted ID
     * @see PDO::lastInsertId
     */
    public static function lastInsertId()
    {
        if (!isset(self::$sLastLink)) {
            return '0';
        }
        return self::${self::$sLastLink}->lastInsertId();
    }

}

class DatabaseException extends \Exception { }

?>
