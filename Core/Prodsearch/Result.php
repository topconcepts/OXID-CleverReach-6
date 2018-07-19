<?php

namespace TopConcepts\CleverReach\Core\Prodsearch;


use OxidEsales\Eshop\Application\Model\ArticleList;
use OxidEsales\Eshop\Core\Registry;
use TopConcepts\CleverReach\Core\Exception\CleverReachException;

/**
 * Generates search results for oxlist objects
 *
 * @class ResultResult
 */
class Result {

    /**
     * List object
     *
     * @var null|ArticleList
     */
    protected $list = null;

    /**
     * Sets list object
     *
     * @param ArticleList $list
     */
    public function __construct(ArticleList $list) {
        $this->list = $list;
    }

    /**
     * Builds json construct from list array
     *
     * @return array
     * @throws CleverReachException
     */
    public function getResultArray() {

        $outputArr = array();
        if (count($this->list) === 0) {
            throw new CleverReachException('article list is empty');
        }

        $outputArr['settings']                        = array();
        $outputArr['settings']['type']                = 'product';
        $outputArr['settings']['link_editable']       = false;
        $outputArr['settings']['link_text_editable']  = false;
        $outputArr['settings']['image_size_editable'] = false;
        $outputArr['items']                           = array();

        foreach ($this->list as $item) {
            $newArr['title']        = $item->oxarticles__oxtitle->value;
            $newArr['description']  = $item->getLongDesc();
            //$newArr['content']      = $item->getLongDesc();
            $newArr['image']        = $item->getThumbnailUrl();
            $link                   = preg_replace('/(\?force_sid=[\d\w]+)/', '', $item->getLink());
            $newArr['url']          = $link;
            $currency               = Registry::getConfig()->getActShopCurrencyObject();
            $newArr['price']        = $item->getFPrice() . ' ' . $currency->sign;
            $newArr['display_info'] = null;
            $outputArr['items'][]   = $newArr;
        }

        return $outputArr;
    }
}
