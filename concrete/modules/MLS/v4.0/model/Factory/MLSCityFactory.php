<?php

class MLSCityFactory extends AbstractMLSCityFactory {
    
    public static function getReferenceArray(){
        $models = MLSCityFactory::search()->select();
        $optionsArray = array();
        
        foreach($models as $model){
            $optionsArray[$model->getProperty('id')] = $model->getTitle();
        }
        
        return $optionsArray;
    }
    
    /** 
     * Get city list 
     * 
     * @param array $listingStatusArray
     * @param array $excludeIdArray
     * @return array $optionsArray
     */
    public static function getCityArray($listingStatusArray = ['Active'], $excludeIdArray = NULL, $idsAsKey = false) {
        $mlsCityTableName = dbConfig::getDbPrefix() . 'mls_city';
        $search = static::search();
        if (!empty($listingStatusArray) || !empty($excludeIdArray)) {
            $search->innerJoin('mls_listing', 'mls_city_id', $mlsCityTableName, 'id', 'ml');
            
            if (!empty($listingStatusArray)) {
                $search->filterIn('ml.listing_status', $listingStatusArray);
            }
                    
            if (!empty($excludeIdArray)) {
                $search->filterNotIn('id', $excludeIdArray);
            }
            
            $search->groupBy('id');
        }
        $models = $search->select();
        $optionsArray = array();
        foreach($models as $model){
            if ($idsAsKey) {
                $optionsArray[$model->getProperty('id')] = array (
                    'id' => $model->getProperty('id'),
                    'ref' => $model->getProperty('ref'),
                    'rets_ref' => $model->getProperty('rets_ref'),
                    'title' => $model->getProperty('title'),
                ); 
            } else {
                $optionsArray[] = array (
                    'id' => $model->getProperty('id'),
                    'ref' => $model->getProperty('ref'),
                    'rets_ref' => $model->getProperty('rets_ref'),
                    'title' => $model->getProperty('title'),
                ); 
            }
        }
        return $optionsArray;
    }
}
