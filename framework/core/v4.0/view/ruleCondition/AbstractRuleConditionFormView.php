<?php
/**
 * Description of AbstractRuleConditionFormView
 *
 * @author General Internet
 * @copyright  2017 General Internet
 * @version    2.0.0
 */
abstract class AbstractRuleConditionFormView extends GI_FormRowView {

    protected $seqNumFieldName = 'conditions';
    protected $modelFieldPrefix = 'condition';
    /** @var AbstractRuleCondition */
    protected $ruleCondition;
    protected $rightRemoveBtn = true;

    public function __construct(GI_Form $form, AbstractRuleCondition $ruleCondition) {
        parent::__construct($form);
        $this->ruleCondition = $ruleCondition;
    }

    protected function getModelId() {
        return $this->ruleCondition->getProperty('id');
    }

    protected function getModelTypeRef() {
        return $this->ruleCondition->getTypeRef();
    }

    public function getFieldName($fieldName) {
        return $this->ruleCondition->getFieldName($fieldName);
    }

    public function getFieldSuffix() {
        return $this->ruleCondition->getFieldSuffix();
    }

    public function getSeqNumber() {
        return $this->ruleCondition->getSeqNumber();
    }

    protected function addSeqNumField() {
        $seqNumber = $this->forceGetSeqNumber();
        $this->form->addHTML('<input name="' . $this->seqNumFieldName . '_' . $this->ruleCondition->getFieldSuffix() . '[]" value="' . $seqNumber . '" type="hidden" class="seq_count"/>');
    }
    
    protected function openFormRowWrap() {
        parent::openFormRowWrap();
        $this->form->addHTML('<div class="flex_row">');
    }
    
    protected function closeFormRowWrap() {
        $this->form->addHTML('</div>');
        parent::closeFormRowWrap();
    }
    
    protected function addRemoveBtn() {
        if ($this->ruleCondition->isDeleteable()) {
            $this->form->addHTML('<span class="custom_btn remove_form_row"><span class="icon_wrap border circle"><span class="icon trash primary"></span></span></span>');
        }
    }

    protected function addFields() {
        //DO Nothing
    }

}
