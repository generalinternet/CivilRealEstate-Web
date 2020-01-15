<?php
/**
 * Description of AbstractRuleConditionMathFormView
 *
 * @author General Internet
 * @copyright  2017 General Internet
 * @version    2.0.0
 */
abstract class AbstractRuleConditionMathFormView extends AbstractRuleConditionFormView {

    protected function addFields() {
        $this->form->addHTML('<div class="flex_col">');
        $this->addLeftPropertyField();
        $this->form->addHTML('</div>');
        
        $this->form->addHTML('<div class="flex_col x_sml">');
        $this->addOperatorField();
        $this->form->addHTML('</div>');
        
        $this->form->addHTML('<div class="flex_col">');
        $this->addRightField();
        $this->form->addHTML('</div>');
    }
    protected function addLeftPropertyField() {
        $propertyOptionsArray = array();
        $ruleGroup = $this->ruleCondition->getRuleGroup();
        if (!empty($ruleGroup)) {
            $propertyOptionsArray = $ruleGroup->getSubjectPropertyOptionsRefAndTitleArray();
        }
        $this->form->addField($this->getFieldName('left_property_ref'), 'dropdown', array(
            'options' => $propertyOptionsArray,
            'displayName' => '&nbsp;',
            'value'=>$this->ruleCondition->getProperty('rule_condition_math.left_property_ref'),
        ));
    }

    protected function addOperatorField() {
        $options = $this->ruleCondition->getOperators();
        $htmlOptions = array();
        foreach($options as $key => $operator){
            $htmlOptions[$key] = '<span class="icon_size_text">' . $operator . '</span>';
        }
        if (!empty($options)) {
            $this->form->addField($this->getFieldName('operator_ref'), 'dropdown', array(
                'displayName' => '&nbsp;',
                'options' => $htmlOptions,
                'htmlOptions' => true,
                'value'=>$this->ruleCondition->getProperty('rule_condition_math.operator_ref'),
            ));
        }
    }
    
    protected function addRightField() {
       //Do Nothing
    }



}
