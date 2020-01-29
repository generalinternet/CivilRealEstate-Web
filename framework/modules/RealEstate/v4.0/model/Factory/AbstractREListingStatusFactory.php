<?php

abstract class AbstractREListingStatusFactory extends GI_ModelFactory {
    
    protected static $primaryDAOTableName = 're_listing_status';
    protected static $models = array();
    protected static $modelsRefKey = array();
    
    /**
     * @param string $typeRef
     * @param GI_DataMap $map
     * @return AbstractREListingStatus
     */
    protected static function buildModelByTypeRef($typeRef, $map) {
        switch ($typeRef) {
            default:
                $model = new REListingStatus($map);
                break;
        }
        return self::setFactoryClassName($model);
    }
    
    public static function getTypeRefArrayFromTypeRef($typeRef) {
        switch ($typeRef) {
            default:
                $typeRefs = array();
                break;
        }
        return $typeRefs;
    }
    
    /**
     * @param string $typeRef
     * @return AbstractREListingStatus
     */
    public static function buildNewModel($typeRef = '') {
        return parent::buildNewModel($typeRef);
    }
    
//    /**
//     * @return AbstractREListingStatus
//     */
//    public static function getSelectableStatuses(){
//        $statuses = static::search()
//                ->filter('selectable', 1)
//                ->orderBy('pos', 'ASC')
//                ->select();
//        return $statuses;
//    }
    
    /**
     * @param string $ref
     * @return AbstractREListingStatus
     */
    public static function getModelByRef($ref){
        if(isset(static::$modelsRefKey[$ref])){
            return static::$modelsRefKey[$ref];
        }
        
        $result = static::search()
                ->filter('ref', $ref)
                ->select();
        
        if($result){
            static::$modelsRefKey[$ref] = $result[0];
            return static::$modelsRefKey[$ref];
        }
        
        return NULL;
    }
    
    public static function getOptionsArray($selectable = true) {
        $returnArray = array();
        $daoSearch = static::search();
        if ($selectable) {
            $daoSearch->filter('selectable', 1);
        }
        $daos = $daoSearch->orderBy('pos', 'ASC')
                ->select();
        if (!empty($daos)) {
            foreach ($daos as $dao) {
                $returnArray[$dao->getProperty('id')] = $dao->getProperty('title');
            }
        }
        return $returnArray;    
    }
}
