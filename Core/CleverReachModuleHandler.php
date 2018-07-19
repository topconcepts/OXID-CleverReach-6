<?php

namespace TopConcepts\CleverReach\Core;


use Composer\Script\Event;
use OxidEsales\Eshop\Core\Config;
use OxidEsales\Eshop\Core\DatabaseProvider;
use OxidEsales\Eshop\Core\Registry;
use TopConcepts\CleverReach\Controller\Admin\CleverReachConfig;

/**
 * Class that handles safe module activation
 * and deactivation tasks
 */
class CleverReachModuleHandler
{

    /**
     * Do tasks like insert sql
     * when module is activated
     */
    public static function onActivate()
    {
        self::createSql();
        self::createConfigEntries();
    }

    /**
     * Insert full module sql
     * Adds the four module tables
     * Adds index to oxnewssubscribed
     *
     * @throws \OxidEsales\Eshop\Core\Exception\DatabaseConnectionException
     * @throws \OxidEsales\Eshop\Core\Exception\DatabaseErrorException
     */
    public static function createSql()
    {

        $db               = DatabaseProvider::getDb(DatabaseProvider::FETCH_MODE_ASSOC);
        $createTableOrder = <<<EOF
CREATE TABLE IF NOT EXISTS `tc_cleverreach_order` (
    `orderid` char(32) character set latin1 collate latin1_general_ci NOT NULL,
    `shopid` char(32) character set latin1 collate latin1_general_ci NOT NULL,
    `userid` char(32) character set latin1 collate latin1_general_ci NOT NULL,
    `tc_cleverreach_send` tinyint(1) NOT NULL default '0',
    PRIMARY KEY  (`orderid`),
    KEY `tc_cleverreach_order_idx` (`userid`, `tc_cleverreach_send`),
    KEY `tc_cleverreach_shop_idx` (`shopid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
EOF;

        $createTableUser = <<<EOF
CREATE TABLE IF NOT EXISTS `tc_cleverreach_user` (
  `userid` char(32) character set latin1 collate latin1_general_ci NOT NULL,
  `shopid` char(32) character set latin1 collate latin1_general_ci NOT NULL default '',
  `tc_cleverreach_last_edit` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
  `tc_cleverreach_last_transfer` timestamp NOT NULL default '0000-00-00 00:00:00',
  PRIMARY KEY  (`userid`),
  KEY `tc_cleverreach_user_idx` (`userid`),
  KEY `tc_cleverreach_shopid_idx` (`shopid`),
  KEY `tc_cleverreach_user_idxcombine` (`userid`, `tc_cleverreach_last_edit`, `tc_cleverreach_last_transfer`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
EOF;

        $createTableNews = <<<EOF
CREATE TABLE IF NOT EXISTS `tc_cleverreach_news` (
  `newsid` char(32) character set latin1 collate latin1_general_ci NOT NULL,
  `userid` char(32) character set latin1 collate latin1_general_ci NOT NULL,
  `shopid` char(32) character set latin1 collate latin1_general_ci NOT NULL,
  `tc_cleverreach_last_edit` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
  `tc_cleverreach_last_transfer` timestamp NOT NULL default '0000-00-00 00:00:00',
  PRIMARY KEY `tc_cleverreach_main_idx` (`newsid`),
  KEY `tc_cleverreach_main_both_idx` (`newsid`, `userid`),
  KEY `tc_cleverreach_main_user_idx` (`userid`),
  KEY `tc_cleverreach_shopid_idx` (`shopid`),
  KEY `tc_cleverreach_news_idx` (`newsid`,`tc_cleverreach_last_edit`,`tc_cleverreach_last_transfer`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
EOF;

        $createTableProdsearch = <<<EOF
CREATE TABLE IF NOT EXISTS `tc_cleverreach_prodsearch` (
  `oxid` CHAR(32) character set latin1 collate latin1_general_ci NOT NULL,
  `url` varchar(255) NOT NULL default '',
  `name` varchar(255) NOT NULL default '',
  `password` varchar(128) NOT NULL,
  `salt` varchar(128) NOT NULL,
  `shopid` char(32) character set latin1 collate latin1_general_ci NOT NULL default '',
  `article` TINYINT(1) NOT NULL default 1,
  `category` TINYINT(1) NOT NULL default 0,
  `manufacturer` TINYINT(1) NOT NULL default 0,
  PRIMARY KEY `tc_cleverreach_prodsearch_id_idx` (`oxid`),
  KEY `tc_cleverreach_shopid_idx` (`shopid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
EOF;

        // check if tables exist, for later insertion
        $hasUsers = $db->select("SHOW TABLES LIKE 'tc_cleverreach_user'");
        $hasUsers = (boolean)$hasUsers->count();

        $hasOrders = $db->select("SHOW TABLES LIKE 'tc_cleverreach_order'");
        $hasOrders = (boolean)$hasOrders->count();

        $hasNews = $db->select("SHOW TABLES LIKE 'tc_cleverreach_news'");
        $hasNews = (boolean)$hasNews->count();

        // create tables if not exists
        $db->execute($createTableUser);
        $db->execute($createTableOrder);
        $db->execute($createTableNews);
        $db->execute($createTableProdsearch);

        // do post insertion after table creation
        self::insertUsers();
        self::resetUserTransfer();
        self::insertOrders();
        self::resetOrderTransfer();
        self::insertNews();
        self::resetNewsTransfer();

        // add indexes if they still don't exist
        self::createIndexes();

    }

    /**
     * Insert users into tc_cleverreach_user table
     * @return int
     * @throws \OxidEsales\Eshop\Core\Exception\DatabaseConnectionException
     * @throws \OxidEsales\Eshop\Core\Exception\DatabaseErrorException
     */
    public static function insertUsers()
    {
        $query = "INSERT IGNORE INTO `tc_cleverreach_user` ( SELECT `OXID`, `OXSHOPID`, now(), '0000-00-00 00:00:00' FROM `oxuser` )";

        return DatabaseProvider::getDb()->execute($query);
    }

    /**
     * Reset user tc_cleverreach_last_transfer
     * @return int
     * @throws \OxidEsales\Eshop\Core\Exception\DatabaseConnectionException
     * @throws \OxidEsales\Eshop\Core\Exception\DatabaseErrorException
     */
    public static function resetUserTransfer()
    {
        $shopId = Registry::getConfig()->getShopId();
        $query  = "UPDATE `tc_cleverreach_user` SET `tc_cleverreach_last_transfer`='0000-00-00 00:00:00' WHERE `shopid`=?";

        return DatabaseProvider::getDb()->execute($query, [$shopId]);
    }

    /**
     * Insert orders into tc_cleverreach_order table
     * @return int
     * @throws \OxidEsales\Eshop\Core\Exception\DatabaseConnectionException
     * @throws \OxidEsales\Eshop\Core\Exception\DatabaseErrorException
     */
    public static function insertOrders()
    {
        $query = "INSERT IGNORE INTO `tc_cleverreach_order` ( SELECT `OXID`, `OXSHOPID`, `OXUSERID`, 0 FROM `oxorder`)";

        return DatabaseProvider::getDb()->execute($query);
    }

    /**
     * Reset user tc_cleverreach_last_transfer
     * @return int
     * @throws \OxidEsales\Eshop\Core\Exception\DatabaseConnectionException
     * @throws \OxidEsales\Eshop\Core\Exception\DatabaseErrorException
     */
    public static function resetOrderTransfer()
    {
        $shopId = Registry::getConfig()->getShopId();
        $query  = "UPDATE `tc_cleverreach_order` SET `tc_cleverreach_send`=? WHERE `shopid`=?";

        return DatabaseProvider::getDb()->execute($query, [0, $shopId]);
    }

    /**
     * Insert rows into tc_cleverreach_news table
     *
     * @return int
     * @throws \OxidEsales\Eshop\Core\Exception\DatabaseConnectionException
     * @throws \OxidEsales\Eshop\Core\Exception\DatabaseErrorException
     */
    public static function insertNews()
    {
        $query = "
          INSERT IGNORE INTO
            `tc_cleverreach_news`
            (
                SELECT DISTINCT
                    oxnewssubscribed.oxid, oxuserid, oxuser.oxshopid, now(), '0000-00-00 00:00:00'
                FROM
                    `oxnewssubscribed`, oxuser
                WHERE
                    oxuser.oxid = oxnewssubscribed.oxuserid
            )";

        return DatabaseProvider::getDb()->execute($query);
    }

    /**
     * Reset user tc_cleverreach_last_transfer
     * @return int
     * @throws \OxidEsales\Eshop\Core\Exception\DatabaseConnectionException
     * @throws \OxidEsales\Eshop\Core\Exception\DatabaseErrorException
     */
    public static function resetNewsTransfer()
    {
        $shopId = Registry::getConfig()->getShopId();
        $query  = "UPDATE `tc_cleverreach_news` SET `tc_cleverreach_last_transfer`='0000-00-00 00:00:00' WHERE `shopid`=?";

        return DatabaseProvider::getDb()->execute($query, [$shopId]);
    }

    /**
     * Create database indexes if they don't exist
     * @throws \OxidEsales\Eshop\Core\Exception\DatabaseConnectionException
     */
    public static function createIndexes()
    {
        $db = DatabaseProvider::getDb();
        try {
            $result = $db->select("SHOW CREATE TABLE oxnewssubscribed");
        } catch (\Exception $e) {
            return;
        }

        try {
            $db->execute("CREATE INDEX oxnewssubscribed_main_idx ON oxnewssubscribed (oxid);");
        } catch (\Exception $e) {
        }
        try {
            $db->execute("CREATE INDEX oxnewssubscribed_shopid_idx ON oxnewssubscribed (oxshopid);");
        } catch (\Exception $e) {
        }
    }

    /**
     * Create oxconfig entries for the module
     */
    public static function createConfigEntries()
    {

        $config = Registry::getConfig();

        // try to create a default path
        $exportPath = $config->getShopConfVar(CleverReachConfig::SETTING_EXPORT_PATH);

        if (isset($exportPath) === false || $exportPath === '') {

            $exportPath = 'tc_export/';
            if (file_exists(getShopBasePath() . $exportPath) === false) {
                $success = @mkdir(getShopBasePath() . $exportPath);
            }
            if ($success === false || defined('TC_CLEVERREACH_UNIT_TEST')) {
                $error = Registry::getLang()->translateString("TC_CLEVERREACH_ERROR_NO_PATH");
                $error = sprintf($error, getShopBasePath() . $exportPath);
                Registry::get("oxUtilsView")->addErrorToDisplay($error);
            }
        }

        $variables = array(
            array('type' => 'str', 'name' => CleverReachConfig::SETTING_EXPORT_PATH, 'value' => $exportPath),
            array('type' => 'bool', 'name' => CleverReachConfig::SETTING_ORDER_FLAG, 'value' => false),
        );

        foreach ($variables as $var) {
            $existing = $config->getShopConfVar($var['name']);

            if (isset($existing) === false || $existing === '') {
                $config->saveShopConfVar($var['type'], $var['name'], $var['value']);
            }
        }
    }
}
