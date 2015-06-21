<?php
namespace Library\Core\Database\Drivers;
use Library\Core\Database\DatabaseAbstract;

/**
 * Mysql Database driver
 *
 */
class Mysql extends DatabaseAbstract
{

    /**
     * SGBD driver (mysql|sqllite)
     * @var string
     */
    protected static $_driver = 'mysql';

}

class MysqlDriverException extends \Exception
{
}
