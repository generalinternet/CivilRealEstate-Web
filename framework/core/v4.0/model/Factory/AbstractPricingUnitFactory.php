<?php
/**
 * Description of AbstractPricingUnitFactory
 *
 * @author General Internet
 * @copyright  2017 General Internet
 * @version    2.0.4
 */
abstract class AbstractPricingUnitFactory extends GI_ModelFactory {

    protected static $primaryDAOTableName = 'pricing_unit';
    protected static $models = array();
    protected static $modelsRefKey = array();
    protected static $modelsLongRefKey = array();

    /**
     * @param string $typeRef
     * @param GI_DataMap $map
     * @return AbstractPricingUnit
     */
    protected static function buildModelByTypeRef($typeRef, $map) {
        switch ($typeRef) {
            case 'volume':
                $model = new PricingUnitVolume($map);
                break;
            case 'distance':
                $model = new PricingUnitDistance($map);
                break;
            case 'weight':
            case 'area':
            case 'pricing_unit':
            default:
                $model = new PricingUnit($map);
                break;
        }
        return static::setFactoryClassName($model);
    }

    protected static function getTypeRefArrayFromTypeRef($typeRef) {
        switch ($typeRef) {
            case 'volume':
                $typeRefs = array('volume');
                break;
            case 'distance':
                $typeRefs = array('distance');
                break;
            case 'weight':
                $typeRefs = array('weight');
                break;
            case 'area':
                $typeRefs = array('area');
                break;
            case 'pricing_unit':
                $typeRefs = array('pricing_unit');
                break;
            default:
                $typeRefs = array();
                break;
        }
        return $typeRefs;
    }

    /**
     * @param string $typeRef
     * @return AbstractPricingUnit
     */
    public static function buildNewModel($typeRef = '') {
        return parent::buildNewModel($typeRef);
    }

    /**
     * @param string $id
     * @param boolean $force
     * @return AbstractPricingUnit
     */
    public static function getModelById($id, $force = false) {
        return parent::getModelById($id, $force);
    }

    /**
     * @param string $ref
     * @return AbstractPricingUnit
     */
    public static function getModelByRef($ref) {
        if (isset(static::$modelsRefKey[$ref])) {
            return static::$modelsRefKey[$ref];
        }

        $result = static::search()
                ->filter('ref', $ref)
                ->select();

        if ($result) {
            static::$modelsRefKey[$ref] = $result[0];
            return static::$modelsRefKey[$ref];
        }
        return NULL;
    }

    /**
     * @param string $longRef
     * @return AbstractPricingUnit
     */
    public static function getModelByLongRef($longRef) {
        if (isset(static::$modelsLongRefKey[$longRef])) {
            return static::$modelsLongRefKey[$longRef];
        }

        $result = static::search()
                ->filter('long_ref', $longRef)
                ->select();

        if ($result) {
            static::$modelsLongRefKey[$longRef] = $result[0];
            return static::$modelsLongRefKey[$longRef];
        }
        return NULL;
    }

    /**
     * @param boolean $withRefAsKey
     * @param boolean $pluralTitles
     * @param boolean $startWithLongRef
     * @param string $typeRef
     * @param array $optionData
     * @return array
     */
    public static function getOptionsArray($withRefAsKey = false, $pluralTitles = false, $startWithLongRef = false, $typeRef = NULL, &$optionData = array()) {
        $returnArray = array();
        $search = static::searchActive()
                ->orderBy('pos', 'ASC')
                ->filter('active', 1);
        if (!empty($typeRef)) {
            $search->filterByTypeRef($typeRef);
        }
        $pricingUnits = $search->select();
        if (!empty($pricingUnits)) {
            foreach ($pricingUnits as $pricingUnit) {
                $pricingUnitId = $pricingUnit->getId();
                $ref = $pricingUnit->getProperty('ref');
                $longRef = $pricingUnit->getProperty('long_ref');
                $title = $pricingUnit->getProperty('title');
                $plTitle = $pricingUnit->getProperty('pl_title');
                $val = '';
                if ($withRefAsKey) {
                    $key = $ref;
                } else {
                    $key = $pricingUnitId;
                }
                if ($startWithLongRef) {
                    $val .= $longRef . ' - ';
                }
                if ($pluralTitles) {
                    $val .= $plTitle;
                } else {
                    $val .= $title;
                }
                $returnArray[$key] = $val;
                $optionData[$key] = array(
                    'unitType' => $pricingUnit->getUnitType(),
                    'unitRef' => $ref
                );
            }
        }
        return $returnArray;
    }

    /**
     * @return GI_DataSearch
     */
    public static function searchActive() {
        $giDataSearch = parent::search()
                ->filter('active', 1);
        return $giDataSearch;
    }

}
