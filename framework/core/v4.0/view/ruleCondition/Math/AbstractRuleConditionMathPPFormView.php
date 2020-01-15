<?php
/**
 * Description of AbstractRuleConditionMathPPFormView
 *
 * @author General Internet
 * @copyright  2017 General Internet
 * @version    2.0.0
 */

abstract class AbstractRuleConditionMathPPFormView extends AbstractRuleConditionMathFormView {

    protected function addRightField() {
        $this->addRightPropertyField();
    }

    protected function addRightPropertyField() {
        $propertyOptionsArray = array();
        $ruleGroup = $this->ruleCondition->getRuleGroup();
        if (!empty($ruleGroup)) {
            $propertyOptionsArray = $ruleGroup->getSubjectPropertyOptionsRefAndTitleArray();
        }
        $this->form->addField($this->getFieldName('right_property_ref'), 'dropdown', array(
            'displayName' => '&nbsp;',
            'value' => $this->ruleCondition->getProperty('rule_condition_math_p_p.right_property_ref'),
            'options'=>$propertyOptionsArray,
        ));
    }

}
