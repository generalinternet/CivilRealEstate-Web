<?php

/**
 * Description of AbstractRuleFactory
 *
 * @author General Internet
 * @copyright  2016 General Internet
 * @version    2.0.0
 */
abstract class AbstractRuleFactory extends GI_ModelFactory {

    protected static $primaryDAOTableName = 'rule';
    protected static $models = array();

    protected static function buildModelByTypeRef($typeRef, $map) {
        switch ($typeRef) {
            case 'rule':
            default:
                $model = new Rule($map);
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
            case 'rule':
                $typeRefs = array('rule');
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
     * @return AbstractRule
     */
    public static function getModelById($id, $force = false) {
        return parent::getModelById($id, $force);
    }

    /**
     * @param string $typeRef
     * @return AbstractRule
     */
    public static function buildNewModel($typeRef = '') {
        return parent::buildNewModel($typeRef);
    }

    /**
     * @param AbstractRuleGroup $ruleGroup
     * @param string[] $ruleActions
     * @param boolean $idAsKey
     * @return AbstractRule[]
     */
    public static function getModelArrayByRuleGroup(AbstractRuleGroup $ruleGroup, $ruleActions = NULL, $idAsKey = false) {
        $ruleSearch = static::search();
        $ruleSearch->filter('rule_group_id', $ruleGroup->getProperty('id'));
        if (!empty($ruleActions)) {
            $ruleTableName = static::getDbPrefix() . 'rule';
            $ruleSearch->join('rule_applies_to_rule_action', 'rule_id', $ruleTableName, 'id', 'RATRA'); 
            $count = count($ruleActions);
            if ($count > 1) {
                $ruleSearch->filterGroup();
                for ($i=0;$i<$count;$i++) {
                    $ruleSearch->filter('RATRA.rule_action_id', $ruleActions[$i]->getProperty('id'));
                    if ($i != ($count - 1)) {
                        $ruleSearch->orIf();
                    }
                }
                $ruleSearch->closeGroup()
                        ->andIf();
            } else {
                $ruleSearch->filter('RATRA.rule_action_id', $ruleActions[0]->getProperty('id'));
            }
        }
        return $ruleSearch->groupBy('id')
                ->orderBy('id')
                ->select($idAsKey);
    }

}
