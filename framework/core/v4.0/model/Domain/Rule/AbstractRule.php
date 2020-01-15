<?php
/**
 * Description of AbstractRule
 *
 * @author General Internet
 * @copyright  2016 General Internet
 * @version    2.0.0
 */
abstract class AbstractRule extends GI_FormRowableModel {
    
    protected $ruleGroup = NULL;
    protected $conditions = NULL;
    protected $ruleActions = NULL;
    
    public function getFormView(GI_Form $form) {
        return new RuleFormView($form, $this);
    }

    public function getDetailView() {
        return new RuleDetailView($this);
    }

    public function getConditions(GI_Form $form = NULL, $returnNewIfNull = true, $newConditionTypeRef = 'condition') {
        $conditions = array();
        if (!empty($form) && $form->wasSubmitted()) {
            $ruleSeqNum = $this->getSeqNumber();
            $seqNums = filter_input(INPUT_POST, 'conditions_' . $ruleSeqNum, FILTER_DEFAULT, FILTER_REQUIRE_ARRAY);
            if($seqNums){
                foreach ($seqNums as $seqNum) {
                    $conditionId = filter_input(INPUT_POST, 'condition_id_' . $ruleSeqNum . '_' . $seqNum);
                    $typeRef = filter_input(INPUT_POST, 'condition_type_' . $ruleSeqNum . '_' . $seqNum);
                    if (!empty($conditionId)) {
                        $condition = RuleConditionFactory::changeModelType(RuleConditionFactory::getModelById($conditionId), $typeRef);
                    } else {
                        $condition = RuleConditionFactory::buildNewModel($typeRef);
                    }
                    $condition->setRule($this);
                    $condition->setFieldSuffix($ruleSeqNum);
                    $condition->setSeqNumber($seqNum);
                    $conditions[] = $condition;
                }
            }
        } else {
            if (empty($this->conditions)) {
                $this->conditions = RuleConditionFactory::getModelArrayByRule($this);
            }
            $conditions = $this->conditions;
        }
        if (empty($conditions) && $returnNewIfNull) {
            $condition = RuleConditionFactory::buildNewModel($newConditionTypeRef);
            $condition->setRule($this);
            $conditions[] = $condition;
        }
        return $conditions;
    }
    
    public function getRuleActions() {
        if (empty($this->ruleActions)) {
            $this->ruleActions = RuleActionFactory::getModelArrayByRule($this);
        }
        return $this->ruleActions;
    }
    
    public function getRuleActionOptions() {
        $ruleGroup = $this->getRuleGroup();
        if (empty($ruleGroup)) {
            return array();
        }
        return $ruleGroup->getActionOptions();
    }
    
    public function getRuleGroup() {
        if (empty($this->ruleGroup)) {
            $this->ruleGroup = RuleGroupFactory::getModelById($this->getProperty('rule_group_id'));
        }
        return $this->ruleGroup;
    }
    
    public function setRuleGroup(AbstractRuleGroup $ruleGroup) {
        $this->ruleGroup = $ruleGroup;
    }
    
    public function applyRuleAction(AbstractRuleAction $ruleAction) {
        return RuleActionFactory::linkRuleActionToRule($ruleAction, $this);
    }
    
    public function unapplyRuleAction(AbstractRuleAction $ruleAction) {
        return RuleActionFactory::linkRuleActionToRule($ruleAction, $this);
    }

    public function handleFormSubmission(GI_Form $form) {
        if ($form->wasSubmitted() && $this->validateForm($form)) {
            $ruleGroup = $this->getRuleGroup();
            if (empty($ruleGroup)) {
                return false;
            }
            $this->setProperty('rule_group_id', $ruleGroup->getId());
            if (!$this->handleFormFields($form)) {
                return false;
            }
            $this->setProperty('rule_group_id', $ruleGroup->getId());
            if (!$this->save()) {
                return false;
            }
            if (!$this->handleActionsField($form)) {
                return false;
            }
            if (!$this->handleConditionRowsFormSubmission($form)) {
                return false;
            }
            return true;
        }
        return false;
    }
    
    public function validateForm(\GI_Form $form) {
        if (!$this->formValidated && $form->wasSubmitted() && $form->validate()) {
            
            //TODO - validate fields
            
            $conditions = $this->getConditions($form);
            if (!empty($conditions)) {
                foreach ($conditions as $condition) {
                    if (!$condition->validateForm($form)) {
                        $this->formValidated = false;
                        return $this->formValidated;
                    }
                }
            }
            $this->formValidated = true;
        }
        return $this->formValidated;
    }

