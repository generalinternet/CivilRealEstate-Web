<?php
/**
 * Description of AbstractDeleteFormView
 *
 * @author General Internet
 * @copyright  2018 General Internet
 * @version    2.0.0
 */
abstract class AbstractDeleteFormView extends GI_View {
    
    /** @var GI_Form */
    protected $form;
    /** @var GI_Model */
    protected $model;
    protected $deleteError = false;
    protected $message = '';

    public function __construct(GI_Form $form, GI_Model $model) {
        parent::__construct();
        $this->form = $form;
        $this->model = $model;
    }

    public function buildForm() {
        if ($this->deleteError){
            $this->addErrorSection();
        } else {
            $this->addMessage();
            $this->addFields();
            $this->addBtns();
        }
    }
    
    protected function addErrorSection(){
        $this->form->addHTML('<div class="center_btns wrap_btns"><span class="other_btn gray close_gi_modal" >Cancel</span></div>');
    }
    
    protected function addMessage(){
        $this->form->addHTML('<p>' . $this->getMessage() . '</p>');
    }
    
    protected function addFields(){
        
    }
    
    protected function addBtns(){
        $this->form->addHTML('<div class="center_btns wrap_btns">');
        $this->addSubmitBtn();
        $this->addCancelBtn();
        $this->form->addHTML('</div>');
    }
    
    protected function addSubmitBtn(){
        $this->form->addHTML('<span class="submit_btn" title="Delete" tabindex="0" >Delete</span>');
    }
    
    protected function addCancelBtn(){
        $this->form->addHTML('<span class="other_btn gray close_gi_modal" title="Cancel" >Cancel</span>');
    }
    
    public function setMessage($message){
        $this->message = $message;
        return true;
    }
    
    public function getMessage(){
        if(empty($this->message)){
            $this->message = 'Are you sure you want to delete <b>' . $this->model->getSpecificTitle() . '</b>?';
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
        $this->addHTML('<h1>Delete '.$this->model->getSpecificTitle().'</h1>');
        $formHTML = $this->form->getForm();
        $this->addHTML($formHTML);
        $this->closeViewWrap();
    }
    
    public function beforeReturningView() {
        $this->buildView();
    }
    
    public function setDeleteError($error){
        $this->deleteError = true;
        $this->form->addHTML('<p class="error">'.$error.'</p>');
    }

}
