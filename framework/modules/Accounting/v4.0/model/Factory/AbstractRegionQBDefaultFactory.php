<?php
/**
 * Description of AbstractRegionQBDefaultFactory
 *
 * @author General Internet
 * @copyright  2018 General Internet
 * @version    3.0.0
 */
abstract class AbstractRegionQBDefaultFactory extends GI_ModelFactory {

    protected static $primaryDAOTableName = 'region_qb_default';
    protected static $models = array();

    protected static function buildModelByTypeRef($typeRef, $map) {
        switch ($typeRef) {
            case 'tax_code':
            case 'purchase':
            case 'sales':
                $model = new RegionQBDefaultTaxCode($map);
                break;
            case 'product':
            case 'sales_eco_fee':
                $model = new RegionQBDefaultProduct($map);
                break;
            default:
                $model = NULL;
                break;
        }
        return static::setFactoryClassName($model);
    }

    public static function getTypeRefArrayFromTypeRef($typeRef) {
        switch ($typeRef) {
            case 'tax_code':
                $typeRefs = array('tax_code', 'tax_code');
                break;
            case 'purchase':
                $typeRefs = array('tax_code','purchase');
                break;
            case 'sales':
                $typeRefs = array('tax_code','sales');
                break;
            case 'product':
                $typeRefs = array('product','product');
                break;
            case 'sales_eco_fee':
                $typeRefs = array('product','sales_eco_fee');
                break;
            case 'default':
                $typeRefs = array('default');
                break;
            default:
                $typeRefs = array();
                break;
        }
        return $typeRefs;
    }
    
}