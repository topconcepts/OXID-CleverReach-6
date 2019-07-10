<?php

namespace TopConcepts\CleverReach\Core\Prodsearch;


use OxidEsales\Eshop\Core\DatabaseProvider;
use OxidEsales\Eshop\Core\Registry;
use OxidEsales\Eshop\Core\Request;
use TopConcepts\CleverReach\Model\CleverReachProdsearch;

/**
 * Handles incoming search request from prodsearch_controller
 *
 * @class Handler
 */
class Handler
{
    /**
     * constant request parameter for categories currently (inactive)
     *
     * @var string
     */
    CONST REQUEST_CAT = 'category';

    /**
     * constant request parameter for manufacturer requests (currently inactive)
     *
     * @var string
     */
    CONST REQUEST_MANU = 'manufacturer';

    /**
     * constnat requrest parameter for product requests
     *
     * @var string
     */
    CONST REQUEST_PROD = 'product';


    /**
     * Handles incoming request
     * Checks for valid pw if given
     * Checks for correct loading
     * Builds form data with elements and results
     * if specific result parameter is given
     *
     * @return string
     * @throws \OxidEsales\Eshop\Core\Exception\DatabaseConnectionException
     */
    public function handle()
    {
        $passwd           = Registry::get(Request::class)->getRequestEscapedParameter('password');
        $hasArticleSearch = Registry::get(Request::class)->getRequestEscapedParameter(self::REQUEST_PROD);

        $query = "
            SELECT
                oxid
            FROM
                tc_cleverreach_prodsearch
            WHERE
                password = ? LIMIT 1";

        $id     = DatabaseProvider::getDB()->getOne($query, array($passwd));
        $search = oxNew(CleverReachProdsearch::class);
        $loaded = $search->load($id);

        if ($loaded === false) {
            return json_encode('error, no item found for product search');
        }

        $elements = $search->getFormElements();
        if (count($elements) === 0) {
            return json_encode('error, no elements for this search');
        }

        // output json
        $formData = array();
        foreach ($elements as $element) {
            $formData[] = $element->getFormularData();
        }

        // CR backend user triggered a search for products
        if (isset($hasArticleSearch) === true) {

            // currently we are supporting only oxsearch dependent search
            // and no filtering by category or manufacturer
            $finder     = oxNew(FinderOxsearch::class);
            $products   = $finder->getSearchProducts($hasArticleSearch);
            $result     = oxNew(Result::class, $products);
            $prodResult = $result->getResultArray();
            $formData   = array_merge($formData, $prodResult);
        }

        return json_encode($formData);
    }
}
