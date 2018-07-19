<?php

namespace TopConcepts\CleverReach\Model;


use OxidEsales\Eshop\Core\DatabaseProvider;
use OxidEsales\Eshop\Core\Model\MultiLanguageModel;
use OxidEsales\Eshop\Core\Registry;
use OxidEsales\Eshop\Core\UtilsView;
use TopConcepts\CleverReach\Core\CleverReachRestApi;
use TopConcepts\CleverReach\Core\Prodsearch\FormInput;
use TopConcepts\CleverReach\Core\Prodsearch\Handler;

/**
 * active record object for product search
 *
 * @class CleverReachProdsearch
 */
class CleverReachProdsearch extends MultiLanguageModel
{
    /**
     * Class name
     *
     * @var string
     */
    protected $_sClassName = CleverReachProdsearch::class;

    /**
     * clever reach api object
     *
     * @var CleverReachRestApi
     */
    protected $api = null;

    /**
     * Initialize object
     */
    public function __construct()
    {
        parent::__construct();
        $this->init('tc_cleverreach_prodsearch');

        $config = Registry::getConfig();
        $shopId = $config->getShopId();

        $this->api = oxNew(CleverReachRestApi::class, $shopId);
    }

    /**
     * Current Module only supports a single product search element in shop
     * @throws \OxidEsales\Eshop\Core\Exception\DatabaseConnectionException
     */
    public function loadCurrentProdSearch()
    {
        $id = DatabaseProvider::getDB()->getOne("SELECT oxid FROM tc_cleverreach_prodsearch LIMIT 1");
        $this->load($id);
    }

    /**
     * After successful parent save
     * we try registering the search to CR backend
     *
     * @return bool|string
     * @throws \Exception
     */
    public function save()
    {
        $ret = parent::save();

        if ($ret !== false) {

            try {
                $result = $this->api->clientRegisterMyProductSearch(
                    [
                        "name"     => $this->tc_cleverreach_prodsearch__name->value,
                        "url"      => $this->tc_cleverreach_prodsearch__url->value,
                        "password" => $this->tc_cleverreach_prodsearch__password->value,
                    ]
                );
            } catch (\Exception $e) {
                $apiError = json_decode($this->api->error);
            }

            $utils     = Registry::get(UtilsView::class);
            $duplicate = Registry::getLang()->translateString('TC_CLEVERREACH_PRODSEARCH_EXISTS');
            $success   = Registry::getLang()->translateString('TC_CLEVERREACH_PRODSEARCH_SUCCESS');
            $success   = sprintf($success, $this->tc_cleverreach_prodsearch__name->value);

            if ($result !== true) {
                switch ($apiError->error->code) {
                    case 409:
                        $utils->addErrorToDisplay($duplicate);
                        break;
                }
            } else {
                $utils->addErrorToDisplay($success);
            }
        }

        return $ret;

    }

    /**
     * Get all formular elements
     *
     * @return array
     */
    public function getFormElements()
    {

        $elements = array();

        if ($this->tc_cleverreach_prodsearch__article->value) {
            $settings   = array(
                'name'        => 'Article',
                'description' => 'Basic Article Search',
                'required'    => false,
                'query_key'   => Handler::REQUEST_PROD,
                'type'        => 'input',
            );
            $elements[] = new FormInput($settings);
        }

        return $elements;


        /*
        EXAMPLES FOR cat and manufacturer, if needed in future release:

        if ($this->tc_cleverreach_prodsearch__category->value) {
            $settings = array(
                'name'          => 'Category',
                'description'   => 'Place description here or leave emtpy',
                'required'      => false,
                'query_key'     => tc_cleverreach_prodsearch_handler::REQUEST_CAT,
                'type'          => 'dropdown',
            );
            $values = array(
                0 => array('value' => 123, 'text' => 'eins')
            );
            $elements[] = oxNew('tc_cleverreach_prodsearch_form_dropdown', $settings, $values);
        }
        if ($this->tc_cleverreach_prodsearch__manufacturer->value) {
            $settings = array(
                'name'          => 'Manufacturer',
                'description'   => 'Place description here or leave emtpy',
                'required'      => false,
                'query_key'     => tc_cleverreach_prodsearch_handler::REQUEST_MANU,
                'type'          => 'input',
            );
            $values = array(
                0 => array('value' => 123, 'text' => 'eins')
            );
            $elements[] = oxNew('tc_cleverreach_prodsearch_form_dropdown', $settings, $values);
        }
        */
    }

}