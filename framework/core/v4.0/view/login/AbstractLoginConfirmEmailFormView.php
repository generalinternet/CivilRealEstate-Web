<?php
/**
 * Description of AbstractLoginConfirmEmailFormView
 *
 * @author General Internet
 * @copyright  2017 General Internet
 * @version    4.0.0
 */
Abstract class AbstractLoginConfirmEmailFormView extends MainWindowView {
    
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
        $this->addSiteTitle(Lang::getString('confirm_email'));
        $this->setWindowTitle(Lang::getString('confirm_email'));
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
            
            $this->addPasswordRules();
            
            $this->form->addHTML('<span class="submit_btn" title="Submit" tabindex="0">' . Lang::getString('submit') . '</span>');
            $this->formBuilt = true;
        }
    }
    
    protected function addFormHeader() {
        $sendCodeURL = GI_URLUtils::buildURL(array(
            'controller'=>'login',
            'action'=>'sendConfirmationEmail',
            'id'=>$this->user->getProperty('id'),
        ));
        $this->form->addHTML('<h3>Set your Password</h3>');
//        if ($this->addCodeField) {
            $this->form->addHTML('<p>If you need another confirmation code, you can <a href="'.$sendCodeURL.'" title="Re-send confirmation code">re-send the confirmation email</a>.</p>');
//        }
    }
    
    protected function addPasswordRules(){
        $this->form->addHTML(GI_StringUtils::getPasswordRules('password', 'password_two', false));
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
            'autoComplete' => false,
            'inputAutoCompleteVal' => 'new-password'
        ));
    }

    protected function addConfirmPasswordField() {
        $this->form->addField('password_two', 'password', array(
            'displayName' => Lang::getString('re_enter_new_password'),
            'placeHolder' => Lang::getString('re_enter_new_password'),
            'required'=>true,
            'autoComplete' => false,
            'inputAutoCompleteVal' => 'new-password'
        ));
    }

}
