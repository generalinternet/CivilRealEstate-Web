<?php
/**
 * Description of AbstractPermissionCategoryFactory
 *
 * @author General Internet
 * @copyright  2017 General Internet
 * @version    4.0.0
 */
class AbstractPermissionCategoryFactory extends GI_ModelFactory {
    
    protected static $primaryDAOTableName = 'permission_category';
    protected static $models = array();
    protected static $modelsRefKey = array();
    protected static $optionsArray = NULL;

    /**
     * @param string $typeRef
     * @param GI_DataMap $map
     * @return AbstractPermissionCategory
     */
    protected static function buildModelByTypeRef($typeRef, $map) {
        switch ($typeRef) {
            default:
                $model = new PermissionCategory($map);
                break;
        }
        return static::setFactoryClassName($model);
    }
    
    /**
     * 
     * @param string $typeRef
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
     * @return AbstractPermissionCategory
     */
    public static function buildNewModel($typeRef = '') {
        return parent::buildNewModel($typeRef);
    }
    
    /**
     * 
     * @param integer $id - the id of the model
     * @param boolean $force - Whether or not you want to force the system to update the model, or to use available model from object pool
     * @return AbstractPermissionCategory
     */
    public static function getModelById($id, $force = false) {
        return parent::getModelById($id, $force);
    }
    
    /** @return GI_DataSearch */
    public static function search() {
        $dataSearch = parent::search();
        $dataSearch->setSortAscending(true);
        return $dataSearch;
    }
    
    /**
     * @param string $ref
     * @return AbstractPermissionCategory
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
    
}
