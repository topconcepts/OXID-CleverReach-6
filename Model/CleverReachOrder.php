<?php

namespace TopConcepts\CleverReach\Model;


use OxidEsales\Eshop\Core\DatabaseProvider;
use OxidEsales\Eshop\Core\Registry;

/**
 * Handle CleverReachOrder saving
 *
 * @class CleverReachOrder
 */
class CleverReachOrder extends CleverReachOrder_parent
{
    /**
     * Check if parent save() was successful
     * and insert/replace into tc_cleverreach_order
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
                        tc_cleverreach_order
                    SET
                        orderid = ?,
                        userid  = ?,
                        shopid = ?,
                        tc_cleverreach_send=0
            ',
                [
                    $this->oxorder__oxid->value,
                    $this->oxorder__oxuserid->value,
                    Registry::getConfig()->getShopId(),
                ]
            );

            $oUser = $this->getOrderUser();
            $db->execute('
                    REPLACE INTO
                        tc_cleverreach_user
                    SET
                        userid = ?,
                        shopid = ?,
                        tc_cleverreach_last_edit = now()
            ',
                [$oUser->oxuser__oxid->value, $oUser->oxuser__oxshopid->value]
            );
        }

        return $parent_result;
    }

}
