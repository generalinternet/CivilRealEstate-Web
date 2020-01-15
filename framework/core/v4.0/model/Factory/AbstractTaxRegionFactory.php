<?php
/**
 * Description of AbstractTaxRegionFactory
 *
 * @author General Internet
 * @copyright  2017 General Internet
 * @version    2.0.1
 * @deprecated since Oct. 2018
 */
class AbstractTaxRegionFactory extends GI_ModelFactory {
    
    //All Abstract Factory classes must contain these 2 fields
    protected static $primaryDAOTableName = 'tax_link_to_region';
    protected static $models = array(); //this is used like an object pool by the superclass

    //All Abstract Factory classes must have this method defined
    protected static function buildModelByTypeRef($typeRef, $map) {
        switch ($typeRef) {
            default:
                $model = new TaxRegion($map);
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
     * @return TaxRegion
     */
    public static function getModelById($id, $force = false) {
        return parent::getModelById($id, $force);
    }

    /**
     * @param string $countryCode
     * @param string $regionCode
     * @param boolean $active
     * @return AbstractTaxRegion[]
     */
    public static function getTaxRegionsByCodes($countryCode, $regionCode, $active = true) {
        if ($active) {
            $activeFlag = 1;
        } else {
            $activeFlag = 0;
        }
        $region = RegionFactory::getModelByCodes($countryCode, $regionCode);
        if($region){
            $regionId = $region->getProperty('id');
            $taxRegionArray = TaxRegionFactory::search()
                    ->filter('active', $activeFlag)
                    ->filter('region_id', $regionId)
                    ->orderBy('tax_id')
                    ->select();
            return $taxRegionArray;
        } else {
            return array();
        }
    }
    
    /**
     * @param AbstractRegion $region
     * @param boolean $active
     * @return AbstractTaxRegion[]
     */
    public static function getTaxRegionsByRegion(AbstractRegion $region, $active = true) {
        if ($active) {
            $activeFlag = 1;
        } else {
            $activeFlag = 0;
        }
        $regionId = $region->getProperty('id');
        $taxRegionArray = TaxRegionFactory::search()
                ->filter('active', $activeFlag)
                ->filter('region_id', $regionId)
                ->orderBy('tax_id')
                ->select();
        return $taxRegionArray;
    }
    
    /**
     * @param string $countryCode
     * @param string $regionCode
     * @param boolean $active
     * @return array
     */
    public static function getTaxRegionOptionsByCodes($countryCode, $regionCode, $active = true){
        $taxRegions = static::getTaxRegionsByCodes($countryCode, $regionCode, $active);
        $taxRegionOptions = array();
        foreach($taxRegions as $taxRegion){
            $taxRegionOptions[$taxRegion->getId()] = $taxRegion->getTaxTitle();
        }
        
        return $taxRegionOptions;
    }
    
    /**
     * @return AbstractTaxRegion[]
     */
    public static function getDefaultTaxRegions(){
        $region = RegionFactory::getModelByCodes(DEFAULT_COUNTRY, DEFAULT_REGION);
        if($region){
            $defaultTaxRegions = ProjectConfig::getDefaultTaxRefs();
            
            $regionId = $region->getProperty('id');
            $taxRegionTable = static::getDbPrefix() . 'tax_link_to_region';
            $taxRegionSearch = TaxRegionFactory::search()
                    ->join('tax', 'id', $taxRegionTable, 'tax_id', 'T')
                    ->filterIn('T.ref', $defaultTaxRegions)
                    ->filter('region_id', $regionId)
                    ->orderBy('tax_id');
            $taxRegions = $taxRegionSearch->select();
            return $taxRegions;
        } else {
            return array();
        }
    }

}
