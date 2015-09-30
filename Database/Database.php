<?php
namespace Library\Core\Database;

/**
 * Manage SGBD with PDO
 *
 * @todo extends Singleton component
 */
class Database
{

    /**
     *
     * @var object Library\Core\Database instance
     */
    private static $_instance;

    /**
     *
     * @var object PDO instance
     */
    protected static $_link;

    /**
     * SGBD driver (mysql|sqllite)
     * @var string
     */
    protected static $_driver = '';

    /**
     * SGBD host
     * @var string
     */
    protected static $_host = '';

    /**
     * database name
     * @var string
     */
    protected static $_name = '';

    /**
     * SGBD user
     * @var string
     */
    protected static $_user = '';

    /**
     * SGBD user's password
     * @var string
     */
    protected static $_pass = '';

    /**
     * SGBD errors
     * @var array
     */
    protected static $_errors = array();

    /**
     * SGBD last link ressource
     * @var ressource
     */
    protected static $_sLastLink = array();

    /**
     * Benchmark SGBD queries
     * @var array
     */
    protected static $_aBenchmark = array(
        'master' => array(
            'time' => 0.0,
            'queries_count' => 0,
            'queries_list' => array()
        ),
        'slave' => array(
            'time' => 0.0,
            'queries_count' => 0,
            'queries_list' => array()
        )
    );

    /**
     * Constructeur
     *
     * @param
     *            void
     * @return void
     * @see PDO::__construct()
     * @access private
     */
    public function __construct()
    {
        $this->setLink();

        return;
    }

    /**
     * Get Database instance
     */
    public function getInstance()
    {
        if (! self::$_instance instanceof self) {
            self::$_instance = new self();
        }

        return self::$_instance;
    }

    /**
     * Instance is a singleton so block cloning it
     */
    final public function __clone()
    {
        return;
    }

    /**
     * Connect to SGBD
     * @throws DatabaseException
     */
    private static function setLink()
    {
        try {

            $aConfig = \Library\Core\Bootstrap::getConfig();

            self::$_driver = $aConfig['database']['driver'];
            self::$_host = $aConfig['database']['host'];
            self::$_name = $aConfig['database']['name'];
            self::$_user = $aConfig['database']['user'];
            self::$_pass = $aConfig['database']['pass'];

            self::$_link = new \PDO(
                self::$_driver . ':dbname=' . self::$_name . ';host=' . self::$_host,
                self::$_user,
                self::$_pass
            );
        } catch (\Exception $log) {
            throw new DatabaseException($log);
        }

        return;
    }

    /**
     * Execute an SQL query
     *
     * @param string $sQuery
     *            SQL query to execute
     * @param array $aValues
     *            Binded values
     * @param string $sLink
     *            Database link (master or slave)
     * @return \PDOStatement boolean result PDO statement
     */
    public static function dbQuery($sQuery, array $aValues = array(), $sLink = 'slave')
    {
        assert('is_string($sQuery)');

        // if ($sLink === 'slave' && self::isMasterQuery($sQuery)) {
        // $sLink = 'master';
        // }

        try {
            if (! isset(self::$_link)) {
                self::setLink();
            }

            $fStart = microtime(true);
            $oStatement = self::$_link->prepare($sQuery, array(
                \PDO::MYSQL_ATTR_USE_BUFFERED_QUERY => true
            ));
            $oStatement->execute($aValues);

            // Must be after query successful execution
            self::$_sLastLink = $sLink;
            self::$_aBenchmark[$sLink]['time'] += microtime(true) - $fStart;
            self::$_aBenchmark[$sLink]['queries_count'] ++;
            self::$_aBenchmark[$sLink]['queries_list'][] = $sQuery;

            return $oStatement;
        } catch (\Exception $oException) {
            self::$_errors[] = array(
                'query' => $sQuery,
                'server' => $sLink,
                'error' => $oException->getMessage()
            );

            if (defined('ENV') && ENV === 'dev') {
                echo $oException->getMessage();
            }

            return false;
        }
    }

    /**
     * Retrieve last inserted ID
     *
     * @see PDO::lastInsertId
     */
    public static function lastInsertId()
    {
        if (isset(self::$_link) === false) {
            return 0;
        }
        return self::$_link->lastInsertId();
    }

}

class DatabaseException extends \Exception
{
}
