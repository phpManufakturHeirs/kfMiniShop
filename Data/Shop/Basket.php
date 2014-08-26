<?php

/**
 * miniShop
 *
 * @author Team phpManufaktur <team@phpmanufaktur.de>
 * @link https://kit2.phpmanufaktur.de/miniShop
 * @copyright 2014 Ralf Hertsch <ralf.hertsch@phpmanufaktur.de>
 * @license MIT License (MIT) http://www.opensource.org/licenses/MIT
 */

namespace phpManufaktur\miniShop\Data\Shop;

use Silex\Application;
use Carbon\Carbon;

class Basket
{
    protected $app = null;
    protected static $table_name = null;

    /**
     * Constructor
     *
     * @param Application $app
     */
    public function __construct(Application $app)
    {
        $this->app = $app;
        self::$table_name = FRAMEWORK_TABLE_PREFIX.'minishop_basket';
    }

    /**
     * Create the table
     *
     * @throws \Exception
     */
    public function createTable()
    {
        $table = self::$table_name;
        $SQL = <<<EOD
    CREATE TABLE IF NOT EXISTS `$table` (
        `id` INT(11) NOT NULL AUTO_INCREMENT,
        `identifier` VARCHAR(64) NOT NULL DEFAULT '',
        `data` TEXT NOT NULL,
        `timestamp` TIMESTAMP,
        PRIMARY KEY (`id`),
        UNIQUE (`identifier`)
        )
    COMMENT='The basket table for the miniShop'
    ENGINE=InnoDB
    AUTO_INCREMENT=1
    DEFAULT CHARSET=utf8
    COLLATE='utf8_general_ci'
EOD;
        try {
            $this->app['db']->query($SQL);
            $this->app['monolog']->addInfo('Created table '.$table, array(__METHOD__, __LINE__));
        } catch (\Doctrine\DBAL\DBALException $e) {
            throw new \Exception($e);
        }
    }

    /**
     * Drop the table
     */
    public function dropTable()
    {
        $this->app['db.utils']->dropTable(self::$table_name);
    }

    public function selectBasket($identifier)
    {
        try {
            $SQL = "SELECT `data` FROM `".self::$table_name."` WHERE `identifier`='$identifier'";
            $data = $this->app['db']->fetchColumn($SQL);
            return (!empty($data)) ? unserialize($data) : array();
        } catch (\Doctrine\DBAL\DBALException $e) {
            throw new \Exception($e);
        }
    }

    public function insertBasket($identifier, $data)
    {
        try {
            $insert = array(
                'data' => serialize($data),
                'identifier' => $identifier
            );
            $this->app['db']->insert(self::$table_name, $insert);
            return $this->app['db']->lastInsertId();
        } catch (\Doctrine\DBAL\DBALException $e) {
            throw new \Exception($e);
        }
    }

    public function updateBasket($identifier, $data)
    {
        try {
            $update = array(
                'data' => serialize($data)
            );
            $this->app['db']->update(self::$table_name, $update, array('identifier' => $identifier));
        } catch (\Doctrine\DBAL\DBALException $e) {
            throw new \Exception($e);
        }
    }

    public function existsIdentifier($identifier)
    {
        try {
            $SQL = "SELECT `identifier` FROM `".self::$table_name."` WHERE `identifier`='$identifier'";
            $result = $this->app['db']->fetchColumn($SQL);
            return ($result === $identifier);
        } catch (\Doctrine\DBAL\DBALException $e) {
            throw new \Exception($e);
        }
    }

    public function removeBasket($identifier)
    {
        try {
            $this->app['db']->delete(self::$table_name, array('identifier' => $identifier));
        } catch (\Doctrine\DBAL\DBALException $e) {
            throw new \Exception($e);
        }
    }

    public function cleanup()
    {
        try {
            $dt = new Carbon();
            $dt->subHours(6);
            $ts = $dt->format('Y-m-d H:i:s');
            $SQL = "DELETE FROM `".self::$table_name."` WHERE `timestamp` < '$ts'";
            $this->app['db']->query($SQL);
        } catch (\Doctrine\DBAL\DBALException $e) {
            throw new \Exception($e);
        }
    }
}
