<?php
/**
 * Description of AbstractLoginStillHereView
 *
 * @author General Internet
 * @copyright  2016 General Internet
 * @version    2.0
 */
class AbstractLoginStillHereView extends GI_View {
    protected $form;

    public function __construct($form) {
        $this->form = $form;
        
        parent::__construct();
        $this->buildForm();
    }

    protected function openViewWrap(){
        $this->form->addHTML('<div class="content_padding">');
        return $this->form;
    }
    
    protected function closeViewWrap(){
        $this->form->addHTML('</div>');
        return $this->form;
    }
    
    public function buildForm() {
        $this->openViewWrap();
        $this->form->addHTML('<h1>Are you still there?</h1>');
        $this->form->addHTML('<p>You will be logged out in 5 minutes.</p>');
        $this->form->addHTML('<span class="submit_btn">I’m still here</span>');
        $this->closeViewWrap();
    }

    public function buildView() {           
        $this->addHTML($this->form->getForm());
    }
    
    public function beforeReturningView() {
        $this->buildView();
    }
    
}
