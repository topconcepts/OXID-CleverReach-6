<?php

namespace TopConcepts\CleverReach\Model;


use OxidEsales\Eshop\Core\DatabaseProvider;

/**
 * Handle CleverReachUser saving
 */
class CleverReachUser extends CleverReachUser_parent
{
    /**
     * Check if parent save() was successful
     * and insert/replace into CleverReachUser
     * @throws \OxidEsales\Eshop\Core\Exception\DatabaseConnectionException
     * @throws \OxidEsales\Eshop\Core\Exception\DatabaseErrorException
     */
    public function save()
    {
        $parent_result = parent::save();

        $shopId = $this->oxuser__oxshopid ?: $this->getShopId();
        
        if ($parent_result) {
            $db = DatabaseProvider::getDb();
            $db->execute(
                '
                    REPLACE INTO
                        tc_cleverreach_user
                    SET
                        userid = ?,
                        shopid = ?,
                        tc_cleverreach_last_edit = now()
            ',
                [$this->oxuser__oxid->value, $shopId]
            );
        }

        return $parent_result;
    }

}
