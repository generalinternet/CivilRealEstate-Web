<?php
/**
 * Description of AbstractMLSAreaFactory
 *
 * @author General Internet
 * @copyright  2019 General Internet
 * @version    4.0.0
 */
abstract class AbstractMLSAreaFactory extends GI_ModelFactory {

    protected static $dbType = 'rets';
    protected static $primaryDAOTableName = 'mls_area';
    protected static $models = array();
    protected static $modelsRefKey = array();
    
    /**
     * @param string $typeRef
     * @param GI_DataMap $map
     * @return AbstractMLSArea
     */
    protected static function buildModelByTypeRef($typeRef, $map) {
        switch ($typeRef) {
            default:
                $model = new MLSArea($map);
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
     * @return AbstractMLSArea
     */
    public static function buildNewModel($typeRef = '') {
        return parent::buildNewModel($typeRef);
    }
    
    /**
     * @param string $ref
     * @return AbstractMLSArea
     */
    public static function getModelByRef($ref){
        if(isset(static::$modelsRefKey[$ref])){
            return static::$modelsRefKey[$ref];
        }
        
        $result = MLSAreaFactory::search()
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
     * @return AbstractMLSArea
     */
    public static function getModelByRefOrCreate($ref, $title){
        $mlsArea = static::getModelByRef($ref);
        if(!$mlsArea){
            $mlsArea = static::buildNewModel();
            $mlsArea->setProperty('title', $title);
            $mlsArea->setProperty('ref', $ref);
            $mlsArea->save();
        }
        return $mlsArea;
    }
    
    public static function search() {
        $dataSearch = parent::search();
        $dataSearch->orderBy('title', 'ASC', true);
        $dataSearch->setSortAscending(true);
        return $dataSearch;
    }
    
}
