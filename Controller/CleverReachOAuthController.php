<?php

namespace TopConcepts\CleverReach\Controller;


use OxidEsales\Eshop\Application\Controller\FrontendController;
use OxidEsales\Eshop\Core\DatabaseProvider;
use OxidEsales\Eshop\Core\Field;
use OxidEsales\Eshop\Core\Registry;
use OxidEsales\Eshop\Core\Request;
use TopConcepts\CleverReach\Model\CleverReachProdsearch;

/**
 *
 */
class CleverReachOAuthController extends FrontendController
{
    const CR_OAUTH_CLIENT_ID     = 'fq5nIUguhs';
    const CR_OAUTH_CLIENT_SECRET = '2WDXSTLf8r2fSh7T4doqxDRC1Axh4PK6';

    /**
     * Cleverreach template name
     *
     * @var string
     * @return bool
     */
    protected $_sThisTemplate = 'tc_cleverreach_oauth.tpl';

    public static function tc_isOAuthTokenValid($shopId = null)
    {
        $shopId         = isset($shopId) ? $shopId : Registry::getConfig()->getShopId();
        $token          = Registry::getConfig()->getShopConfVar('tcCleverReachOAuthToken', $shopId);
        $tokenTimestamp = (int)Registry::getConfig()->getShopConfVar('tcCleverReachOAuthTokenTimestamp', $shopId);

        return $token != '' && $tokenTimestamp != '' && (time() + 15 < $tokenTimestamp);
    }

    /**
     * @param null|string|int $shopId
     */
    public static function resetOAuthToken($shopId = null)
    {
        $shopId = isset($shopId) ? $shopId : Registry::getConfig()->getShopId();

        Registry::getConfig()->saveShopConfVar('str', 'tcCleverReachOAuthToken', '', $shopId);
        Registry::getConfig()->saveShopConfVar('str', 'tcCleverReachOAuthTokenTimestamp', '', $shopId);
    }

    /**
     * @return string
     */
    public static function getOAuthRedirectUrl()
    {
        $shopId = Registry::getConfig()->getShopId();
        return Registry::getConfig()->getShopConfVar('sSSLShopURL') . "?cl=CleverReachOAuthController&shp=$shopId";
    }

    /**
     * @return bool|null|string
     * @throws \OxidEsales\Eshop\Core\Exception\DatabaseConnectionException
     */
    public function render()
    {
        $parent = parent::render();

        $code = Registry::get(Request::class)->getRequestEscapedParameter('code');

        if (isset($code) == false) {

            return false;
        }
        $token_url = "https://rest.cleverreach.com/oauth/token.php";

        //prepare post
        $fields["client_id"]     = $this::CR_OAUTH_CLIENT_ID;
        $fields["client_secret"] = $this::CR_OAUTH_CLIENT_SECRET;

        //must be the same as previous redirect uri
        $fields["redirect_uri"] = self::getOAuthRedirectUrl();
        //tell oauth what we want! we want to trade in our auth code for an access token
        $fields["grant_type"] = "authorization_code";
        $fields["code"]       = $_GET["code"];

        //Trade the Authorize token for an access token
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $token_url);
        curl_setopt($curl, CURLOPT_POST, sizeof($fields));
        curl_setopt($curl, CURLOPT_POSTFIELDS, $fields);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        $result = curl_exec($curl);
        curl_close($curl);

        $creds = json_decode($result, true);
        $shopId = Registry::getConfig()->getShopId();

        Registry::getConfig()->saveShopConfVar('str', 'tcCleverReachOAuthToken', $creds['access_token'], $shopId);
        Registry::getConfig()->saveShopConfVar('str', 'tcCleverReachOAuthTokenTimestamp', time() + $creds['expires_in'], $shopId);

        if (self::tc_isOAuthTokenValid()) {
            $this->insertProdsearch();
        }

        return $this->_sThisTemplate;
    }


    /**
     * Insert rows into tc_cleverreach_news table
     *
     * @return void
     * @throws \OxidEsales\Eshop\Core\Exception\DatabaseConnectionException
     */
    protected function insertProdsearch()
    {
        $id         = DatabaseProvider::getDb()->getOne("SELECT oxid FROM tc_cleverreach_prodsearch LIMIT 1");
        $prodSearch = oxNew(CleverReachProdsearch::class);
        $prodSearch->load($id);

        $baseUrl                                     = self::getDefaultProdsearchUrl();
        $shopId                                      = Registry::getConfig()->getShopId();
        $url                                         = $baseUrl . '&name=' . htmlentities($this->getProdSearchName()) . '&shp=' . htmlentities($shopId);
        $prodSearch->tc_cleverreach_prodsearch__url  = new Field($url);
        $prodSearch->tc_cleverreach_prodsearch__name = new Field($this->getProdSearchName());
        $prodSearch->tc_cleverreach_prodsearch__shopid = new Field($shopId);
        $prodSearch->save();
    }

    /**
     * Builds the default url to use
     *
     * @return string
     */
    protected function getDefaultProdsearchUrl()
    {
        return Registry::getConfig()->getShopUrl() . 'index.php?cl=CleverreachProdsearchController';
    }

    /**
     * @return mixed
     */
    public function getProdSearchName()
    {
        return str_replace(' ', '_', Registry::getConfig()->getActiveShop()->oxshops__oxname->value);
    }
}
