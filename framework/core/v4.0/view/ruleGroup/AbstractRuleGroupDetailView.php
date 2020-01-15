<?php
/**
 * Description of AbstractRuleGroupDetailView
 *
 * @author General Internet
 * @copyright  2017 General Internet
 * @version    2.0.0
 */
abstract class AbstractRuleGroupDetailView extends GI_View {
    
    protected $ruleGroup;
    
    public function __construct(AbstractRuleGroup $ruleGroup) {
        parent::__construct();
        $this->ruleGroup = $ruleGroup;
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
        $this->addHTML('<div class="right_btns">');
        $this->addEditButton();
        $this->addHTML('</div>');
        $this->addHTML('<h1>' . $this->ruleGroup->getTypeTitle() . ' Rules</h1>');
    }

    protected function addEditButton() {
        if ($this->ruleGroup->isEditable()) {
            $editURL = $this->ruleGroup->getEditURL();
            $this->addHTML('<a href="' . $editURL . '" title="Edit Rule Group" class="custom_btn" ><span class="icon_wrap"><span class="icon primary pencil"></span></span><span class="btn_text">Edit</span></a>');
        }
    }

    protected function buildViewBody() {
        $this->buildRulesSection();
    }
    
    protected function buildRulesSection() {
        $rules = $this->ruleGroup->getRules();
        if (!empty($rules)) {
            foreach ($rules as $rule) {
                $ruleDetailView = $rule->getDetailView();
                $this->addHTML($ruleDetailView->getHTMLView());
            }
        } else {
            $this->addHTML('<p>No rules found.</p>');
        }
    }
    
    protected function buildViewFooter() {
        
    }
    
    public function beforeReturningView() {
        $this->buildView();
    }
    
}