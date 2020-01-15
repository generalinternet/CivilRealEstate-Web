<?php
/**
 * Description of AbstractColourFormView
 *
 * @author General Internet
 * @copyright  2018 General Internet
 * @version    2.0.0
 */
abstract class AbstractColourFormView extends GI_View {
    
    /** GI_Form */
    protected $form;
    /** GI_Model */
    protected $model;
    protected $message = '';

    public function __construct(GI_Form $form, GI_Model $model) {
        parent::__construct();
        $this->form = $form;
        $this->model = $model;
    }

    public function buildForm() {
        $this->form->addHTML('<p>' . $this->getMessage() . '</p>');
        $this->addColourPicker();
        $this->addBtns();
    }
    
    public function setMessage($message){
        $this->message = $message;
        return true;
    }
    
    protected function addColourPicker($overWriteSettings = array()){
        $fieldSettings = GI_Form::overWriteSettings(array(
            'displayName' => 'Colour',
            'value' => $this->model->getColour(),
            'required' => true,
            'fieldClass' => 'autofocus_off',
        ), $overWriteSettings);
        
        $this->form->addField('colour', 'colour', $fieldSettings);
    }
    
    protected function addBtns(){
        $this->form->addHTML('<div class="center_btns wrap_btns">');
        $this->addSubmitBtn();
        $this->addCancelBtn();
        $this->form->addHTML('</div>');
    }
    
    protected function addSubmitBtn(){
        $this->form->addHTML('<span class="submit_btn" >Save</span>');
    }
    
    protected function addCancelBtn(){
        $this->form->addHTML('<span class="other_btn gray close_gi_modal" >Cancel</span>');
    }
    
    public function getMessage(){
        if(empty($this->message)){
            $this->message = 'Change the colour of <b>' . $this->model->getSpecificTitle() . '</b>.';
        }
        return $this->message;
    }

    protected function openViewWrap(){
        $this->addHTML('<div class="content_padding">');
        return $this;
    }
    
    protected function closeViewWrap(){
        $this->addHTML('</div>');
        return $this;
    }
    
    public function buildView() {
        $this->openViewWrap();
        $this->addMainTitle('Colour Picker');
        $formHTML = $this->form->getForm();
        $this->addHTML($formHTML);
        $this->closeViewWrap();
    }
    
    public function beforeReturningView() {
        $this->buildView();
    }

}
