<?php
/**
 * Description of AbstractPricingRegionFactory
 *
 * @author General Internet
 * @copyright  2016 General Internet
 * @version    2.0
 */
class AbstractPricingRegionFactory extends GI_ModelFactory {
    
    protected static $primaryDAOTableName = 'pricing_region';
    protected static $models = array(); 
    protected static $modelsRefKey = array();
    protected static $optionsArray = array();

    protected static function buildModelByTypeRef($typeRef, $map) {
        switch ($typeRef) {
            default:
                $model = new PricingRegion($map);
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
     * @return AbstractPricingRegion
     */
    public static function getModelById($id, $force = false) {
        return parent::getModelById($id, $force);
    }

    /**
     * @param AbstractContactInfoAddress $address
     * @return AbstractPricingRegion[]
     */
    public static function getPricingRegionsByAddress(AbstractContactInfoAddress $address) {
        $addrRegion = $address->getProperty('contact_info_address.addr_region');
        $addrCountry = $address->getProperty('contact_info_address.addr_country');
        $pricingRegionTableName = PricingRegionFactory::getDbPrefix() . 'pricing_region';
        $regionSpecificSearch = PricingRegionFactory::search()
                ->join('pricing_region_incl', 'pricing_region_id', $pricingRegionTableName, 'id', 'INCL')
                ->filter('INCL.country_code', $addrCountry)
                ->filter('INCL.region_code', $addrRegion);
        $regionPricingRegions = $regionSpecificSearch->select();
        if (!empty($regionPricingRegions)) {
            return $regionPricingRegions;
        }
        $countrySpecificSearch = PricingRegionFactory::search()
                ->join('pricing_region_incl', 'pricing_region_id', $pricingRegionTableName, 'id', 'INCL')
                ->filter('INCL.country_code', $addrCountry)
                ->filterNULL('INCL.region_code');

        $countryPricingRegions = $countrySpecificSearch->select();
        if (!empty($countryPricingRegions)) {
            return $countryPricingRegions;
        }
        $noCountryOrRegionSearch = PricingRegionFactory::search()
                ->join('pricing_region_incl', 'pricing_region_id', $pricingRegionTableName, 'id', 'INCL')
                ->filterNULL('INCL.country_code')
                ->filterNULL('INCL.region_code');
        $noCountryOrRegionRegions = $noCountryOrRegionSearch->select();
        return $noCountryOrRegionRegions;
    }
    
    /**
     * @param string $ref
     * @return AbstractPricingRegion
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
    
    /** @return GI_DataSearch */
    public static function search() {
        $dataSearch = parent::search();
        $dataSearch->setSortAscending(true);
        $dataSearch->orderBy('pos', 'ASC', true);
        return $dataSearch;
    }

}
