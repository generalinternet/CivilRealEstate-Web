<?php
/**
 * Description of AbstractMLSCityFactory
 *
 * @author General Internet
 * @copyright  2019 General Internet
 * @version    4.0.0
 */
abstract class AbstractMLSCityFactory extends GI_ModelFactory {

    protected static $dbType = 'rets';
    protected static $primaryDAOTableName = 'mls_city';
    protected static $models = array();
    protected static $modelsRefKey = array();
    
    /**
     * @param string $typeRef
     * @param GI_DataMap $map
     * @return AbstractMLSCity
     */
    protected static function buildModelByTypeRef($typeRef, $map) {
        switch ($typeRef) {
            default:
                $model = new MLSCity($map);
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
     * @return AbstractMLSCity
     */
    public static function buildNewModel($typeRef = '') {
        return parent::buildNewModel($typeRef);
    }
    
    /**
     * @param string $ref
     * @return AbstractMLSCity
     */
    public static function getModelByRef($ref){
        if(isset(static::$modelsRefKey[$ref])){
            return static::$modelsRefKey[$ref];
        }
        
        $result = MLSCityFactory::search()
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
     * @return AbstractMLSCity
     */
    public static function getModelByRefOrCreate($ref, $title){
        $mlsCity = static::getModelByRef($ref);
        if(!$mlsCity){
            $mlsCity = static::buildNewModel();
            $mlsCity->setProperty('title', $title);
            $mlsCity->setProperty('ref', $ref);
            $mlsCity->save();
        }
        return $mlsCity;
    }
    
    public static function search() {
        $dataSearch = parent::search();
        $dataSearch->orderBy('title', 'ASC', true);
        $dataSearch->setSortAscending(true);
        return $dataSearch;
    }
    
}
