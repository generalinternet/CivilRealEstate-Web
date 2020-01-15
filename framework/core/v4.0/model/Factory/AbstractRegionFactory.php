<?php
/**
 * Description of AbstractRegionFactory
 *
 * @author General Internet
 * @copyright  2016 General Internet
 * @version    4.0.1
 */
abstract class AbstractRegionFactory extends GI_ModelFactory {
    
    protected static $primaryDAOTableName = 'region';
    protected static $models = array();
    protected static $optionGroupsArray = NULL;

    /**
     * @param string $typeRef
     * @param GI_DataMap $map
     * @return Region
     */
    protected static function buildModelByTypeRef($typeRef, $map) {
        switch ($typeRef) {
            default:
                $model = new Region($map);
                break;
        }
        return static::setFactoryClassName($model);
    }
    
    /**
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
     * @return Region
     */
    public static function buildNewModel($typeRef = '') {
        return parent::buildNewModel($typeRef);
    }
    
    /**
     * @param string $id
     * @param boolean $force
     * @return Region
     */
    public static function getModelById($id, $force = false){
        return parent::getModelById($id, $force);
    }
    
    /** @return GI_DataSearch */
    public static function search() {
        $dataSearch = parent::search();
        $dataSearch->setSortAscending(true);
        return $dataSearch;
    }

    /**
     * @param string $countryCode
     * @param string $regionCode
     * @return Region
     */
    public static function getModelByCodes($countryCode, $regionCode) {
        $regionArray = RegionFactory::search()
                ->filter('country_code', $countryCode)
                ->filter('region_code', $regionCode)
                ->select();
        if ($regionArray) {
            return $regionArray[0];
        }
        return NULL;
    }
    
    /**
     * @param string $valueColumn
     * @return string[]
     */
    public static function getOptionGroupsArray() {
        if (empty(static::$optionGroupsArray)) {
            $models = static::search()
                    ->orderBy('country_code', 'ASC')
                    ->orderBy('region_name', 'ASC')
                    ->select();
            
            $optionGroups = array();
            
            if (!empty($models)) {
                foreach ($models as $model) {
                    /* @var $model AbstractRegion */
                    $countryName = $model->getCountryName();
                    if(!isset($optionGroups[$countryName])){
                        $optionGroups[$countryName] = array();
                    }
                    $id = $model->getId();
                    $name = $model->getRegionName();
                    $optionGroups[$countryName][$id] = $name;
                }
            }
            static::$optionGroupsArray = $optionGroups;
        }
        return static::$optionGroupsArray;
    }
    
}
