<?php

namespace TopConcepts\CleverReach\Core;


use OxidEsales\Eshop\Core\Registry;

/**
 * tc_cleverreach_collection_filter
 *
 * This class intends to filter the results from the collection, you can overload this class
 * using the configuration value 'tc_cleverreach_collection_filter' and extending from this
 * class
 *
 */
class CleverReachCollectionFilter extends \FilterIterator
{

    /**
     * Db optin mode for none
     * @var integer
     */
    CONST DBOPTIN_NONE = 0;

    /**
     * Db optin mode for registerd newsletter
     * @var integer
     */
    CONST DBOPTIN_REGISTER = 2;

    /**
     * Db optin mode for confimed newsletter registration by mail
     *
     * @var integer
     */
    CONST DBOPTIN_CONFIRMED = 1;

    /**
     * Opt in modes
     *
     * @var array
     */
    protected $optModes = array(
        self::DBOPTIN_REGISTER,
        self::DBOPTIN_CONFIRMED,
    );

    /**
     * Original Element of the filter
     *
     * @var object
     */
    protected $originalElement;

    /**
     * createTimestamp($date)
     * Helper function to convert mysql date to timestamp
     *
     * @param string $date The date that cames from MySQL
     * @return int
     */
    public function createTimestamp($date)
    {
        if ($date === null) {
            return null;
        }
        $dateObj = new \DateTime($date);

        return $dateObj->getTimeStamp();
    }

    /**
     * current()
     * Function intended to transform the current element in the iterator to the clean CSV version
     *
     * @return array
     * @throws \OxidEsales\Eshop\Core\Exception\DatabaseConnectionException
     */
    public function current()
    {
        $element               = parent::current();
        $this->originalElement = $element;
        $mapped_element        = array();
        $tldMapping            = CleverReachTldMapping::getInstance();

        if ($element->newsid != null) {
            $mapped_element = $this->mapElementsIfNewsId($element);
        } else {
            $mapped_element['active']     = "0";
            $mapped_element['email']      = $element->oxuser__oxusername;
            $mapped_element['activated']  = time();
            $mapped_element['salutation'] = $element->oxuser__oxsal;
            $mapped_element['firstname']  = $element->oxuser__oxfname;
            $mapped_element['lastname']   = $element->oxuser__oxlname;
        }
        $mapped_element['registered'] = strtotime($element->oxuser__oxcreate);
        $mapped_element['company']    = $element->oxuser__oxcompany;
        $mapped_element['street']     = $element->oxuser__oxstreet . ' ' . $element->oxuser__oxstreetnr;
        $mapped_element['city']       = $element->oxuser__oxcity;
        $mapped_element['zip']        = $element->oxuser__oxzip;

        if ($element->oxuser__oxbirthdate != '0000-00-00') {
            $mapped_element['birthday'] = $element->oxuser__oxbirthdate;
        } else {
            $mapped_element['birthday'] = null;
        }

        $mapped_element['shop'] = $element->oxuser__oxshopid;

        if (
            $element->oxuser__oxcountryid !== '' &&
            $element->oxuser__oxcountryid !== null &&
            $element->oxuser__oxcountryid !== 'NULL'
        ) {
            $mapped_element['country']  = $element->oxcountry__oxtitle;
            $mapped_element['language'] = $tldMapping->getLanguageByISO2($element->oxcountry__oxisoalpha2);
        }

        if ($element->oxcountry__oxtitle == '') {
            // Staat und Sprache
            $mapped_element['country']  = $tldMapping->getCountryByEMail($mapped_element['email']);
            $mapped_element['language'] = $tldMapping->getLanguageByEMail($mapped_element['email']);
        }

        $productName =
            trim($element->oxorderarticles__oxtitle . " " . $element->oxorderarticles__oxselvariant);

        $mapped_element['order_id']      = $element->oxorder__oxordernr;
        $mapped_element['purchase_date'] = $this->createTimestamp($element->oxorder__oxorderdate);
        $mapped_element['source']        = $element->oxshops__oxname;
        $mapped_element['amount']        = $element->oxorderarticles__oxamount;
        $mapped_element['product_id']    = $element->oxorderarticles__oxartnum;
        $mapped_element['product']       = $productName;
        $mapped_element['price']         = $element->oxorderarticles__oxprice;

        return $mapped_element;
    }

    /**
     * Maps values from oxnewsubscribe object to array
     *
     * @param object $element
     * @return array
     */
    public function mapElementsIfNewsId($element)
    {
        $mapped_element['active']     = ($this->isActiveElement($element) === true) ? '1' : '0';
        $mapped_element['email']      = $element->oxnewssubscribed__oxemail;
        $mapped_element['activated']  = $this->checkOxDoubleOptIn3($element->oxnewssubscribed__oxdboptin, $element);
        $mapped_element['salutation'] = $element->oxnewssubscribed__oxsal;
        $mapped_element['firstname']  = $element->oxnewssubscribed__oxfname;
        $mapped_element['lastname']   = $element->oxnewssubscribed__oxlname;

        return $mapped_element;
    }

    /**
     * Check the oxid double opt in and
     * returns a value
     *
     * @param int $oxDoubleOptIn
     * @return string 1|0
     */
    protected function checkOxDoubleOptIn1($oxDoubleOptIn)
    {
        return ($oxDoubleOptIn == 1) ? "1" : "0";
    }

    /**
     * Checks double opt in again and return german sounding string
     *
     * @param int $oxDoubleOptIn
     * @param object $element
     * @return string
     */
    protected function checkOxDoubleOptIn2($oxDoubleOptIn, $element)
    {
        return ($oxDoubleOptIn == 1) ? "Ja" : (($element->oxnewssubscribed__oxdboptin == 2) ? 'Unerledigt' : 'Nein');
    }

    /**
     * Creates a timestamp from an oxnewssubscribed element
     * Checks it agains 0
     *
     * @param int $oxDoubleOptIn
     * @param object $element
     * @return int|string '0' | timestamp
     */
    protected function checkOxDoubleOptIn3($oxDoubleOptIn, $element)
    {
        return ($oxDoubleOptIn == 0) ? "0" : $this->createTimestamp($element->oxnewssubscribed__oxsubscribed);
    }

    /**
     * accept()
     * Default CleverReach filter
     *
     * @return boolean
     */
    public function accept()
    {
        return true;
    }

    /**
     * Check wether opt in mode is activated in the modul settigns.
     * Depending on check, we return current element active status.
     *
     * @param $element
     * @return bool
     */
    public function isActiveElement($element)
    {
        return intval($element->oxnewssubscribed__oxdboptin) === self::DBOPTIN_CONFIRMED;
    }
}
