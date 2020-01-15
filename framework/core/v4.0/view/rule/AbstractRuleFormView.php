<?php
/**
 * Description of AbstractRuleFormView
 *
 * @author General Internet
 * @copyright  2017 General Internet
 * @version    2.0.0
 */
abstract class AbstractRuleFormView extends GI_FormRowView {

    protected $seqNumFieldName = 'rules';
    protected $modelFieldPrefix = 'rule';

    /** @var AbstractRule */
    protected $rule = NULL;
    protected $addRolesSection = false;
    
    public function __construct(\GI_Form $form, AbstractRule $rule) {
     //   $this->addFormRowClass('give_me_space');
        $this->addFormRowClass('line_form_row');
        parent::__construct($form);
        $this->rule = $rule;
    }

    protected function getModelId() {
        return $this->rule->getProperty('id');
    }

    protected function getModelTypeRef() {
        return $this->rule->getTypeRef();
    }

    public function getFieldName($fieldName) {
        return $this->rule->getFieldName($fieldName);
    }

    public function getFieldSuffix() {
        return $this->rule->getFieldSuffix();
    }

    public function getSeqNumber() {
        return $this->rule->getSeqNumber();
    }
    
    public function setAddRolesSection($addRolesSection){
        $this->addRolesSection = $addRolesSection;
        return $this;
    }

//    public function buildForm() {
//        if (!$this->formBuilt) {
//            $this->openFormRowWrap();
//            $this->addRequiredInfo();
//            $this->addRemoveBtnWrap();
//            $this->addFields();
//            $this->closeFormRowWrap();
//            $this->formBuilt = true;
//        }
//    }
    
    protected function openFormRowWrap() {
        parent::openFormRowWrap();
        $this->form->addHTML('<div class="flex_row">');
    }
    
    protected function closeFormRowWrap() {
        $this->form->addHTML('</div>');
        parent::closeFormRowWrap();
    }
    
    protected function addFields() {
        $this->form->addHTML('<div class="flex_col">');
        $this->addActionSection();
        $this->form->addHTML('</div>');
        
        $this->form->addHTML('<div class="flex_col size_2 no_pad">');
        $this->addConditionsSection();
        $this->form->addHTML('</div>');
        
        if($this->addRolesSection){
            $this->form->addHTML('<div class="flex_col">');
            $this->addAppliesToRoleSection();
            $this->form->addHTML('</div>');
        }
    }

    protected function addActionSection() {
        $this->form->addHTML('<div class="columns thirds">')
                ->addHTML('<div class="column">');
        $this->addNegateActionsField();
        $this->form->addHTML('</div>')
                ->addHTML('<div class="column two_thirds">');
        $this->addActionsField();
        $this->form->addHTML('</div>')
                ->addHTML('</div>');
    }
    
    protected function addNegateActionsField() {
        $this->form->addField($this->getFieldName('negate_actions'), 'onoff', array(
            'displayName'=>'Do Not',
            'required'=>false,
            'value'=>$this->rule->getProperty('negate_actions'),
        ));
    }

    protected function addActionsField() {
        $actionIds = '';
        $actions = $this->rule->getRuleActions();
        if (!empty($actions)) {
            $actionIdsArray = array();
            foreach ($actions as $action) {
                $actionIdsArray[] = $action->getProperty('id');
            }
            $actionIds = implode(',', $actionIdsArray);
        }
        $actionOptions = $this->rule->getRuleActionOptions();
        $this->form->addField($this->getFieldName('action_ids'), 'dropdown', array(
            'displayName' => 'Action(s)',
            'options' => $actionOptions,
            'value' => $actionIds,
            'required' => true,
        ));
    }

    protected function addConditionsSection() {
        $this->form->addHTML('<div class="form_rows_group">');
        $this->form->addHTML('<div id="conditions_'.$this->rule->getSeqNumber().'"  class="form_rows labels_on_first_row">');
        $this->addConditions();
        $this->form->addHTML('</div>');
        $this->form->addHTML('</div>');
        $this->form->addHTML('<div class="wrap_btns">');
        $this->addAddMathPVConditionBtn();
        $this->addAddMathPPConditionBtn();
        $this->form->addHTML('</div>');
    }

    protected function addConditions() {
        $formWasSubmitted = $this->form->wasSubmitted();
        $seqCount = 0;
        $conditions = $this->rule->getConditions($this->form, true, 'math_p_v');
        $ruleSeqNum = $this->rule->getSeqNumber();
        if (!empty($conditions)) {
            foreach ($conditions as $condition) {
                if (!$formWasSubmitted) {
                    $condition->setFieldSuffix($ruleSeqNum);
                    $condition->setSeqNumber($seqCount);
                    $seqCount++;
                }
                $formView = $condition->getFormView($this->form);
                $formView->setFullView(false);
                $formView->buildForm();
            }
        }
    }
    
    protected function addAddMathPVConditionBtn() {
        $addURL = GI_URLUtils::buildURL(array(
                    'controller' => 'rule',
                    'action' => 'addRuleCondition',
                    'ruleSeq' => $this->rule->getSeqNumber(),
                    'rType' => $this->rule->getTypeRef(),
                    'rGroupType' => $this->rule->getRuleGroup()->getTypeRef(),
                        ), false, true);
        $this->form->addHTML('<span class="custom_btn add_form_row" data-add-to="conditions_' . $this->rule->getSeqNumber() . '" data-add-type="math_p_v" data-add-url="' . $addURL . '"><span class="icon_wrap"><span class="icon plus"></span></span><span class="btn_text">Math PV</span></span>');
    }

    protected function addAddMathPPConditionBtn() {
        $addURL = GI_URLUtils::buildURL(array(
                    'controller' => 'rule',
                    'action' => 'addRuleCondition',
                    'ruleSeq' => $this->rule->getSeqNumber(),
                    'rType' => $this->rule->getTypeRef(),
                    'rGroupType' => $this->rule->getRuleGroup()->getTypeRef(),
                        ), false, true);
        $this->form->addHTML('<span class="custom_btn add_form_row" data-add-to="conditions_' . $this->rule->getSeqNumber() . '" data-add-type="math_p_p" data-add-url="' . $addURL . '"><span class="icon_wrap"><span class="icon plus"></span></span><span class="btn_text">Math PP</span></span>');
    }

    protected function addAppliesToRoleSection() {
        $this->form->addHTML('Roles');
    }

//    protected function addSubmitButton() {
//        $this->form->addHTML('<span class="submit_btn">Submit</span>');
//    }
    protected function addRemoveBtn() {
        if ($this->rule->isDeleteable()) {
            $this->form->addHTML('<span class="custom_btn remove_form_row"><span class="icon_wrap border circle"><span class="icon trash primary"></span></span></span>');
        }
    }

    protected function addOpenPadding() {
        $this->addHTML('<div class="content_padding">');
    }

    protected function addClosePadding() {
        $this->addHTML('</div>');
    }

    public function buildView() {
        if ($this->fullView) {
            $this->addOpenPadding();
            $this->addViewHeader();
            $this->addHTML($this->form->getForm());
            $this->addClosePadding();
        } else {
            $this->form->setBtnText('');
            $this->addHTML($this->form->getForm(NULL, false));
        }
    }

    public function beforeReturningView() {
        $this->buildView();
    }

}
