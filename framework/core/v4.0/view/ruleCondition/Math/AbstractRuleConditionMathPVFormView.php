<?php
/**
 * Description of AbstractRuleConditionMathPVFormView
 *
 * @author General Internet
 * @copyright  2017 General Internet
 * @version    2.0.0
 */

abstract class AbstractRuleConditionMathPVFormView extends AbstractRuleConditionMathFormView {

    protected function addRightField() {
        $this->addRightValueField();
    }

    protected function addRightValueField() {
        $this->form->addField($this->getFieldName('right_val'), 'decimal', array(
            'displayName' => '&nbsp;',
            'value' => $this->ruleCondition->getProperty('rule_condition_math_p_v.right_value'),
        ));
    }

}
