<?php
/**
 * Description of AbstractRuleConditionMath
 *
 * @author General Internet
 * @copyright  2016 General Internet
 * @version    2.0.0
 */
abstract class AbstractRuleConditionMath extends AbstractRuleCondition {
    
    protected static $operators = array(
        'less_than' => '&lt;',
        'less_than_or_equal_to' => '&le;',
        'equal_to' => '=',
        'not_equal_to' => '&ne;',
        'greater_than_or_equal_to' => '&ge;',
        'greater_than' => '&gt;',
    );
    
    public static function getOperators() {
        return static::$operators;
    }
    
    public static function getOperator($ref) {
        return static::$operators[$ref];
    }
    
    public function getFormView(\GI_Form $form) {
        return new RuleConditionMathFormView($form, $this);
    }

    protected function handleFormFields(GI_Form $form) {
        if ($form->wasSubmitted() && $this->validateForm($form)) {
            $leftPropertyRef = filter_input(INPUT_POST, $this->getFieldName('left_property_ref'));
            $this->setProperty('rule_condition_math.left_property_ref', $leftPropertyRef);
            $operatorRef = filter_input(INPUT_POST, $this->getFieldName('operator_ref'));
            $this->setProperty('rule_condition_math.operator_ref', $operatorRef);
//            $rightValue = filter_input(INPUT_POST, $this->getFieldName('right_val'));
//            $this->setProperty('rule_condition_math.right_value', $rightValue);
            return true;
        }
        return false;
    }

    public function evaluate() {
        return false;
    }
    
    /**
     * @param float[] $array
     */
    protected function sumArray($array) {
        if (!is_array($array)) {
            return $array;
        }
        if (empty($array)) {
            return NULL;
        }
        if (count($array) == 1) {
            return array_values($array)[0];
        }
        $sum = 0;
        foreach ($array as $key=>$value) {
            $sum += $value;
        }
        return $sum;
    }
    
    protected function evaluateExpressions($leftValue, $operatorRef, $rightValue) {
        if (is_array($leftValue)  && is_array($rightValue)) {
            if (count($leftValue) != count($rightValue)) {
                return false;
            }
            $result = true;

            foreach ($leftValue as $key => $leftVal) {
                if (!isset($rightValue[$key])) {
                    return false;
                }
                $rightVal = $rightValue[$key];
                if (!$this->evaluateExpression($leftVal, $operatorRef, $rightVal)) {
                    $result = false;
                    break;
                }
            }
            return $result;
        } else if (is_array($leftValue) && !is_array($rightValue)) {
            $leftValue = $this->sumArray($leftValue);
        } else if (!is_array($leftValue) && is_array($rightValue)) {
            $rightValue = $this->sumArray($rightValue);
        } 
        return $this->evaluateExpression($leftValue, $operatorRef, $rightValue);
    }
    
    protected function evaluateExpression($leftValue, $operatorRef, $rightValue) {
        $leftValue = (float) $leftValue;
        $rightValue = (float) $rightValue;
        if (!(($operatorRef == 'less_than_or_equal_to') || ($operatorRef == 'equal_to') || ($operatorRef == 'greater_than_or_equal_to')) && GI_Math::floatEquals($leftValue, $rightValue)) {
            return false;
        }
        switch ($operatorRef) {
            case 'less_than':
                if ($leftValue < $rightValue) {
                    return true;
                }
                break;
            case 'less_than_or_equal_to':
                if ($leftValue <= $rightValue) {
                    return true;
                }
                break;
            case 'equal_to':
                if (GI_Math::floatEquals($leftValue, $rightValue)) {
                    return true;
                }
                break;
            case 'not_equal_to':
                if (!GI_Math::floatEquals($leftValue, $rightValue)) {
                    return true;
                }
                break;
            case 'greater_than_or_equal_to':
                if ($leftValue >= $rightValue) {
                    return true;
                }
                break;
            case 'greater_than':
                if ($leftValue > $rightValue) {
                    return true;
                }
                break;
        }
        return false;
    }
    
    public function getSummaryString() {
        return '';
    }

}
