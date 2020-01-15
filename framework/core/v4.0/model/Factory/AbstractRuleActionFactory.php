<?php

/**
 * Description of AbstractRuleActionFactory
 *
 * @author General Internet
 * @copyright  2016 General Internet
 * @version    2.0.0
 */
abstract class AbstractRuleActionFactory extends GI_ModelFactory {

    protected static $primaryDAOTableName = 'rule_action';
    protected static $models = array();
    protected static $optionsArray = NULL;

    protected static function buildModelByTypeRef($typeRef, $map) {
        switch ($typeRef) {
            default:
                $model = new RuleAction($map);
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
            default:
                $typeRefs = array();
                break;
        }
        return $typeRefs;
    }

    /**
     * @param integer $id 
     * @param boolean $force
     * @return AbstractRuleAction
     */
    public static function getModelById($id, $force = false) {
        return parent::getModelById($id, $force);
    }
    
    /**
     * @param string $typeRef
     * @return AbstractRuleAction
     */
    public static function buildNewModel($typeRef = '') {
        return parent::buildNewModel($typeRef);
    }
    
    public static function getModelArrayByRule(AbstractRule $rule, $idAsKey = false) {
        $ruleActionTableName = static::getDbPrefix() . 'rule_action';
        $search = static::search();
        $search->join('rule_applies_to_rule_action', 'rule_action_id', $ruleActionTableName, 'id', 'RATRA');
        $search->filter('RATRA.rule_id', $rule->getProperty('id'));
        return $search->select($idAsKey);
    }

    public static function linkRuleActionToRule(AbstractRuleAction $ruleAction, AbstractRule $rule) {
        $existingSearch = new GI_DataSearch('rule_applies_to_rule_action');
        $existingSearch->filter('rule_action_id', $ruleAction->getProperty('id'));
        $existingSearch->filter('rule_id', $rule->getProperty('id'));
        $existingLinks = $existingSearch->select();
        if (!empty($existingLinks)) {
            return true;
        }
        $softDeletedSearch = new GI_DataSearch('rule_applies_to_rule_action');
        $softDeletedSearch->filter('rule_action_id', $ruleAction->getProperty('id'));
        $softDeletedSearch->filter('rule_id', $rule->getProperty('id'));
        $softDeletedSearch->filter('status', 0);
        $softDeletedArray = $softDeletedSearch->select();
        if (!empty($softDeletedArray)) {
            $softDeletedLink = $softDeletedArray[0];
            $softDeletedLink->setProperty('status', 1);
            if ($softDeletedLink->save()) {
                return true;
            }
        }
        $defaultDAOClass = ApplicationConfig::getProperty('defaultDAOClass');
        $newLink = new $defaultDAOClass('rule_applies_to_rule_action');
        $newLink->setProperty('rule_action_id', $ruleAction->getProperty('id'));
        $newLink->setProperty('rule_id', $rule->getProperty('id'));
        if (!$newLink->save()) {
            return false;
        }
        return true;
    }

    public static function unlinkRuleActionFromRule(AbstractRuleAction $ruleAction, AbstractRule $rule) {
        $search = new GI_DataSearch('rule_applies_to_rule_action');
        $search->filter('rule_action_id', $ruleAction->getProperty('id'));
        $search->filter('rule_id', $rule->getProperty('id'));
        $existingLinks = $search->select();
        if (!empty($existingLinks)) {
            foreach ($existingLinks as $existingLink) {
                if (!$existingLink->softDelete()) {
                    return false;
                }
            }
        }
        return true;
    }
    
    public static function getModelByRef($ref) {
        $search = static::search();
        $search->filter('ref', $ref);
        $array = $search->select();
        if (!empty($array)) {
            return $array[0];
        }
        return NULL;
    }

}
