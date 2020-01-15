<?php
/**
 * Description of AbstractRuleGroupResultSummaryView
 *
 * @author General Internet
 * @copyright  2017 General Internet
 * @version    2.0.0
 */

abstract class AbstractRuleGroupResultSummaryView extends GI_View {
    
    protected $ruleGroup;
    protected $rules = NULL;
    
    /**
     * @param AbstractRuleGroup $ruleGroup
     * @param AbstractRule[] $rules
     */
    public function __construct(AbstractRuleGroup $ruleGroup, $rules) {
        parent::__construct();
        $this->ruleGroup = $ruleGroup;
        $this->rules = $rules;
    }
    
    protected function buildView() {
        $this->buildResultsTable();
    }
    
    protected function buildResultsTable() {
        $rules = $this->rules;
        $conditions = array();
        if (!empty($rules)) {
            foreach ($rules as $rule) {
                $ruleConditions = $rule->getConditions();
                if (!empty($ruleConditions)) {
                    $conditions = array_merge($conditions, $ruleConditions);
                }
            }
        }
        if (!empty($conditions)) {
            $this->addHTML('<div class="flex_table">')
                    ->addHTML('<div class="flex_row flex_head">');
            $this->addHTML('<div class="flex_col sml">Result</div>');
            $this->addHTML('<div class="flex_col">Condition</div>');
            $this->addHTML('</div>');
            foreach ($conditions as $cond) {
                $result = $cond->getResult();
                if ($result == false) {
                    $resultString = '<span class="icon_wrap"><span class="icon black remove"></span></span>';
                } else {
                    $resultString = '<span class="icon_wrap"><span class="icon black check"></span></span>';
                }
                $this->addHTML('<div class="flex_row">');
                $this->addHTML('<div class="flex_col sml">')
                        ->addHTML($resultString)
                        ->addHTML('</div>');
                $this->addHTML('<div class="flex_col">')
                        ->addHTML($cond->getSummaryString())
                        ->addHTML('</div>');
                $this->addHTML('</div>');
            }
            $this->addHTML('</div>');
        }
    }
    
    public function beforeReturningView() {
        $this->buildView();
    }
}