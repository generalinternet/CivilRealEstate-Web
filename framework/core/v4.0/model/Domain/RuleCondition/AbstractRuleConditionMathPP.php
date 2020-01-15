<?php
/**
 * Description of AbstractRuleConditionMathPP
 *
 * @author General Internet
 * @copyright  2016 General Internet
 * @version    2.0.0
 */
abstract class AbstractRuleConditionMathPP extends AbstractRuleConditionMath {
    
    public function getFormView(\GI_Form $form) {
        return new RuleConditionMathPPFormView($form, $this);
    }

    protected function handleFormFields(GI_Form $form) {
        if ($form->wasSubmitted() && $this->validateForm($form)) {
            $rightPropertyRef = filter_input(INPUT_POST, $this->getFieldName('right_property_ref'));
            $this->setProperty('rule_condition_math_p_p.right_property_ref', $rightPropertyRef);
            return parent::handleFormFields($form);
        }
        return false;
    }

    public function evaluate() {
        $rule = $this->getRule();
        if (empty($rule)) {
            return false;
        }
        $ruleGroup = $this->getRuleGroup();
        if (empty($ruleGroup)) {
            return false;
        }
        $leftPropertyRef = $this->getProperty('rule_condition_math.left_property_ref');
        $leftValue = $ruleGroup->getSubjectPropertyValue($leftPropertyRef);
        $operatorRef = $this->getProperty('rule_condition_math.operator_ref');
        $rightPropertyRef = $this->getProperty('rule_condition_math_p_p.right_property_ref');
        $rightValue = $ruleGroup->getSubjectPropertyValue($rightPropertyRef);
        $result = $this->evaluateExpressions($leftValue, $operatorRef, $rightValue);
        $this->result = $result;
        return $this->result;
    }

    public function getSummaryString() {
        $ruleGroup = $this->getRuleGroup();
        if (empty($ruleGroup)) {
            return '';
        }
        $leftPropertyTitle = '';
        $leftPropertyOptionArray = $ruleGroup->getSubjectPropertyOption($this->getProperty('rule_condition_math.left_property_ref'));
        if (isset($leftPropertyOptionArray['title'])) {
            $leftPropertyTitle = $leftPropertyOptionArray['title'];
        }
        $string = $leftPropertyTitle;
        $operatorRef = $this->getProperty('rule_condition_math.operator_ref');
        $operator = $this->getOperator($operatorRef);
        $string .= ' ' . $operator;
        $rightPropertyTitle = '';
        $rightPropertyOptionArray = $ruleGroup->getSubjectPropertyOption($this->getProperty('rule_condition_math_p_p.right_property_ref'));
        if (isset($rightPropertyOptionArray['title'])) {
            $rightPropertyTitle = $rightPropertyOptionArray['title'];
        }
        $string .= ' ' . $rightPropertyTitle;
        return $string;
    }

}
