<?php
/**
 * Description of AbstractTaxCollectionTypeFactory
 *
 * @author General Internet
 * @copyright  2017 General Internet
 * @version    4.0.0
 */
abstract class AbstractTaxCollectionTypeFactory extends GI_ModelFactory {

    protected static $primaryDAOTableName = 'tax_collection_type';
    protected static $models = array();
    protected static $modelsRefKey = array();
    protected static $optionsArray = NULL;
    
    /**
     * @param string $typeRef
     * @param GI_DataMap $map
     * @return TaxCollectionType
     */
    protected static function buildModelByTypeRef($typeRef, $map) {
        switch ($typeRef) {
            default:
                $model = new TaxCollectionType($map);
                break;
        }
        return static::setFactoryClassName($model);
    }
    
    /**
     * @param string $typeRef
     * @return array
     */
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
     * @return TaxCollectionType
     */
    public static function buildNewModel($typeRef = '') {
        return parent::buildNewModel($typeRef);
    }
    
    /**
     * @param string $id
     * @param boolean $force
     * @return TaxCollectionType
     */
    public static function getModelById($id, $force = false){
        return parent::getModelById($id, $force);
    }
    
    /**
     * @param string $ref
     * @return FOBShippingType
     */
    public static function getModelByRef($ref) {
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
    
    /**
     * @param string $valueColumn
     * @return string[]
     */
    public static function getOptionsArray($valueColumn = 'title') {
        if (empty(static::$optionsArray)) {
            $models = static::search()
                    ->filter('active', 1)
                    ->orderBy('pos', 'ASC')
                    ->select();
            
            if (!empty($models)) {
                foreach ($models as $model) {
                    $id = $model->getId();
                    $title = $model->getProperty($valueColumn);
                    $returnArray[$id] = $title;
                }
            }
            static::$optionsArray = $returnArray;
        }
        return static::$optionsArray;
    }
    
}
