<?php

namespace TopConcepts\CleverReach\Controller\Admin;


use OxidEsales\Eshop\Application\Controller\Admin\AdminDetailsController;
use OxidEsales\Eshop\Core\Registry;
use OxidEsales\Eshop\Core\Request;
use TopConcepts\CleverReach\Controller\CleverReachOAuthController;
use TopConcepts\CleverReach\Core\CleverReachRestApi;

/**
 * Administration View for cleverreach options
 *
 * @author nkuersten
 */
class CleverReachConfig extends AdminDetailsController
{
    /**
     * Constant string for export path
     *
     * @var string
     */
    const SETTING_EXPORT_PATH = 'tc_cleverreach_exportpath';

    /**
     * Constant string for order options
     *
     * @var string
     */
    const SETTING_ORDER_FLAG = 'tc_cleverreach_with_orders';

    /**
     * Name of the template file
     * @var string
     */
    protected $_sThisTemplate = 'tc_cleverreach_config.tpl';


    /**
     * @return string
     */
    public function render()
    {
        $parent = parent::render();

        if ($this->tc_isOAuthTokenValid()) {
            $this->checkIfCurrentListValid();
        }

        return $parent;
    }

    /**
     *
     * @throws \Exception
     */
    public function createList()
    {
        $config     = Registry::getConfig();
        $restClient = oxNew(CleverReachRestApi::class);
        $result     = $restClient->createList(Registry::get(Request::class)->getRequestEscapedParameter('list-name'));
        $config->saveShopConfVar('str', 'tcCleverReachListId', $result->id, $config->getShopId());
    }

    /**
     * A very simple getter.
     * We use this getter instead of writing data directly into $this->_aViewData
     *
     * @return object
     */
    public function getAccountId()
    {
        return Registry::getConfig()->getShopConfVar('tc_cleverreach_account_id');
    }

    /**
     * A very simple getter.
     * We use this getter instead of writing data directly into $this->_aViewData
     *
     * @return object
     */
    public function getLogin()
    {
        return Registry::getConfig()->getShopConfVar('tc_cleverreach_login');
    }

    /**
     * A very simple getter.
     * We use this getter instead of writing data directly into $this->_aViewData
     *
     * @return object
     */
    public function getPassword()
    {
        return Registry::getConfig()->getShopConfVar('tc_cleverreach_password');
    }

    /**
     * A very simple getter.
     * We use this getter instead of writing data directly into $this->_aViewData
     *
     * @return string
     */

    public function getOAuthClientId()
    {
        $oAuth = Registry::get(CleverReachOAuthController::class);

        return $oAuth::CR_OAUTH_CLIENT_ID;
    }


    /**
     * A very simple getter.
     * We use this getter instead of writing data directly into $this->_aViewData
     *
     * @return string
     */
    public function getOAuthClientSecret()
    {
        $oAuth = Registry::get(CleverReachOAuthController::class);

        return $oAuth::CR_OAUTH_CLIENT_SECRET;
    }

    /**
     * A very simple getter.
     * We use this getter instead of writing data directly into $this->_aViewData
     *
     * @return object
     */
    public function getListId($shopId = null)
    {
        $shopId = $shopId !== null ? $shopId : Registry::getConfig()->getShopId();

        return Registry::getConfig()->getShopConfVar('tcCleverReachListId', $shopId);
    }

    /**
     * Check if the OAuth token is saved compare it's timestamp with current time to see if it's still valid
     * @param null|int|string $shopId
     * @return bool
     */
    public function tc_isOAuthTokenValid($shopId = null)
    {
        return CleverReachOAuthController::tc_isOAuthTokenValid($shopId);
    }

    /**
     * @return string
     */
    public function tc_getOAuthUrl()
    {
        $clientid = $this->getOAuthClientId();
        $rdu      = urlencode(CleverReachOAuthController::getOAuthRedirectUrl());

        return 'https://rest.cleverreach.com/oauth/authorize.php?client_id=' . $clientid . '&grant=basic&response_type=code&redirect_uri=' . $rdu;
    }

    /**
     * @param null|string|int $shopId
     */
    public function resetOAuthToken($shopId = null)
    {
        CleverReachOAuthController::resetOAuthToken($shopId);
    }

    /**
     * get flag for orders
     *
     * @return string
     */
    public function getOptionToggleOrders()
    {
        return Registry::getConfig()->getShopConfVar(self::SETTING_ORDER_FLAG);
    }

    /**
     * Sets a view value to view object
     *
     * @return null
     */
    public function tc_getLastTransfer()
    {
        return Registry::getConfig()->getShopConfVar('tc_cleverreach_last_transfer');
    }

    /**
     * A very simple getter.
     * We use this setter instead of writing data directly into $this->_aViewData.
     *
     * @return string
     */
    public function tc_getShopBasePath()
    {
        return realpath(getShopBasePath() . '../') . '/';
    }

    /**
     * @throws \Exception
     * @return bool|mixed
     */
    public function getLists()
    {
        $restClient = oxNew(CleverReachRestApi::class);
        return $restClient->getAllLists();
    }

    /**
     *
     */
    protected function checkIfCurrentListValid()
    {
        $restClient = oxNew(CleverReachRestApi::class);
        $result = $restClient->getList($this->getListId());

        if (!$result) {
            Registry::getConfig()->saveShopConfVar('str', 'tcCleverReachListId', null);
        }
    }

    public function select_list()
    {
        Registry::getConfig()->saveShopConfVar('str', 'tcCleverReachListId', Registry::get(Request::class)->getRequestEscapedParameter('selectlist'));
    }

    /**
     * @throws \Exception
     */
    public function getListNameById()
    {
        $restClient = oxNew(CleverReachRestApi::class);
        $result = $restClient->getListById($this->getListId());

        return $result->name;
    }

    public function disconnectList()
    {
        Registry::getConfig()->saveShopConfVar('str', 'tcCleverReachListId', null);
    }
}
