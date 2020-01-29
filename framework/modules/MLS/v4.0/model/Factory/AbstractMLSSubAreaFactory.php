<?php
/**
 * Description of AbstractMLSSubAreaFactory
 *
 * @author General Internet
 * @copyright  2019 General Internet
 * @version    4.0.0
 */
abstract class AbstractMLSSubAreaFactory extends GI_ModelFactory {

    protected static $dbType = 'rets';
    protected static $primaryDAOTableName = 'mls_sub_area';
    protected static $models = array();
    protected static $modelsRefKey = array();
    
    /**
     * @param string $typeRef
     * @param GI_DataMap $map
     * @return AbstractMLSSubArea
     */
    protected static function buildModelByTypeRef($typeRef, $map) {
        switch ($typeRef) {
            default:
                $model = new MLSSubArea($map);
                break;
        }
        return static::setFactoryClassName($model);
    }
    
    /**
     * @param type $typeRef
     * @return array
     */
    protected static function getTypeRefArrayFromTypeRef($typeRef) {
        switch ($typeRef) {
            default:
                $typeRefs = array();
                break;
        }
        return $typeRefs;
    }
    
    /**
     * @param string $typeRef
     * @return AbstractMLSSubArea
     */
    public static function buildNewModel($typeRef = '') {
        return parent::buildNewModel($typeRef);
    }
    
    /**
     * @param string $ref
     * @return AbstractMLSSubArea
     */
    public static function getModelByRef($ref){
        if(isset(static::$modelsRefKey[$ref])){
            return static::$modelsRefKey[$ref];
        }
        
        $result = MLSSubAreaFactory::search()
                ->filter('ref', $ref)
                ->select();
        
        if($result){
            static::$modelsRefKey[$ref] = $result[0];
            return static::$modelsRefKey[$ref];
        }
        
        return NULL;
    }
    
    /**
     * @param string $ref
     * @param string $title
     * @return AbstractMLSSubArea
     */
    public static function getModelByRefOrCreate($ref, $title){
        $mlsSubArea = static::getModelByRef($ref);
        if(!$mlsSubArea){
            $mlsSubArea = static::buildNewModel();
            $mlsSubArea->setProperty('title', $title);
            $mlsSubArea->setProperty('ref', $ref);
            $mlsSubArea->save();
        }
        return $mlsSubArea;
    }
    
    public static function search() {
        $dataSearch = parent::search();
        $dataSearch->orderBy('title', 'ASC', true);
        $dataSearch->setSortAscending(true);
        return $dataSearch;
    }
    
}
