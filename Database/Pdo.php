<?php
namespace Library\Core\Database;
use Library\Core\Pattern\Singleton;

/**
 * PDO abstract layer
 *
 * @todo extends Singleton component
 */
class Pdo extends Singleton
{

    /**
     * PDO instance
     * @var \Pdo
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
     */
    protected static $_sLastLink = array();

    /**
     * Instance constructor, connect to database
     * @throws DatabaseException
     */
    protected function __construct()
    {
        $this->setLink();
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
     * Begin transactional mode
     * @return bool
     */
    public static function beginTransaction()
    {
        return self::$_link->beginTransaction();
    }

    /**
     * Close transactional mode to autocommit mode
     * @return mixed
     */
    public function closeTransaction()
    {
        return self::closeTransaction();
    }

    /**
     * Commit a PDO transaction
     * @return bool
     */
    public static function commit()
    {
        return self::$_link->commit();
    }

    /**
     * Rollback current transaction
     * @return bool
     */
    public function rollback()
    {
        return self::$_link->rollBack();
    }

    /**
     * Execute an SQL query
     *
     * @param string $sQuery SQL query to execute
     * @param array $aValues Binded values
     * @return \PDOStatement
     */
    public static function dbQuery($sQuery, array $aValues = array())
    {
        assert('is_string($sQuery)');

        try {
            if (! isset(self::$_link)) {
                self::setLink();
            }

            $oStatement = self::$_link->prepare(
                $sQuery,
                array(
                    \PDO::MYSQL_ATTR_USE_BUFFERED_QUERY => true
                )
            );
            $oStatement->execute($aValues);

            return $oStatement;
        } catch (\Exception $oException) {

            if (defined('ENV') && ENV === 'dev') {
                throw $oException;
            }

            return false;
        }
    }

    /**
     * Retrieve last inserted ID
     * @return int
     */
    public static function lastInsertId()
    {
        if (isset(self::$_link) === false) {
            return 0;
        }
        return (int) self::$_link->lastInsertId();
    }

}

class DatabaseException extends \Exception
{
}
