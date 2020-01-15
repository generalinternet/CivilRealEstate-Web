<?php
/**
 * Description of AbstractRuleGroupPreAddFormView
 *
 * @author General Internet
 * @copyright  2017 General Internet
 * @version    2.0.0
 */

abstract class AbstractRuleGroupPreAddFormView extends GI_View {
    
    protected $form;
    protected $typeOptions = array();
    protected $formBuilt = false;
    
    public function __construct(GI_Form $form, $typeOptions) {
        parent::__construct();
        $this->form = $form;
        $this->typeOptions = $typeOptions;
    }
    
    public function buildForm() {
        if (!$this->formBuilt) {
            $this->form->addHTML('<h1>Add Rule Group</h1>');
            $this->addTypeField();
            $this->addSubmitButton();
            $this->formBuilt = true;
        }
    }
    
    protected function addTypeField() {
        $this->form->addField('rule_group_type_ref', 'dropdown', array(
            'displayName'=>'Type',
            'options'=>$this->typeOptions,
            'required'=>true,
            'fieldClass'=>'autofocus_off',
            'autoFocus'=>false,
        ));
    }
    
    protected function addSubmitButton() {
        $this->form->addHTML('<span class="submit_btn">Submit</span>');
    }
    
    public function buildView() {
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