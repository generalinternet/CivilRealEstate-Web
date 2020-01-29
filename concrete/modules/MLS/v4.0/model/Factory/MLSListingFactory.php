<?php

class MLSListingFactory extends AbstractMLSListingFactory {
    public static function getModifiedListingsByStatusRef($ref){
        $mlsListingTableName = dbConfig::getDbPrefix().'mls_listing';
        
        switch($ref){
            case 'H':
            case 'featured':
                $search = MLSListingFactory::search()
                        ->join('mls_listing_modified', 'mls_listing_id', $mlsListingTableName, 'id', 'mlm')
                        ->join('mls_listing_status', 'id', 'mlm', 'mls_listing_status', 'mlms')
                        ->groupBy('id')
                        ->filter('mlms.ref', 'H');
                break;
            default:
                $search = MLSListingFactory::search()
                        ->join('mls_listing_modified', 'mls_listing_id', $mlsListingTableName, 'id', 'mlm')
                        ->join('mls_listing_status', 'id', 'mlm', 'mls_listing_status', 'mlms')
                        ->groupBy('id')
                        ->filter('mlms.ref', $ref);
                break;
        }
        
        $models = $search->select();
        return $models;
    }
}
