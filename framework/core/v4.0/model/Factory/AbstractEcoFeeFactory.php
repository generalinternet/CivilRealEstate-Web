<?php
/**
 * Description of AbstractEcoFeeFactory
 *
 * @author General Internet
 * @copyright  2016 General Internet
 * @version    2.0.0
 */
abstract class AbstractEcoFeeFactory extends GI_ModelFactory {

    protected static $primaryDAOTableName = 'eco_fee';
    protected static $models = array();
    protected static $modelsRefKey = array();
    protected static $optionsArray = NULL;

    /**
     * @param string $typeRef
     * @param GI_DataMap $map
     * @return AbstractEcoFee
     */
    protected static function buildModelByTypeRef($typeRef, $map) {
        switch ($typeRef) {
            case 'by_container_size':
                $model = new EcoFeeByContainerSize($map);
                break;
            case 'eco_fee':
            case 'by_unit':
            default:
                $model = new EcoFee($map);
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
            case 'eco_fee':
                $typeRefs = array('eco_fee');
                break;
            case 'by_unit':
                $typeRefs = array('by_unit');
                break;
            case 'by_container_size':
                $typeRefs = array('by_container_size');
                break;
            default:
                $typeRefs = array();
                break;
        }
        return $typeRefs;
    }
    
    /**
     * 
     * @param integer $id
     * @param boolean $force
     * @return AbstractEcoFee
     */
    public static function getModelById($id, $force = false) {
        return parent::getModelById($id, $force);
    }
    
    /** @return GI_DataSearch */
    public static function search() {
        $dataSearch = parent::search();
        $dataSearch->setSortAscending(true);
        return $dataSearch;
    }

}
