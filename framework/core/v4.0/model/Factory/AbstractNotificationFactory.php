<?php
/**
 * Description of AbstractNotificationFactory
 *
 * @author General Internet
 * @copyright  2016 General Internet
 * @version    4.0
 */
class AbstractNotificationFactory extends GI_ModelFactory {
    
    //All Abstract Factory classes must contain these 2 fields
    protected static $primaryDAOTableName = 'notification';
    protected static $models = array(); //this is used like an object pool by the superclass
    
    /**
     * @param string $typeRef
     * @param GI_DataMap $map
     * @return AbstractNotification
     */
    protected static function buildModelByTypeRef($typeRef, $map) {
        switch ($typeRef) {
            default:
                $model = new Notification($map);
                break;
        }
        return static::setFactoryClassName($model);
    }
    
    
    /**
     * @param type $typeRef - can be empty string
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
     * @return AbstractNotification
     */
    public static function buildNewModel($typeRef = '') {
        return parent::buildNewModel($typeRef);
    }
    
    /**
     * @param integer $id - the id of the model
     * @param boolean $force - Whether or not you want to force the system to update the model, or to use available model from object pool
     * @return AbstractNotification
     */
    public static function getModelById($id, $force = false) {
        return parent::getModelById($id, $force);
    }
    
}
