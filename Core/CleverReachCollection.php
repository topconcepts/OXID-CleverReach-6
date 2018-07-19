<?php

namespace TopConcepts\CleverReach\Core;


use OxidEsales\Eshop\Core\DatabaseProvider;
use OxidEsales\Eshop\Core\Registry;
use TopConcepts\CleverReach\Controller\Admin\CleverReachConfig;

/**
 * CleverReachCollection
 *
 * Class that extends from Iterator and perform the queries to the database
 * @class CleverReachCollection
 */
class CleverReachCollection extends \ArrayIterator
{

    /**
     * A class constant that points to the table in mysql DB
     * @var string
     */
    const LAST_TRANSFER = 'tc_cleverreach_last_transfer';

    /**
     * A class constant that points to the table in mysql DB
     * @var string
     */
    const LAST_EDIT = 'tc_cleverreach_last_edit';

    /**
     * A class constant that points to the table in mysql DB
     * @var string
     */
    const ORDER_SEND = 'tc_cleverreach_send';

    /**
     * Id of the shop
     *
     * @var string|integer
     */
    protected $shopId;

    /**
     * Total rows (uses lazy read)
     *
     * @var integer
     */
    protected $totalRows = null;

    /**
     * Stores the results of the query groupping by user
     *
     * @var array
     */
    protected $byUser;

    /**
     * Timestamp pointing to now
     *
     * @var string
     */
    public $now;

    /**
     * Elements pending to transfer
     *
     * @var array
     */
    public $toTransfer = array(array(), array());

    protected $fullList = false;

    /**
     * getFilteredIterator()
     * Get filtered iterator to iterate between the results
     *
     * @return CleverReachCollectionFilter
     */
    public function getFilteredIterator()
    {
        return oxNew(
            $this->getIteratorClassName(),
            $this
        );
    }

    /**
     * getUserFilteredIterator()
     * Get filtered iterator (by User) to iterate between the results
     *
     * @return array
     */
    public function getUserFilteredIterator()
    {
        $return = array();
        foreach ($this->byUser as $iterator) {
            $return[] = oxNew(
                $this->getIteratorClassName(),
                $iterator
            );
        }

        return ($return);
    }

    /**
     * setTransfer()
     * Execute the pending updates on the DB to set the transfers for the moved records
     * @throws \OxidEsales\Eshop\Core\Exception\DatabaseConnectionException
     * @throws \OxidEsales\Eshop\Core\Exception\DatabaseErrorException
     */
    public function setTransfer()
    {
        $db = DatabaseProvider::getDb(DatabaseProvider::FETCH_MODE_ASSOC);

        $oxidArray = array();
        foreach ($this->toTransfer[0] as $oxids) {
            $oxidArray[] = $oxids;
        }

        if (count($oxidArray) > 0) {

            $sql = '
                UPDATE
                    tc_cleverreach_user user
                SET
                    user.' . self::LAST_TRANSFER . ' = FROM_UNIXTIME(' . $this->now . '+1) ,
                    user.' . self::LAST_EDIT . ' = FROM_UNIXTIME(' . $this->now . ')
                WHERE
                    user.shopid = ' . $this->shopId . '
                AND
                    user.userid IN ("%s")';

            $db->execute(sprintf($sql, implode('", "', $oxidArray)));

            $sql = '
                UPDATE
                    tc_cleverreach_news news
                SET
                    news.' . self::LAST_TRANSFER . ' = FROM_UNIXTIME(' . $this->now . '+1) ,
                    news.' . self::LAST_EDIT . ' = FROM_UNIXTIME(' . $this->now . ')
                WHERE
                    news.shopid = ' . $this->shopId . '
                AND
                    news.userid IN ("%s")';

            $db->execute(sprintf($sql, implode('", "', $oxidArray)));
        }

        $ordersArray = array();
        foreach ($this->toTransfer[1] as $order) {
            $ordersArray[] = $order;
        }

        if (count($ordersArray) > 0) {
            $sql = '
                UPDATE
                    tc_cleverreach_order `order`
                SET
                    `order`.tc_cleverreach_send = 1
                WHERE
                    `order`.orderid IN ("%s")';

            $db->execute(sprintf($sql, implode('", "', $ordersArray)));
        }

        $this->toTransfer = [[], []];
    }

