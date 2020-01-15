<?php
/**
 * Description of AbstractUserHasLabourRateFactory
 *
 * @author General Internet
 * @copyright  2017 General Internet
 * @version    2.0.1
 */
abstract class AbstractUserHasLabourRateFactory extends GI_ModelFactory {

    protected static $primaryDAOTableName = 'user_has_labour_rate';
    protected static $models = array();
    
    /**
     * @param string $typeRef
     * @param GI_DataMap $map
     * @return AbstractUserHasLabourRate
     */
    protected static function buildModelByTypeRef($typeRef, $map) {
        switch ($typeRef) {
            default:
                $model = new UserHasLabourRate($map);
                break;
        }
        return static::setFactoryClassName($model);
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
     * @return AbstractUserHasLabourRate
     */
    public static function buildNewModel($typeRef = '') {
        return parent::buildNewModel($typeRef);
    }
    
    /**
     * @param string $id
     * @param boolean $force
     * @return AbstractUserHasLabourRate
     */
    public static function getModelById($id, $force = false){
        return parent::getModelById($id, $force);
    }
    
}
