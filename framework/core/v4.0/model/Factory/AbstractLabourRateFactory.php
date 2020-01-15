<?php
/**
 * Description of AbstractLabourRateFactory
 *
 * @author General Internet
 * @copyright  2017 General Internet
 * @version    2.0.0
 */
abstract class AbstractLabourRateFactory extends GI_ModelFactory {

    protected static $primaryDAOTableName = 'labour_rate';
    protected static $models = array();
    
    /**
     * @param string $typeRef
     * @param GI_DataMap $map
     * @return AbstractLabourRate
     */
    protected static function buildModelByTypeRef($typeRef, $map) {
        switch ($typeRef) {
            case 'labour':
            default:
                $model = new LabourRate($map);
                break;
        }
        return static::setFactoryClassName($model);
    }
    
    public static function getTypeRefArrayFromTypeRef($typeRef) {
        switch ($typeRef) {
            case 'labour':
                $typeRefs = array('labour');
                break;
            default:
                $typeRefs = array();
                break;
        }
        return $typeRefs;
    }
    
    /**
     * @param string $typeRef
     * @return AbstractLabourRate
     */
    public static function buildNewModel($typeRef = '') {
        return parent::buildNewModel($typeRef);
    }
    
    /**
     * @param string $id
     * @param boolean $force
     * @return AbstractLabourRate
     */
    public static function getModelById($id, $force = false){
        return parent::getModelById($id, $force);
    }
    
}