    /**
     * addTransferred($userId)
     * Mark the specific $document as exported
     *
     * @param $key
     */
    public function addTransferred($key)
    {
        if (!in_array($this[$key]->oxuser__oxid, $this->toTransfer[0])) {
            $this->toTransfer[0][] = $this[$key]->oxuser__oxid;
        }

        if ($this[$key]->oxorder__oxid !== null && !in_array($this[$key]->oxorder__oxid, $this->toTransfer[1])) {
            $this->toTransfer[1][] = $this[$key]->oxorder__oxid;
        }
    }

    /**
     * getDataQuery($getDoubleOptIn, $limit)
     * Get the query to get the data.
     *
     * @param mixed $limit Can be false or an integer (false means no limit)
     *
     * @param int $offset
     * @return string
     */
    public function getDataQuery($limit, $offset)
    {
        $addOrders = Registry::getConfig()->getShopConfVar(CleverReachConfig::SETTING_ORDER_FLAG, $this->shopId);

        $sql = '
                SELECT SQL_CALC_FOUND_ROWS
                    user.userid AS user__userid,
                    news.newsid AS newsid,
                    oxuser.oxid as oxuser__oxid,
                    oxuser.oxusername as oxuser__oxusername,
                    oxuser.oxsal as oxuser__oxsal,
                    oxuser.oxfname as oxuser__oxfname,
                    oxuser.oxlname as oxuser__oxlname,
                    oxuser.oxcreate as oxuser__oxcreate,
                    oxuser.oxcompany as oxuser__oxcompany,
                    oxuser.oxstreet as oxuser__oxstreet,
                    oxuser.oxstreetnr as oxuser__oxstreetnr,
                    oxuser.oxcity as oxuser__oxcity,
                    oxuser.oxzip as oxuser__oxzip,
                    oxuser.oxbirthdate as oxuser__oxbirthdate,
                    oxuser.oxshopid as oxuser__oxshopid,
                    oxuser.oxcountryid as oxuser__oxcountryid,

                    oxnewssubscribed.oxid as oxnewssubscribed__oxid,
                    oxnewssubscribed.oxdboptin as oxnewssubscribed__oxdboptin,
                    oxnewssubscribed.oxsal as oxnewssubscribed__oxsal,
                    oxnewssubscribed.oxfname as oxnewssubscribed__oxfname,
                    oxnewssubscribed.oxlname as oxnewssubscribed__oxlname,
                    oxnewssubscribed.oxsubscribed as oxnewssubscribed__oxsubscribed,
                    oxnewssubscribed.oxemail as oxnewssubscribed__oxemail,

                    oxcountry.oxid as oxcountry__oxid,
                    oxcountry.oxtitle as oxcountry__oxtitle,
                    oxcountry.oxisoalpha2 as oxcountry__oxisoalpha2,';
        if ($addOrders) {
            $sql .= '
                    crorder.orderid AS orderid,
                    oxorder.oxid as oxorder__oxid,
                    oxorder.oxorderdate as oxorder__oxorderdate,
                    oxorder.oxordernr as oxorder__oxordernr,
                  
                    oxorderarticles.oxid as oxorderarticles__oxid,
                    oxorderarticles.oxtitle as oxorderarticles__oxtitle,
                    oxorderarticles.oxamount as oxorderarticles__oxamount,
                    oxorderarticles.oxartnum as oxorderarticles__oxartnum,
                    oxorderarticles.oxprice as oxorderarticles__oxprice,';
        }
        $sql .= '
                    oxshops.oxid as oxshops__oxid,
                    oxshops.oxname as oxshops__oxname
                FROM
                    `tc_cleverreach_news` news
                JOIN
                    `oxnewssubscribed` ON news.newsid = `oxnewssubscribed`.oxid
                JOIN
                    `tc_cleverreach_user` user ON news.userid = user.userid
                JOIN
                        `oxuser` ON `oxuser`.oxid = user.userid';
        if ($addOrders) {
            $sql .= '
                LEFT JOIN
                    `tc_cleverreach_order` crorder
                    LEFT JOIN
                        `oxorder`
                        LEFT JOIN
                            `oxorderarticles`
                        ON `oxorder`.oxid = `oxorderarticles`.oxorderid
                    ON `oxorder`.oxid = crorder.orderid
                ON news.userid = crorder.userid';
            if (!$this->fullList) {
                $sql .= ' AND crorder.tc_cleverreach_send=0 ';
            }
        }
        $sql .= '
                 LEFT JOIN
                     `oxcountry` ON `oxcountry`.oxid = `oxuser`.oxcountryid
                 JOIN
                     `oxshops` ON `oxshops`.oxid = `oxuser`.`oxshopid`
                 WHERE
                     user.shopid = ' . $this->shopId . '
                 AND
                     news.shopid = ' . $this->shopId . '
                 AND (
                       `oxnewssubscribed`.`oxdboptin` = 1
                        OR
                       `oxnewssubscribed`.`oxunsubscribed` != "0000-00-00 00:00:00"
                     )';
        if (!$this->fullList) {
            if ($addOrders) {
                $sql .= '
                AND (
                  crorder.tc_cleverreach_send=0
                OR ((
                      news.' . self::LAST_EDIT . ' >= news.' . self::LAST_TRANSFER . '
                      OR
                      user.' . self::LAST_EDIT . ' >= user.' . self::LAST_TRANSFER . '
                    )
                AND crorder.orderid IS NULL))';
            } else {
                $sql .= '
                AND (
                      news.' . self::LAST_EDIT . ' >= news.' . self::LAST_TRANSFER . '
                      OR
                      user.' . self::LAST_EDIT . ' >= user.' . self::LAST_TRANSFER . '
                    )';
            }
        }

        $sql .= '
        AND user.userid not like "%dummy%" ORDER BY user.userid
        LIMIT ' . $limit . ' OFFSET ' . $offset;

        return $sql;
    }

