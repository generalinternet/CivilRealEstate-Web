<?php
/**
 * Description of AbstractLoginConfirmEmailFormView
 *
 * @author General Internet
 * @copyright  2017 General Internet
 * @version    2.0.1
 */
Abstract class AbstractLoginConfirmEmailFormView extends GI_View {
    
    protected $form;
    protected $user;
    protected $formBuilt = false;
    protected $addCodeField = false;
    protected $ajax = false;
    protected $addWrapper = false;
    
    public function __construct(GI_Form $form, AbstractUser $user) {
        parent::__construct();
        $this->form = $form;
        $this->user = $user;
    }
    
    public function setAddWrapper($addWrapper){
        $this->addWrapper = $addWrapper;
        return $this;
    }
    
    public function setAjax($ajax){
        $this->ajax = $ajax;
        return $this;
    }
    
    protected function buildView() {
        if($this->ajax || $this->addWrapper){
            $this->openViewWrap();
        }
        $this->buildForm();
        $this->addHTML($this->form->getForm(''));
        if($this->ajax || $this->addWrapper){
            $this->closeViewWrap();
        }
    }
    
    public function setAddCodeField($addCodeField = false) {
        $this->addCodeField = $addCodeField;
    }
    
    public function buildForm() {
        if (!$this->formBuilt) {
            
            $this->addFormHeader();
            
            $this->addMessageSection();
            
            if ($this->addCodeField) {
                $this->addCodeField();
            }

            $this->addPasswordField();
            
            $this->addConfirmPasswordField();
            
            $this->form->addHTML('<span class="submit_btn" title="Submit">Submit</span>');
            $this->formBuilt = true;
        }
    }
    
    protected function addFormHeader() {
        $sendCodeURL = GI_URLUtils::buildURL(array(
            'controller'=>'login',
            'action'=>'sendConfirmationEmail',
            'id'=>$this->user->getProperty('id'),
        ));
        $this->addHTML('<h1>Create a Password</h1>');
        $this->addHTML('<p>If you need another confirmation code, you can <a href="'.$sendCodeURL.'" title="Re-send confirmation code">re-send the confirmation email</a>.</p>');
    }
    
    protected function addMessageSection() {
        
    }
    
    protected function addCodeField() {
        $this->form->addField('code', 'text', array(
            'required'=>true,
            'displayName'=>'Confirm Code',
            'placeHolder' => 'Confirm Code',
        ));
    }

    protected function addPasswordField() {
        $this->form->addField('password', 'password', array(
            'displayName' => Lang::getString('new_password'),
            'placeHolder' => Lang::getString('new_password'),
            'required'=>true,
        ));
    }

    protected function addConfirmPasswordField() {
        $this->form->addField('password_two', 'password', array(
            'displayName' => Lang::getString('re_enter_new_password'),
            'placeHolder' => Lang::getString('re_enter_new_password'),
            'required'=>true
        ));
    }

    public function beforeReturningView() {
        $this->buildView();
    }
    
    protected function openViewWrap(){
        $this->addHTML('<div class="content_padding">');
        return $this;
    }
    
    protected function closeViewWrap(){
        $this->addHTML('</div>');
        return $this;
    }

}
