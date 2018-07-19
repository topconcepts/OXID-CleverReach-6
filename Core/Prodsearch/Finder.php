<?php
/**
 * Interface for finder
 */
interface Finder {

    /**
     * Used to implements the handling of the search source
     * Where the search results should come from
     *
     * @param $searchString
     * @return mixed
     */
    public function getSearchProducts($searchString);

}