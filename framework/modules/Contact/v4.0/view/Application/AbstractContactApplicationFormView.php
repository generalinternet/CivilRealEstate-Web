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
        $this->addJS('framework/modules/Contact/' . MODULE_CONTACT_VER . '/resources/application/contact_application.js');
        $this->setViewWrapClass('profile_form');
        $this->addCSS('https://cdnjs.cloudflare.com/ajax/libs/paymentfont/1.1.2/css/paymentfont.min.css');
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
        $preIcon = GI_StringUtils::getSVGIcon('bird_beak_left');
        if (!empty($backURL)) {
            $this->form->addHTML('<a class="other_btn gray" href="' . $backURL. '" title="'.ucwords($label).'">' . $preIcon . '<span class="btn_text">'.ucwords($label).'</span></a>');
        }
    }
    
    protected function addSubmitButton($label = 'next') {
        $postIcon = GI_StringUtils::getSVGIcon('bird_beak_right');
//        if($label == 'Next'){
//            $postIcon = NULL;
//        }
        $this->form->addHTML('<span class="submit_btn"><span class="btn_text">'.ucwords($label).'</span>' . $postIcon . '</span>');
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