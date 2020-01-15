<?php
/**
 * Description of AbstractLoginResendConfirmationEmailFormView
 *
 * @author General Internet
 * @copyright  2016 General Internet
 * @version    2.0.0
 */
abstract class AbstractLoginResendConfirmationEmailFormView extends GI_View {

    protected $form;
    protected $formBuilt = false;
    protected $message = '';
    protected $ajax = false;
    protected $addWrapper = false;

    public function __construct(GI_Form $form) {
        parent::__construct();
        $this->form = $form;
        $this->buildForm();
    }
    
    public function setAddWrapper($addWrapper){
        $this->addWrapper = $addWrapper;
        return $this;
    }
    
    public function setAjax($ajax){
        $this->ajax = $ajax;
        return $this;
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
            'displayName' => 'Email',
            'placeHolder' => 'Email'
        ));
    }

    protected function addSubmitButton() {
        $this->form->addHTML('<span class="submit_btn" title="Send Confirmation Email">Send</span>');
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
        if($this->ajax || $this->addWrapper){
            $this->openViewWrap()
                    ->addHTML('<h1>Send Confirmation Email</h1>');
        }
        $this->addHTML($this->form->getForm());
        if($this->ajax || $this->addWrapper){
            $this->closeViewWrap();
        }
    }
    
}
