<?php
/**
 * Description of AbstractSettingsFactory
 *
 * @author General Internet
 * @copyright  2016 General Internet
 * @version    2.0.0
 */
abstract class AbstractSettingsFactory extends GI_ModelFactory {
        protected static $primaryDAOTableName = 'settings';
    protected static $models = array();

    protected static function buildModelByTypeRef($typeRef, $map) {
        switch ($typeRef) {
            case 'qb':
                $model = new SettingsQB($map);
                break;
            default:
                $model = new Settings($map);
                break;
        }
        return static::setFactoryClassName($model);
    }
    
    /**
     * 
     * @param type $typeRef - can be empty string
     * @return array
     */
    protected static function getTypeRefArrayFromTypeRef($typeRef) {
        switch ($typeRef) {
            case 'settings':
                $typeRefs = array('settings');
                break;
            case 'notification':
                $typeRefs = array('notification');
                break;
            case 'qb':
                $typeRefs = array('qb');
                break;
            default:
                $typeRefs = array();
                break;
        }
        return $typeRefs;
    }
    /**
     * 
     * @param type $id - the id of the model
     * @param type $force - Whether or not you want to force the system to update the model, or to use available model from object pool
     * @return AbstractSettings
     */
    public static function getModelById($id, $force = false){ 
        return parent::getModelById($id, $force);
    }
}