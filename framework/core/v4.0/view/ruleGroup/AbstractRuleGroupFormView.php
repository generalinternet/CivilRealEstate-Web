<?php
/**
 * Description of AbstractRuleGroupFormView
 *
 * @author General Internet
 * @copyright  2017 General Internet
 * @version    2.0.0
 */
abstract class AbstractRuleGroupFormView extends GI_View {
    
    protected $form;
    protected $ruleGroup;
    protected $formBuilt = false;
    
    public function __construct(GI_Form $form, AbstractRuleGroup $ruleGroup) {
        parent::__construct();
        $this->form = $form;
        $this->ruleGroup = $ruleGroup;
    }
    
    public function buildForm() {
        if (!$this->formBuilt) {
            $this->buildFormHeader();
            $this->buildFormBody();
            $this->buildFormFooter();
            $this->formBuilt = true;
        }
    }
    
    protected function buildFormHeader() {
        $this->form->addHTML('<h1>'.$this->ruleGroup->getTypeTitle().' Rules</h1>');
    }
    
    protected function buildFormBody() {
        $this->addRulesSection();
    }

    protected function addRulesSection() {
        $this->form->addHTML('<div class="form_rows_group">');
        $this->form->addHTML('<div id="rules" class="form_rows">');
        $this->addRules();
        $this->form->addHTML('</div>');
        $this->form->addHTML('</div>');
        $this->form->addHTML('<div class="wrap_btns">');
        $this->addAddRuleBtn();
        $this->form->addHTML('</div>');
    }

    protected function addRules() {
        $formWasSubmitted = $this->form->wasSubmitted();
        $seqCount = 0;
        $rules = $this->ruleGroup->getRules($this->form);
        if (!empty($rules)) {
            foreach ($rules as $rule) {
                if (!$formWasSubmitted) {
                    $rule->setSeqNumber($seqCount);
                    $seqCount++;
                }
                $formView = $rule->getFormView($this->form);
                $formView->setFullView(false);
                $formView->buildForm();
            }
        }
    }

    protected function addAddRuleBtn() {
        if (Permission::verifyByRef('add_rules')) {
            $addURL = GI_URLUtils::buildURL(array(
                        'controller' => 'rule',
                        'action' => 'addRule',
                        'gType' => $this->ruleGroup->getTypeRef(),
                            ), false, true);
            $this->form->addHTML('<span class="custom_btn add_form_row" data-add-to="rules" data-add-type="rule" data-add-url="' . $addURL . '"><span class="icon_wrap"><span class="icon plus"></span></span><span class="btn_text">Rule</span></span>');
        }
    }

    protected function buildFormFooter() {
        $this->form->addHTML('<br />');
        $this->addSubmitButton();
    }
    
    protected function addSubmitButton() {
        $this->form->addHTML('<span class="submit_btn">Save</span>');
    }
    
    protected function buildView() {
        $this->openViewWrap();
        $this->buildForm();
        $this->addHTML($this->form->getForm(''));
        $this->closeViewWrap();
    }
    
    protected function openViewWrap() {
        $this->addHTML('<div class="content_padding">');
    }
    
    protected function closeViewWrap() {
        $this->addHTML('</div>');
    }
    
    public function beforeReturningView() {
        $this->buildView();
    }
    
}