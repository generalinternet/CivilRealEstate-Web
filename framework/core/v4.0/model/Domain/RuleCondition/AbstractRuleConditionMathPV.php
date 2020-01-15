<?php
/**
 * Description of AbstractRuleConditionMathPV
 *
 * @author General Internet
 * @copyright  2016 General Internet
 * @version    2.0.0
 */
abstract class AbstractRuleConditionMathPV extends AbstractRuleConditionMath {
    
    public function getFormView(\GI_Form $form) {
        return new RuleConditionMathPVFormView($form, $this);
    }

    protected function handleFormFields(GI_Form $form) {
        if ($form->wasSubmitted() && $this->validateForm($form)) {
            $rightValue = filter_input(INPUT_POST, $this->getFieldName('right_val'));
            $this->setProperty('rule_condition_math_p_v.right_value', $rightValue);
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
        $rightValue = $this->getProperty('rule_condition_math_p_v.right_value');
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
        $rightValue = $this->getProperty('rule_condition_math_p_v.right_value');
        $string .= ' $' . GI_StringUtils::formatMoney($rightValue, true); //TODO - format as money for now. May require subtype in the future
        return $string;
    }

}
