<?php

namespace TopConcepts\CleverReach\Core;


use OxidEsales\Eshop\Core\Registry;
use TopConcepts\CleverReach\Controller\Admin\CleverReachConfig;
use TopConcepts\CleverReach\Controller\CleverReachOAuthController;
use TopConcepts\CleverReach\Core\Exception\CleverReachException;
use TopConcepts\CleverReach\Core\Exception\GroupNotFoundException;

/**
 * Class CleverReachRestApi
 * @package TopConcepts\CleverReach\Core
 */
class CleverReachRestApi
{
    private $attributes;

    public $data = false;
    public $url  = "https://rest.cleverreach.com/v2";

    public $postFormat   = "json";
    public $returnFormat = "json";

    public $authMode         = false;
    public $authModeSettings = false;

    public $debugValues = false;

    public $checkHeader     = true;
    public $throwExceptions = true;
    public $header          = false;
    public $error           = false;


    /**
     * Internal cleverreach list id
     *
     * @var string
     */
    public $listId;

    /**
     * tc_cleverreach_rest_api constructor.
     * @param null $shopId
     */
    public function __construct($shopId = null)
    {
        $shopId                 = isset($shopId) ? $shopId : Registry::getConfig()->getShopId();
        $this->authModeSettings = new \stdClass;
        $this->debugValues      = new \stdClass;
        $this->listId           = Registry::getConfig()->getShopConfVar('tcCleverReachListId', $shopId);

        if ($this->tc_isOAuthTokenValid($shopId)) {
            $token = Registry::getConfig()->getShopConfVar('tcCleverReachOAuthToken', $shopId);
            $this->setAuthMode("bearer", $token);
        } else {
            $shopmsg = "";
            if($shopId){
                $shopmsg =  'ShopId: '.$shopId."\n";
            }
            echo($shopmsg.'Nicht authentifiziert oder oAuth Token ungültig.');
        }
    }

    /**
     * sets AuthMode (jwt, webauth, etc)
     *
     * @param string    jwt, webauth,none
     * @param mixed
     */
    public function setAuthMode($mode, $value)
    {
        $this->authMode                = $mode;
        $this->authModeSettings->token = $value;
    }

    /**
     * makes a GET call
     *
     * @param  array
     * @param bool $data
     * @param string $mode
     * @return mixed
     * @throws \Exception
     */
    public function get($path, $data = false, $mode = "get")
    {
        $this->resetDebug();
        if (is_string($data)) {
            if (!$data = json_decode($data)) {
                throw new CleverReachException("data is string but no JSON");
            }
        }

        $url = sprintf("%s?%s", $this->url . $path, ($data ? http_build_query($data) : ""));
        $this->debug("url", $url);

        $curl = curl_init($url);
        $this->setupCurl($curl);

        switch ($mode) {
            case 'delete':
                curl_setopt($curl, CURLOPT_CUSTOMREQUEST, strtoupper($mode));
                $this->debug("mode", strtoupper($mode));
                break;

            default:
                $this->debug("mode", "GET");
                break;
        }

        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        $curl_response = curl_exec($curl);

        $headers = curl_getinfo($curl);
        curl_close($curl);

        $this->debugEndTimer();

        return $this->returnResult($curl_response, $headers);
    }

    /**
     * makes a DELETE call
     *
     * @param  array
     *
     * @param bool $data
     * @return mixed
     * @throws \Exception
     */
    public function delete($path, $data = false)
    {
        return $this->get($path, $data, "delete");
    }

    /**
     * makes a put call
     *
     * @param  array
     *
     * @return mixed
     * @throws \Exception
     */
    public function put($path, $data = false)
    {
        return $this->post($path, $data, "put");
    }

    /**
     * does POST
     *
     * @param $path
     * @param $data
     * @param string $mode
     * @return mixed [type]
     * @throws \Exception
     */
    public function post($path, $data, $mode = "post")
    {
        $this->resetDebug();
        $this->debug("url", $this->url . $path);
        if (is_string($data)) {
            if (!$data = json_decode($data)) {
                throw new \Exception("data is string but no JSON");
            }
        }
        $curl_post_data = $data;

        $curl = curl_init($this->url . $path);
        $this->setupCurl($curl);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

        switch ($mode) {
            case 'put':
                curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "PUT");
                break;

            default:
                curl_setopt($curl, CURLOPT_POST, true);
                break;
        }

        $this->debug("mode", strtoupper($mode));

