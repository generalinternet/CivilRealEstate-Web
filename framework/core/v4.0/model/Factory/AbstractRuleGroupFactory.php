<?php

/**
 * Description of AbstractRuleGroupFactory
 *
 * @author General Internet
 * @copyright  2016 General Internet
 * @version    2.0.0
 */
abstract class AbstractRuleGroupFactory extends GI_ModelFactory {

    protected static $primaryDAOTableName = 'rule_group';
    protected static $models = array();

    protected static function buildModelByTypeRef($typeRef, $map) {
        switch ($typeRef) {
            case 'sales_order':
                $model = new RuleGroupSalesOrder($map);
                break;
            case 'discount':
                $model = new RuleGroupDiscount($map);
                break;
            case 'group':
            default:
                $model = new RuleGroup($map);
                break;
        }
        return static::setFactoryClassName($model);
    }

    /**
     * @param string $typeRef
     * @return string[]
     */
    protected static function getTypeRefArrayFromTypeRef($typeRef) {
        switch ($typeRef) {
            case 'group':
                $typeRefs = array('group');
                break;
            case 'sales_order':
                $typeRefs = array('sales_order');
                break;
            case 'discount':
                $typeRefs = array('discount');
                break;
            default:
                $typeRefs = array();
                break;
        }
        return $typeRefs;
    }

    /**
     * @param integer $id 
     * @param boolean $force
     * @return AbstractRuleGroup
     */
    public static function getModelById($id, $force = false) {
        return parent::getModelById($id, $force);
    }
    
    /**
     * @param string $typeRef
     * @return AbstractRuleGroup
     */
    public static function buildNewModel($typeRef = '') {
        return parent::buildNewModel($typeRef);
    }
    
    /**
     * Returns the model of $typeRef - There will only be either 0 or 1 models of any given type
     * @param string $typeRef
     * @return AbstractRuleGroup
     */
    public static function getModelByTypeRef($typeRef) {
        $search = static::search();
        $search->filterByTypeRef($typeRef, false);
        $array = $search->select();
        if (!empty($array)) {
            $model = $array[0];
            static::$models[$model->getProperty('id')] = $model;
            return $model;
        }
        return NULL;
    }
}