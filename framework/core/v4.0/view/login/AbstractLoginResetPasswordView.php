<?php
/**
 * Description of AbstractLoginResetPasswordView
 *
 * @author General Internet
 * @copyright  2016 General Internet
 * @version    4.0.0
 */
class AbstractLoginResetPasswordView extends MainWindowView {
    
    /**
     * @var GI_Form
     */
    protected $form;

    public function __construct(GI_Form $form) {
        $this->form = $form;
        
        parent::__construct();
        $this->buildForm();
        $this->addSiteTitle(Lang::getString('reset_password'));
        $this->setWindowTitle(Lang::getString('enter_a_new_password'));
    }
    
    protected function overWriteSettings($defaultFieldSettings, $fieldSettings = array()){
        foreach($fieldSettings as $setting => $value){
            $defaultFieldSettings[$setting] = $value;
        }
        return $defaultFieldSettings;
    }
    
    protected function addPasswordField($fieldSettings = array()){
        $defaultFieldSettings = $this->overWriteSettings(array(
            'displayName' => Lang::getString('new_password'),
            'placeHolder' => Lang::getString('new_password'),
            'value' => '',
            'required' => true,
            'autoComplete' => false,
            'inputAutoCompleteVal' => 'new-password'
        ), $fieldSettings);
        $this->form->addField('password', 'password', $defaultFieldSettings);
    }
    
    protected function addRePasswordField($fieldSettings = array()){
        $defaultFieldSettings = $this->overWriteSettings(array(
            'displayName' => Lang::getString('re_enter_new_password'),
            'placeHolder' => Lang::getString('re_enter_new_password'),
            'value' => '',
            'required' => true,
            'autoComplete' => false,
            'inputAutoCompleteVal' => 'new-password'
        ), $fieldSettings);
        $this->form->addField('password_conf', 'password', $defaultFieldSettings);
    }
    
    protected function addSubmitBtn(){
        $this->form->addHTML('<span class="submit_btn" tabindex="0">' . Lang::getString('save_password') . '</span>');
    }

    public function buildForm() {
        $user = Login::getUser();
        if($user && $user->requiresPassReset()){
            $this->form->addHTML('<div class="alert_message red"><p>For your security, we require you to reset your password.</p></div>');
        }
        $this->addPasswordField();
        $this->addRePasswordField();
        $this->addPasswordRules();
        $this->addSubmitBtn();
    }
    
    protected function addPasswordRules(){
        $this->form->addHTML(GI_StringUtils::getPasswordRules('password', 'password_conf', true));
    }
    
    protected function addViewBodyContent(){
        $this->addHTML($this->form->getForm());
        $this->addLoginLink();
        $this->addRegisterLink();
    }
                
    protected function addLoginLink(){
        $loginURL = GI_URLUtils::buildURL(array(
            'controller' => 'login',
            'action' => 'index'
        ));
        $this->addHTML('<p>Nevermind I remember my old password. <a href="' . $loginURL . '" title="' . Lang::getString('log_in') . '" class="login_action_btn">' . Lang::getString('log_in') . '</a></p>');
    }
                
    protected function addRegisterLink(){
        if(!ProjectConfig::isRegistrationEnabled()){
            return;
        }
        $registerURL = GI_URLUtils::buildURL(array(
            'controller' => 'login',
            'action' => 'register'
        ));
        $this->addHTML('<p>Would you like to create a new account instead? <a href="' . $registerURL . '" title="' . Lang::getString('sign_up') . '" class="login_action_btn" >' . Lang::getString('sign_up') . '</a></p>');
    }
    
}
