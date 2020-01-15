<?php
/**
 * Description of AbstractRuleCondition
 *
 * @author General Internet
 * @copyright  2016 General Internet
 * @version    2.0.0
 */
abstract class AbstractRuleCondition extends GI_FormRowableModel {
    
    protected $rule = NULL;
    protected $ruleGroup = NULL;
    protected $result = NULL;
    
    public function getRule() {
        if (empty($this->rule)) {
            $this->rule = RuleFactory::getModelById($this->getProperty('rule_id'));
        }
        return $this->rule;
    }
    
    public function getRuleGroup() {
        if (empty($this->ruleGroup)) {
            $rule = $this->getRule();
            if (!empty($rule)) {
                $this->ruleGroup = $rule->getRuleGroup();
            }
        }
        return $this->ruleGroup;
    }
    
    public function getFormView(GI_Form $form) {
        return new RuleConditionFormView($form, $this);
    }
    
    public function getDetailView() {
        return new RuleConditionDetailView($this);
    }
    
    public function setRule(AbstractRule $rule) {
        $this->rule = $rule;
    }
    
    public function setRuleGroup(AbstractRuleGroup $ruleGroup) {
        $this->ruleGroup = $ruleGroup;
    }
    
    public function getResult() {
        return $this->result;
    }

    public function validateForm(\GI_Form $form) {
        if (!$this->formValidated && $form->wasSubmitted() && $form->validate()) {
            //TODO - validate fields..
            $this->formValidated = true;
        }
        return $this->formValidated;
    }
    
    public function handleFormSubmission(GI_Form $form) {
        if ($form->wasSubmitted() && $this->validateForm($form)) {
            $rule = $this->getRule();
            if (empty($rule)) {
                return false;
            }
            $this->setProperty('rule_id', $rule->getProperty('id'));
            if (!$this->handleFormFields($form)) {
                return false;
            }
            if (!$this->save()) {
                return false;
            }
            return true;
        }
        return false;
    }
    
    protected function handleFormFields(GI_Form $form) {
        if ($form->wasSubmitted() && $this->validateForm($form)) {
            
            return true;
        }
        return false;
    }
    
    
    public function evaluate() {
        $this->result = true;
        return $this->result;
    }
    
    public function getSummaryString() {
        return '';
    }
    
    public function getResultSummaryView() {
        
    }
}
    