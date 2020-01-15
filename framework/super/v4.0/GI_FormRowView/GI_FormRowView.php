<?php
/**
 * Description of GI_FormRowView
 *
 * @author General Internet
 * @copyright  2017 General Internet
 * @version    3.0.4
 */
abstract class GI_FormRowView extends GI_View {
    
    /**
     * @var GI_Form
     */
    protected $form;
    protected $formBuilt = false;
    protected $fullView = true;
    protected $seqNumFieldName = 'seq_num';
    protected $modelFieldPrefix = 'row';
    protected $formRowClass = array();
    protected $rightRemoveBtn = false;
    protected $addHiddenTypeField = true;
    
    public function __construct(GI_Form $form) {
        parent::__construct();
        $this->form = $form;
    }
    
    protected function addSeqNumField(){
        $seqNumber = $this->forceGetSeqNumber();
        $this->form->addHTML('<input name="' . $this->seqNumFieldName . '[]" value="' . $seqNumber . '" type="hidden" class="seq_count"/>');
    }
    
    public function setRightRemoveBtn($rightRemoveBtn){
        $this->rightRemoveBtn = $rightRemoveBtn;
        return $this;
    }
    
    protected function getModelId(){
        return NULL;
    }
    
    protected function getModelTypeRef(){
        return NULL;
    }
    
    public function addFormRowClass($class) {
        if (!in_array($class, $this->formRowClass)) {
            array_push($this->formRowClass, $class);
        }
    }
    
    public function setSeqNumFieldName($seqNumFieldName){
        $this->seqNumFieldName = $seqNumFieldName;
        return $this;
    }
    
    public function getSeqNumFieldName(){
        return $this->seqNumFieldName;
    }
    
    public function setModelFieldPrefix($modelFieldPrefix){
        $this->modelFieldPrefix = $modelFieldPrefix;
        return $this;
    }
    
    public function getModelFieldPrefix(){
        return $this->modelFieldPrefix;
    }
    
    public function getFormRowClass(){
        return implode(' ', $this->formRowClass);
    }
    
    public function getModelFieldName($fieldName){
        if(!is_null($this->modelFieldPrefix)){
            $fieldName = $this->modelFieldPrefix . '_' . $fieldName;
        }
        $fieldSuffix = $this->getFieldSuffix();
        if(!is_null($fieldSuffix)){
            $fieldName .= '_' . $fieldSuffix;
        }
        $seqNumber = $this->getSeqNumber();
        if(!is_null($seqNumber)){
            $fieldName .= '_' . $seqNumber;
        }
        return $fieldName;
    }
    
    protected function getModelIdFieldClass(){
        return '';
    }
    
    protected function addHiddenModelFields(){
        $this->form->addField($this->getModelFieldName('id'), 'hidden', array(
            'value' => $this->getModelId(),
            'fieldClass' => $this->getModelIdFieldClass()
        ));
        if($this->addHiddenTypeField){
            $this->form->addField($this->getModelFieldName('type'), 'hidden', array(
                'value' => $this->getModelTypeRef()
            ));
        }
    }
    
    /**
     * @param boolean $addHiddenTypeField
     * @return \GI_FormRowView
     */
    protected function setAddHiddenTypeField($addHiddenTypeField){
        $this->addHiddenTypeField = $addHiddenTypeField;
        return $this;
    }

    protected function addRequiredInfo(){
        $this->addSeqNumField();
        $this->addHiddenModelFields();
    }
    
    protected function addRemoveBtnWrap(){
        $this->form->addHTML('<div class="remove_form_row_wrap center_align">');
            $this->addRemoveBtn();
        $this->form->addHTML('</div>');
    }
    
    protected function addRemoveBtn(){
        $this->form->addHTML('<span class="custom_btn remove_form_row"><span class="icon_wrap"><span class="icon primary remove"></span></span></span>');
    }
    
    protected function addFields(){
        
    }
    
    public function getFieldSuffix(){
        return NULL;
    }

    public function getFieldName($fieldName) {
        return $fieldName;
    }

    public function getSeqNumber() {
        return NULL;
    }

    public function forceGetSeqNumber(){
        $seqNumber = $this->getSeqNumber();
        if(is_null($seqNumber)){
            $seqNumber = $this->getFieldSuffix();
        }
        return $seqNumber;
    }
    
    protected function openFormRowWrap(){
        $seqNumber = $this->forceGetSeqNumber();
        $this->form->addHTML('<div class="form_row ' . $this->getFormRowClass() . '" data-seq-number="' . $seqNumber . '">');
    }
    
    protected function closeFormRowWrap(){
        $this->form->addHTML('</div>');
    }
    
    public function buildForm() {
        $this->openFormRowWrap();
            $this->addRequiredInfo();
            if(!$this->rightRemoveBtn){
                $this->addRemoveBtnWrap();
            }
            $this->beforeFieldsAdded();
            $this->addFields();
            $this->afterFieldsAdded();
            if($this->rightRemoveBtn){
                $this->addRemoveBtnWrap();
            }
        $this->closeFormRowWrap();
    }
    
    protected function beforeFieldsAdded(){
        return NULL;
    }
    
    protected function afterFieldsAdded(){
        return NULL;
    }

    public function buildView() {
        if ($this->fullView) {
            $this->addHTML($this->form->getForm());
        } else {
            $this->form->setBtnText('');
            $this->addHTML($this->form->getForm('', false));
        }
    }
    
    public function setFullView($fullView){
        $this->fullView = $fullView;
    }

    public function beforeReturningView() {
        $this->buildView();
    }
    
}
