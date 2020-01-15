<?php
/**
 * Description of AbstractRuleConditionDetailView
 *
 * @author General Internet
 * @copyright  2017 General Internet
 * @version    2.0.0
 */

abstract class AbstractRuleConditionDetailView extends GI_View {
    
    protected $ruleCondition;
    
    public function __construct(AbstractRuleCondition $ruleCondition) {
        parent::__construct();
        $this->ruleCondition = $ruleCondition;
    }
    
    protected function buildView() {
        $this->openViewWrap();
        $this->buildViewHeader();
        $this->buildViewBody();
        $this->buildViewFooter();
        $this->closeViewWrap();
    }
    
    protected function openViewWrap() {
        $this->addHTML('<div class="content_padding">');
    }
    
    protected function closeViewWrap() {
        $this->addHTML('</div>');
    }
    
    protected function buildViewHeader() {
        
    }
    
    protected function buildViewBody() {
        $this->addHTML('CONDITION '.$this->ruleCondition->getProperty('id') . '<br>'); //TODO - temp
    }
    
    protected function buildViewFooter() {
        
    }
    
    public function beforeReturningView() {
        $this->buildView();
    }
    
}