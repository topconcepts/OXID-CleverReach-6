<?php

namespace TopConcepts\CleverReach\Model;


use OxidEsales\Eshop\Application\Model\NewsSubscribed;
use OxidEsales\Eshop\Core\DatabaseProvider;
use OxidEsales\Eshop\Core\Registry;

/**
 * Handle saving to the tc_cleverreach_news table
 */
class CleverReachNewsSubscribed extends NewsSubscribed
{
    /**
     * Check if parent save() was successfull
     * Insert/replace an entry in tc_cleverreach_news
     *
     * @throws \OxidEsales\Eshop\Core\Exception\DatabaseConnectionException
     * @throws \OxidEsales\Eshop\Core\Exception\DatabaseErrorException
     */
    public function save()
    {
        $parent_result = parent::save();

        if ($parent_result) {
            $db = DatabaseProvider::getDb();
            $db->execute(
                '
                    REPLACE INTO
                        tc_cleverreach_news
                    SET
                        newsid =?,
                        userid =?,
                        tc_cleverreach_last_edit = now(),
                        shopid =?,
                        tc_cleverreach_last_transfer="0000-00-00 00:00:00"
            ',
                [
                    $this->oxnewssubscribed__oxid->value,
                    $this->oxnewssubscribed__oxuserid->value,
                    Registry::getConfig()->getShopId(),
                ]
            );
        }

        return $parent_result;
    }

}


