<?php

/**
 * Description of AbstractRuleConditionFactory
 *
 * @author General Internet
 * @copyright  2016 General Internet
 * @version    2.0.0
 */
abstract class AbstractRuleConditionFactory extends GI_ModelFactory {

    protected static $primaryDAOTableName = 'rule_condition';
    protected static $models = array();

    protected static function buildModelByTypeRef($typeRef, $map) {
        switch ($typeRef) {
            case 'math':
                $model = new RuleConditionMath($map);
                break;
            case 'math_p_v':
                $model = new RuleConditionMathPV($map);
                break;
            case 'math_p_p':
                $model = new RuleConditionMathPP($map);
                break;
            case 'condition':
            default:
                $model = new RuleCondition($map);
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
            case 'condition':
                $typeRefs = array('condition');
                break;
            case 'math':
                $typeRefs = array('math', 'math');
                break;
            case 'math_p_v':
                $typeRefs = array('math', 'math_p_v');
                break;
            case 'math_p_p':
                $typeRefs = array('math', 'math_p_p');
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
     * @return AbstractRuleCondition
     */
    public static function getModelById($id, $force = false) {
        return parent::getModelById($id, $force);
    }

    /**
     * @param string $typeRef
     * @return AbstractRuleCondition
     */
    public static function buildNewModel($typeRef = '') {
        return parent::buildNewModel($typeRef);
    }
    
    /**
     * @param AbstractRule $rule
     * @param boolean $idAsKey
     * @return AbstractRuleCondition[]
     */
    public static function getModelArrayByRule(AbstractRule $rule, $idAsKey = false) {
        $search = static::search();
        $search->filter('rule_id', $rule->getProperty('id'))
                ->setSortAscending(true);
        return $search->select($idAsKey);
    }

}