        if ($this->postFormat == "json") {
            $curl_post_data = json_encode($curl_post_data);
        }

        curl_setopt($curl, CURLOPT_POSTFIELDS, $curl_post_data);
        $curl_response = curl_exec($curl);
        $headers       = curl_getinfo($curl);
        curl_close($curl);

        $this->debugEndTimer();

        return $this->returnResult($curl_response, $headers);
    }


    /**
     * [resetDebug description]
     * @return void [type]
     */
    private function resetDebug()
    {
        $this->debugValues = new \stdClass;
        $this->error       = false;
        $this->debugStartTimer();
    }

    /**
     * set debug keys
     *
     * @param $key
     * @param $value
     * @return void [type]
     */
    private function debug($key, $value)
    {
        $this->debugValues->$key = $value;
    }

    /**
     *
     */
    private function debugStartTimer()
    {
        $this->debugValues->time = $this->microtime_float();
    }

    /**
     *
     */
    private function debugEndTimer()
    {
        $this->debugValues->time = $this->microtime_float() - $this->debugValues->time;
    }

    /**
     * prepapres curl with settings amd ein object
     *
     * @param  pointer_curl
     */
    private function setupCurl(&$curl)
    {
        $header = array();

        switch ($this->postFormat) {
            case 'json':
                $header['content'] = 'Content-Type: application/json';
                break;

            default:
                $header['content'] = 'Content-Type: application/x-www-form-urlencoded; charset=UTF-8';
                break;
        }
        $header['token'] = 'Authorization: Bearer ' . $this->authModeSettings->token;

        $this->debugValues->header = $header;
        curl_setopt($curl, CURLOPT_HTTPHEADER, $header);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($curl, CURLOPT_SSLVERSION, 4);
    }

    /**
     * returls formated based on given obj settings
     *
     * @param      string
     *
     * @param bool $header
     *
     * @return mixed
     * @throws \Exception
     */
    private function returnResult($in, $header = false)
    {
        $this->header = $header;

        if ($this->checkHeader && isset($header["http_code"])) {
            if ($header["http_code"] < 200 || $header["http_code"] >= 300) {
                //error!?
                $this->error = $in;
                $message     = var_export($in, true);
                if ($tmp = json_decode($in)) {
                    if (isset($tmp->error->message)) {
                        $message = $tmp->error->message;
                    }
                }
                if ($this->throwExceptions) {
                    if (strstr($message, 'invalid group')) {
                        throw new GroupNotFoundException('Group not found.');
                    } else {
                        throw new CleverReachException('' . $header["http_code"] . ';' . $message);
                    }
                }
                $in = null;
            }
        }

        switch ($this->returnFormat) {
            case 'json':
                return json_decode($in);
                break;

            default:
                return $in;
                break;
        }

        return $in;
    }

    /**
     * @return float
     */
    public function microtime_float()
    {
        list($usec, $sec) = explode(" ", microtime());

        return ((float)$usec + (float)$sec);
    }

    /**
     * receiverAddBatch($collection)
     * Function intended to update the elements using the API
     *
     * @param CleverReachCollection $collection
     *
     * @return array
     * @throws \Exception
     */
    public function receiverAddBatch($collection)
    {
        $iterator = $collection->getUserFilteredIterator();
        $request  = array();

        foreach ($iterator as $byUserCollection) {
            $data      = $this->extractFilter($byUserCollection);
            $request[] = $data;
        }

        if (count($request) === 0) {
            return false;
        }

        $result = $this->post("/groups/{$this->listId}/receivers/upsert", $request);

        foreach ($result as $row) {
            if (is_string($row)) {
                if (strstr($row, 'invalid address')) {
                    throw new CleverReachException('Export Failed.');
                }
            }
        }

        foreach ($collection as $key => $element) {
            $collection->addTransferred($key);
        }
        $collection->setTransfer();

        return $result;
    }

    /**
     * Fetch existing global attributes from cleverreach
     */
    protected function getExistingAttributes()
    {
        $this->attributes = $this->get('/attributes');
        $attributeNames   = array();
        foreach ($this->attributes as $attribute) {
            $attributeNames[] = $attribute->name;
        }
        $this->attributes = $attributeNames;
    }

    /**
     * @param $key
     * @throws \Exception
     */
    protected function addGlobalAttributes($key)
    {
        /** Attribute already exists in cleverreach */
        if (in_array($key, $this->attributes)) {
            return;
        }

        $data = array(
            'name' => $key,
            'type' => $key == 'birthday' ? 'date' : 'text',
        );

        $this->post('/attributes', $data);
    }

    /**
     * Extract filters from collection
     *
     * @param CleverReachCollectionFilter $filter
     *
     * @return array
     * @throws \Exception
     */
    public function extractFilter(CleverReachCollectionFilter $filter)
    {
        $user = array('orders' => array());
        $this->getExistingAttributes();

        foreach ($filter as $element) {
            if (Registry::getConfig()->getConfigParam('iUtfMode') == 0) {
                array_walk($element, array($this, 'convertToUTF8'));
            }

            $user['active']                          = $element['active'];
            $user['email']                           = $element['email'];
            $user['activated']                       = $element['activated'];
            $user['registered']                      = $element['registered'];
            $user['source']                          = $element['source'];
            $user['global_attributes']               = array();
            $user['global_attributes']["salutation"] = $element['salutation'];
            $user['global_attributes']["firstname"]  = $element['firstname'];
            $user['global_attributes']["lastname"]   = $element['lastname'];
            $user['global_attributes']["company"]    = $element['company'];
            $user['global_attributes']["street"]     = $element['street'];
            $user['global_attributes']["city"]       = $element['city'];
            $user['global_attributes']["zip"]        = $element['zip'];
            $user['global_attributes']["birthday"]   = $element['birthday'];
            $user['global_attributes']["shop"]       = $element['shop'];
            $user['global_attributes']["country"]    = $element['country'];
            $user['global_attributes']["language"]   = $element['language'];

            $hasOrderFlag = Registry::getConfig()->getShopConfVar(CleverReachConfig::SETTING_ORDER_FLAG);

            if (
                $element['order_id'] !== null &&
                $hasOrderFlag === true
            ) {
                $order                  = array();
                $order['order_id']      = $element['order_id'];
                $order['purchase_date'] = $element['purchase_date'];
                $order['source']        = $element['source'];
                $order['amount']        = $element['amount'];
                $order['product_id']    = $element['product_id'];
                $order['product']       = $element['product'];
                $order['price']         = $element['price'];
                $user['orders'][]       = $order;
            }
        }


        foreach ($user['global_attributes'] as $key => $value) {
            $this->addGlobalAttributes($key);
        }

        return $user;
    }

    /**
     * Converts a string to utf8
     *
     * @param string $toConvert
     *
     * @return void
     */
    public function convertToUTF8(&$toConvert)
    {
        if (is_string($toConvert) === true) {
            $toConvert = iconv('ISO-8859-15', 'UTF-8', $toConvert);
        }
    }

    /**
     * @param $email
     * @return mixed
     * @throws \Exception
     */
    public function receiverSetInactive($email)
    {
        $result = $this->put("/groups/{$this->listId}/receivers/{$email}/setinactive");

        return $result;
    }

    /**
     * @param $data
     * @return mixed
     * @throws \Exception
     */
    public function clientRegisterMyProductSearch($data)
    {
        $result = $this->post('/mycontent', $data);


        return $result;
    }

    /**
     * Check if the OAuth token is saved compare it's timestamp with current time to see if it's still valid
     * @param int|string $shopId
     * @return bool
     */
    public function tc_isOAuthTokenValid($shopId = null)
    {
        return CleverReachOAuthController::tc_isOAuthTokenValid($shopId);
    }

    /**
     * @param $name
     * @return mixed
     * @throws \Exception
     */
    public function createList($name)
    {
        $result = $this->post('/groups', array('name' => $name));

        return $result;
    }

    /**
     * @param $id
     * @throws \Exception
     * @return mixed
     */
    public function getList($id)
    {
        try {
            $result = $this->get('/groups/' . $id);
        } catch (CleverReachException $e) {
            if ($e->getMessage() === '404;Not Found'){

                return false;
            }
        }

        return $result->id == $id;
    }

    /**
     * @param $id
     * @throws \Exception
     * @return mixed
     */
    public function getListById($id)
    {
        try {
            $result = $this->get('/groups/' . $id);
        } catch (CleverReachException $e) {
            if ($e->getMessage() === '404;Not Found'){

                return false;
            }
        }

        return $result;
    }

    /**
     * @throws \Exception
     * @return bool|mixed
     */
    public function getAllLists()
    {
        $result = false;
        try {
            $result = $this->get('/groups/');
        } catch (CleverReachException $e) {
            if ($e->getMessage() === '404;Not Found'){

                return false;
            }
        }

        return $result;
    }
}
