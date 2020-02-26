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
}
