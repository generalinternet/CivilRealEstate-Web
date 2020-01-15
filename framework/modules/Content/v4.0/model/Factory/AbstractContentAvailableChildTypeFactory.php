<?php
/**
 * Description of AbstractContentAvailableChildTypeFactory
 *
 * @author General Internet
 * @copyright  2017 General Internet
 * @version    4.0.1
 */
abstract class AbstractContentAvailableChildTypeFactory extends GI_ModelFactory {

    protected static $primaryDAOTableName = 'content_available_child_type';
    protected static $models = array();
    
    protected static function buildModelByTypeRef($typeRef, $map) {
        $model = new ContentAvailableChildType($map);
        return static::setFactoryClassName($model);
    }
    
    public static function getTypeRefArrayFromTypeRef($typeRef) {
        return array();
    }
    
    /**
     * 
     * @param string $typeRef
     * @return ContentAvailableChildType
     */
    public static function buildNewModel($typeRef = '') {
        return parent::buildNewModel($typeRef);
    }
    
}