    /**
     * getIteratorClassName()
     * Get the classname of the iterator
     *
     * @return string
     */
    public function getIteratorClassName()
    {
        $filter = CleverReachCollectionFilter::class;
        if (Registry::getConfig()->getConfigParam('tc_cleverreach_collection_filter')) {
            $filter = Registry::getConfig()->getConfigParam('tc_cleverreach_collection_filter');
        }

        return $filter;
    }

    /**
     * getTotalRows()
     * Return the total rows (stored in $totalRows property) if null make the query to the DB.
     *
     * @return string
     */
    public function getTotalRows()
    {
        return $this->totalRows;
    }

    /**
     * addElement(stdClass $element)
     * Add element to he object, including byUser property
     * @param \stdClass $element
     */
    public function addElement(\stdClass $element)
    {
        $this->append($element);
        if (!array_key_exists($element->user__userid, $this->byUser)) {
            $this->byUser[$element->user__userid]      = oxNew(__CLASS__, $this->shopId);
            $this->byUser[$element->user__userid]->now = $this->now;
        }
        $this->byUser[$element->user__userid]->append($element);
    }

    /**
     * factory($shopId, $limit)
     * Factory that returns the object (use this instead of the constructor)
     * ! Legacy stuff, but kept.
     *
     * @param integer $shopId Shop ID
     *
     * @return CleverReachCollection
     */
    static function factory($shopId)
    {
        $obj = oxNew(__CLASS__, $shopId);

        return ($obj);
    }

    /**
     * Fetches a datasets for the given limit/offset
     * Procomputes the limit
     *
     * @param bool $limit
     * @param int $offset
     *
     * @return CleverReachCollection
     * @throws \OxidEsales\Eshop\Core\Exception\DatabaseConnectionException
     * @throws \OxidEsales\Eshop\Core\Exception\DatabaseErrorException
     */
    public function fetch($limit, $offset)
    {
        $sql             = $this->getDataQuery($limit, $offset);
        $resultSet       = DatabaseProvider::getDb(DatabaseProvider::FETCH_MODE_ASSOC)->select($sql);
        $this->totalRows = (int)DatabaseProvider::getDb()->getOne("select found_rows() as num;");

        if ($resultSet != false && $resultSet->count() > 0) {
            while (!$resultSet->EOF) {
                $row = $resultSet->getFields();
                $this->addElement((object)$row);
                $resultSet->fetchRow();
            }
        }

        return $this;
    }

    /**
     * Setter for whole list
     *
     * @param $boolean
     */
    public function withFullList($boolean)
    {
        $this->fullList = $boolean;
    }

    /**
     * Constructor of the collection
     * This function have all the queries to the db to get the data
     */
    function __construct($shopId)
    {
        $this->shopId = $shopId;
        $this->now    = time();
        $this->byUser = array();
        parent::__construct(array());
    }
}
