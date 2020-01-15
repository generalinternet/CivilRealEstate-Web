<?php
/**
 * Description of AbstractApplicableTaxFactory
 *
 * @author General Internet
 * @copyright  2016 General Internet
 * @version    2.0
 */
class AbstractApplicableTaxFactory extends GI_ModelFactory {
    
    //All Abstract Factory classes must contain these 2 fields
    protected static $primaryDAOTableName = 'applicable_tax';
    protected static $models = array(); //this is used like an object pool by the superclass

    //All Abstract Factory classes must have this method defined
    protected static function buildModelByTypeRef($typeRef, $map) {
        switch ($typeRef) {
            default:
                $model = new ApplicableTax($map);
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
     * @return ApplicableTax
     */
    public static function getModelById($id, $force = false) {
        return parent::getModelById($id, $force);
    }

    public static function getModelsByRegionCodesAndTableName($regionCode, $countryCode, $tableName) {
        $region = RegionFactory::getModelByCodes($countryCode, $regionCode);
        $regionId = $region->getProperty('id');
        $applicableTaxTableName = dbConfig::getDbPrefix() . 'applicable_tax';
        $applicableTaxArray = static::search()
                ->join('tax_link_to_region', 'id', $applicableTaxTableName, 'tax_link_to_region_id', 'tltr')
                ->filter('table_name', $tableName)
                ->filter('tltr.region_id', $regionId)
                ->filter('tltr.active', 1)
                ->groupBy('id')
                ->select();
        return $applicableTaxArray;
    }

}
