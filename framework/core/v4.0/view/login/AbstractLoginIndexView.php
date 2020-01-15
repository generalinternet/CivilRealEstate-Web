<?php
/**
 * Description of AbstractLoginIndexView
 *
 * @author General Internet
 * @copyright  2016 General Internet
 * @version    2.0
 */
class AbstractLoginIndexView extends GI_View {
    
    /**
     * @var GI_Form
     */
    protected $form;
    protected $ajax = false;
    protected $addWrapper = false;
    protected $addRememberMe = true;
    protected $addForgotPass = true;

    public function __construct(GI_Form $form) {
        $this->form = $form;
        parent::__construct();
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
    
    public function setAddRememberMe($addRememberMe){
        $this->addRememberMe = $addRememberMe;
        return $this;
    }
    
    public function setAddForgotPass($addForgotPass){
        $this->addForgotPass = $addForgotPass;
        return $this;
    }
    
    protected function overWriteSettings($defaultFieldSettings, $fieldSettings = array()){
        foreach($fieldSettings as $setting => $value){
            $defaultFieldSettings[$setting] = $value;
        }
        return $defaultFieldSettings;
    }
    
    protected function addEmailField($fieldSettings = array()){
        $defaultFieldSettings = $this->overWriteSettings(array(
            'displayName' => 'Email',
            'placeHolder' => 'Email',
            'autoFocus' => true,
            'value' => '',
            'required' => true
        ), $fieldSettings);
        
        $this->form->addField('email', 'email', $defaultFieldSettings);
    }
    
    protected function addPasswordField($fieldSettings = array()){
        $defaultFieldSettings = $this->overWriteSettings(array(
            'displayName' => 'Password',
            'placeHolder' => 'Password',
            'value' => '',
            'required' => true
        ), $fieldSettings);
        $this->form->addField('password', 'password', $defaultFieldSettings);
    }
    
    protected function addRememberMeField($fieldSettings = array()){
        $defaultFieldSettings = $this->overWriteSettings(array(
            'displayName' => 'Remember Me',
            'value' => '0',
            'required' => false,
            'onoffStyleAsCheckbox' => true
        ), $fieldSettings);
        if($this->addRememberMe){
            $this->form->addField('remember_me', 'onoff', $defaultFieldSettings);
        }
    }
    
    protected function addForgotPasswordLink(){
        if($this->addForgotPass){
            $forgotPassURL = GI_URLUtils::buildURL(array(
                'controller' => 'login',
                'action' => 'forgotPassword'
            ));
            $this->form->addHTML('<a href="'.$forgotPassURL.'" title="Forgot Your Password?">Forgot Password?</a>');
        }
    }
    
    protected function addLoginActions(){
        if($this->addRememberMe || $this->addForgotPass){
            $this->form->addHTML('<div class="login_actions">');
            $this->addRememberMeField();
            $this->addForgotPasswordLink();
            $this->form->addHTML('</div>');
        }
    }
    
    protected function addSubmitBtn(){
        $this->form->addHTML('<span class="submit_btn">Log In</span>');
    }

    protected function buildForm() {
        $this->openFormBody();
            $this->buildFormBody();
        $this->closeFormBody();
    }
    
    protected function openViewWrap(){
        $this->addHTML('<div class="view_wrap">');
        return $this;
    }
    
    protected function closeViewWrap(){
        $this->addHTML('</div>');
        return $this;
    }
    
    protected function openFormBody($class ='') {
        $this->form->addHTML('<div class="main_body form_body'.$class.'">');
    }
    
    protected function closeFormBody() {
        $this->form->addHTML('</div><!--main_body-->');
    }
    
    protected function buildFormBody() {
        $this->addEmailField();
        $this->addPasswordField();
        $this->addLoginActions();
        $this->addSubmitBtn();
    }
    
    public function buildView() {
        if($this->ajax || $this->addWrapper){
            $this->openViewWrap()
                    ->addHTML('<h1 class="main_head">Log In</h1>');
        }
        $this->addHTML($this->form->getForm());
        if($this->ajax || $this->addWrapper){
            $this->closeViewWrap();
        }
    }
    
    public function beforeReturningView() {
        $this->buildView();
    }

}
