<?php

abstract class AbstractCancelFormView extends GI_View {
    
    protected $form;
    protected $model;
    protected $cancelError = false;

    public function __construct($form, GI_Model $model) {
        parent::__construct();
        $this->form = $form;
        $this->model = $model;
    }

    protected function buildForm() {
        if ($this->cancelError){
            $this->form->addHTML('<div class="center_btns wrap_btns"><span class="other_btn gray close_gi_modal" >Cancel</span></div>');
        } else {
            $this->form->addHTML('<p>Are you sure you want to cancel <b>'.$this->model->getSpecificTitle().'</b>?</p>');
            $this->form->addHTML('<div class="center_btns wrap_btns"><span class="submit_btn" >Cancel</span><span class="other_btn gray close_gi_modal" >Do Not Cancel</span></div>');
        }
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
        $this->addHTML('<h1>Cancel '.$this->model->getSpecificTitle().'</h1>');
        $formHTML = $this->form->getForm();
        $this->addHTML($formHTML);
        $this->closeViewWrap();
    }
    
    public function beforeReturningView() {
        $this->buildView();
    }
    
    public function setCancelError($error){
        $this->cancelError = true;
        $this->form->addHTML('<p class="error">'.$error.'</p>');
    }

}