    protected function handleFormFields(GI_Form $form) {
        if (!($form->wasSubmitted() && $this->validateForm($form))) {
            return false;
        }
        $negate = filter_input(INPUT_POST, $this->getFieldName('negate_actions'));
        $this->setProperty('negate_actions', $negate);

        return true;
    }

    protected function handleActionsField(GI_Form $form) {
        if (!($form->wasSubmitted() && $this->validateForm($form))) {
            return false;
        }
        $existingActions = RuleActionFactory::getModelArrayByRule($this, true);
        $actionIdsString = filter_input(INPUT_POST, $this->getFieldName('action_ids'));
        if (!empty($actionIdsString)) {
            $actionIds = explode(',', $actionIdsString);
            foreach ($actionIds as $actionId) {
                if (isset($existingActions[$actionId])) {
                    $action = $existingActions[$actionId];
                    unset($existingActions[$actionId]);
                } else {
                    $action = RuleActionFactory::getModelById($actionId);
                    if (!$this->applyRuleAction($action)) {
                        return false;
                    }
                }
            }
        }
        foreach ($existingActions as $actionToUnapply) {
            if (!$this->unapplyRuleAction($actionToUnapply)) {
                return false;
            }
        }
        return true;
    }

    public function handleConditionRowsFormSubmission(GI_Form $form) {
        if ($form->wasSubmitted() && $this->validateForm($form)) {
            $existingConditions = RuleConditionFactory::getModelArrayByRule($this, true);
            $ruleSeqNum = $this->getSeqNumber();
            $conditionSeqNums = filter_input(INPUT_POST, 'conditions_' . $ruleSeqNum, FILTER_DEFAULT, FILTER_REQUIRE_ARRAY);
            if (!empty($conditionSeqNums)) {
                foreach ($conditionSeqNums as $conditionSeqNum) {
                    $conditionId = filter_input(INPUT_POST, 'condition_id_' . $ruleSeqNum . '_' . $conditionSeqNum);
                    $typeRef = filter_input(INPUT_POST, 'condition_type_' . $ruleSeqNum . '_' . $conditionSeqNum);
                    if (empty($typeRef)) {
                        $typeRef = 'condition';
                    }
                    if (empty($conditionId)) {
                        $condition = RuleConditionFactory::buildNewModel($typeRef);
                    } else {
                        $condition = RuleConditionFactory::changeModelType($existingConditions[$conditionId], $typeRef);
                        unset($existingConditions[$conditionId]);
                    }
                    $condition->setFieldSuffix($ruleSeqNum);
                    $condition->setSeqNumber($conditionSeqNum);
                    $condition->setRule($this);
                    if (!$condition->handleFormSubmission($form)) {
                        return false;
                    }
                }
            }
            foreach ($existingConditions as $existingCondition) {
                if (!$existingCondition->softDelete()) {
                    return false;
                }
            }
            return true;
        }
        return false;
    }
    
    public function softDelete() {
        $conditions = $this->getConditions();
        if (!empty($conditions)) {
            foreach ($conditions as $condition) {
                if (!$condition->softDelete()) {
                    return false;
                }
            }
        }
        $ruleActions = $this->getRuleActions();
        if (!empty($ruleActions)) {
            foreach ($ruleActions as $ruleAction) {
                if (!$this->unapplyRuleAction($ruleAction)) {
                    return false;
                }
            }
        }
        return parent::softDelete();
    }
    
    public function evaluate() {
        $result = true;
        $conditions = $this->getConditions();
        if (!empty($conditions)) {
            foreach ($conditions as $condition) {
                if (!$condition->evaluate()) {
                    $result = false;
                }
            }
        }
//        $negate = $this->getProperty('negate_actions');
//        if (!empty($negate)) {
//            if ($result == true) {
//                $result = false;
//            }
//        }
        return $result;
    }
    
    protected function getIsDeleteable() {
        if (Permission::verifyByRef('delete_rules')) {
            return true;
        }
        return false;
    }
    
    public function save() {
        if(empty($this->getProperty('rule_group_id')) && !empty($this->ruleGroup)){
            $this->setProperty('rule_group_id', $this->ruleGroup->getId());
        }
        return parent::save();
    }
    
}
