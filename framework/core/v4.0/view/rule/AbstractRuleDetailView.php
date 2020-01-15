<?php
/**
 * Description of AbstractRuleDetailView
 *
 * @author General Internet
 * @copyright  2017 General Internet
 * @version    2.0.0
 */
abstract class AbstractRuleDetailView extends GI_View {
    
    protected $rule;
    
    public function __construct(AbstractRule $rule) {
        parent::__construct();
        $this->rule = $rule;
    }
    
    protected function buildView() {
        $this->addHTML($this->rule->getProperty('id') . ' ' . $this->rule->getTypeTitle()); //TODO - temp
    }
    
    public function beforeReturningView() {
        $this->buildView();
    }
    
}