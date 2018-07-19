<?php

namespace TopConcepts\CleverReach\Controller\Admin;


use OxidEsales\Eshop\Application\Controller\Admin\AdminDetailsController;
use OxidEsales\Eshop\Core\Registry;

/**
 * Backend administration panel for export
 *
 * @author nkuersten
 */
class CleverReachManualCsv extends AdminDetailsController
{
    /**
     * Name of the template
     * @var string
     */
    protected $_sThisTemplate = 'tc_cleverreach_manual_csv.tpl';

    /**
     * A very simple getter.
     * We use this setter instead of writing data directly into $this->_aViewData
     *
     * @return string
     */
    public function tc_shopBasePath()
    {
        return realpath(getShopBasePath() . '../') . '/';
    }

    /**
     * Returns the complete path for export files
     *
     * @return string
     */
    public function getCompletePath()
    {
        $realpath = realpath(getShopBasePath() . $this->tc_getCsvPath());
        if ($realpath !== false) {
            return $realpath;
        }

        return false;
    }

    /**
     * A very simple getter.
     * We use this setter instead of writing data directly into $this->_aViewData
     *
     * @return object
     */
    public function tc_getCsvPath()
    {
        return Registry::getConfig()->getShopConfVar(CleverReachConfig::SETTING_EXPORT_PATH);
    }

    /**
     * get flag for orders
     *
     * @return string
     */
    public function getOptionToggleOrders()
    {
        return Registry::getConfig()->getShopConfVar(CleverReachConfig::SETTING_ORDER_FLAG);
    }
}
