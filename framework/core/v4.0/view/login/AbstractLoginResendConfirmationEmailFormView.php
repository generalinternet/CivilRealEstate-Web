<?php
/**
 * Description of AbstractLoginResendConfirmationEmailFormView
 *
 * @author General Internet
 * @copyright  2016 General Internet
 * @version    2.0.0
 */
abstract class AbstractLoginResendConfirmationEmailFormView extends MainWindowView {

    protected $form;
    protected $formBuilt = false;
    protected $message = '';
    protected $ajax = false;
    protected $addWrapper = false;

    public function __construct(GI_Form $form) {
        parent::__construct();
        $this->form = $form;
        $this->buildForm();
        $this->addSiteTitle(Lang::getString('resend_email_confirmation'));
        $this->setWindowTitle(Lang::getString('resend_email_confirmation'));
    }
    
    public function setAddWrapper($addWrapper){
        $this->addWrapper = $addWrapper;
        return $this;
    }
    
    public function setAjax($ajax){
        $this->ajax = $ajax;
        return $this;
    }
    
    protected function addViewBodyContent(){
        $this->addHTML($this->form->getForm());
    }

    public function buildForm() {
        if (!$this->formBuilt) {
            $this->form->addHTML('<p>Enter your email so we can send you your confirmation email.</p>');
            $this->addEmailAddressField();
            $this->addSubmitButton();
            $this->formBuilt = true;
        }
    }
    
    protected function addMessage() {
        $this->form->addHTML('<p>' . $this->message . '</p>');
    }
    
    public function setMessage($message) {
        $this->message = $message;
    }

    protected function addEmailAddressField() {
        $this->form->addField('email_address', 'email', array(
            'required' => 'true',
            'displayName' => Lang::getString('email'),
            'placeHolder' => Lang::getString('email_address')
        ));
    }

    protected function addSubmitButton() {
        $this->form->addHTML('<span class="submit_btn" title="Send Confirmation Email" tabindex="0">' . Lang::getString('send') . '</span>');
    }
    
}
