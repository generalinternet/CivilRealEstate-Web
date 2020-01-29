<?php
/**
 * Description of AbstractContactApplicationFormView
 *
 * @author General Internet
 * @copyright  2019 General Internet
 * @version    4.0.0
 */

abstract class AbstractContactApplicationFormView extends MainWindowView {
    
    protected $form;
    protected $formBuilt = false;
    protected $backButtonURL = NULL;
    /** @var AbstractContactApplication */
    protected $application = NULL;
    
    public function __construct(GI_Form $form, AbstractContactApplication $application) {
        parent::__construct();
        $this->form = $form;
        $this->application = $application;
        $this->addJS('framework/modules/Contact/v4.0/resources/application/contact_application.js');
    }

    public function buildForm(AbstractContactApplicationStatus $status) {
        if (!$this->formBuilt) {
            $this->buildFormByStatus($status);
            $this->formBuilt = true;
        }
    }

    protected function buildFormButtons($backButtonLabel = 'back', $submitButtonLabel = 'next') {
        $this->form->addHTML('<div class="center_btns wrap_btns">');
        $this->addBackButton($backButtonLabel);
        $this->addSubmitButton($submitButtonLabel);
        $this->form->addHTML('</div>');
    }

    public function setBackButtonURL($url) {
        $this->backButtonURL = $url;
    }

    protected function addBackButton($label = 'back') {
        $backURL = $this->backButtonURL;
        if (!empty($backURL)) {
            $this->form->addHTML('<a class="other_btn gray" href="' . $backURL. '">'.$label.'</a>');
        }
    }
    
    protected function addSubmitButton($label = 'next') {
        $this->form->addHTML('<span class="submit_btn">'.$label.'</span>');
    }


    protected function buildFormByStatus(AbstractContactApplicationStatus $status) {
        return true;
    }
    
    protected function addViewBodyContent() {
        //TODO
        $this->addHTML($this->form->getForm(''));
        
        return $this;
    }
    
}