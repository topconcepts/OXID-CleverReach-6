<?php

namespace TopConcepts\CleverReach\Core\Prodsearch;

use OxidEsales\Eshop\Application\Model\Search;

/**
 *
 * Uses oxsearch as search result retrieval
 *
 * @class tc_cleverreach_prodsearch_finder_oxsearch
 */
class FinderOxsearch implements Finder
{

    /**
     * Gets the search results like oxid does
     *
     * @param $searchString
     * @return mixed
     */
    public function getSearchProducts($searchString)
    {

        $search = oxNew(Search::class);

        return $search->getSearchArticles($searchString, false, false, false, false);

    }

}
