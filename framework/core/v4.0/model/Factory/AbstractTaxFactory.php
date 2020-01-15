<?php
/**
 * Description of AbstractTaxFactory
 *
 * @author General Internet
 * @copyright  2016 General Internet
 * @version    2.0
 */
class AbstractTaxFactory extends GI_ModelFactory {
    
    //All Abstract Factory classes must contain these 2 fields
    protected static $primaryDAOTableName = 'tax';
    protected static $models = array(); //this is used like an object pool by the superclass

    //All Abstract Factory classes must have this method defined
    protected static function buildModelByTypeRef($typeRef, $map) {
        switch ($typeRef) {
            default:
                $model = new Tax($map);
                break;
        }
        return static::setFactoryClassName($model);
    }
    
    //All Abstract Factory classes must have this method defined
    /**
     * 
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
     * 
     * @param type $id - the id of the model
     * @param type $force - Whether or not you want to force the system to update the model, or to use available model from object pool
     * @return Tax
     */
    public static function getModelById($id, $force = false) {
        return parent::getModelById($id, $force);
    }

    /**
     * 
     * @param string $ref
     * @return Tax
     */
    public static function getModelByRef($ref) {
        $taxModelArray = static::search()
                ->filter('ref', $ref)
                ->select();
        if ($taxModelArray) {
            return $taxModelArray[0];
        }
        return NULL;
    }

}
