<?php
/**
 * Description of AbstractLoginStillHereView
 *
 * @author General Internet
 * @copyright  2019 General Internet
 * @version    4.0.0
 */
class AbstractLoginStillHereView extends MainWindowView {
    protected $form;

    public function __construct($form) {
        $this->form = $form;
        
        parent::__construct();
        $this->buildForm();
        $this->addSiteTitle(Lang::getString('are_you_still_there'));
        $this->setWindowTitle(Lang::getString('are_you_still_there'));
    }
    
    public function buildForm() {
        $this->form->addHTML('<p>You will be logged out in 5 minutes.</p>');
        $this->form->addHTML('<span class="submit_btn" tabindex="0">I’m still here</span>');
    }
    
    protected function addViewBodyContent(){
        $this->addHTML($this->form->getForm());
    }
    
}
