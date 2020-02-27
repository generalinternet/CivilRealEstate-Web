<?php

class REListingFactory extends AbstractREListingFactory {
    public static function getSearchValue($key){
        $queryId = GI_URLUtils::getAttribute('queryId');
        if(empty($queryId)){
            return null;
        }
        $query = REListingFactory::search()->setQueryId($queryId);
        $queryVal = $query->getSearchValue($key);
        if(empty($queryVal)){
            return null;
        }
        return $queryVal;
    }

    public static function getUnionPageBar(GI_DataSearch $relistingSearch, GI_DataSearch $mlsListingSearch, $linkArray, $pageLinks = 3){
        if(!isset($linkArray['queryId'])){
            $linkArray['queryId'] = $relistingSearch->getQueryId();
        }
        $itemPerPage = $relistingSearch->getItemsPerPage();
        $count = $relistingSearch->getCount() + $mlsListingSearch->getCount();
        $pageNumber = $relistingSearch->getPageNumber();
        $pageBar = new PageBarView($linkArray, $itemPerPage, $count, $pageNumber, $pageLinks);
        return $pageBar;
    }

}
