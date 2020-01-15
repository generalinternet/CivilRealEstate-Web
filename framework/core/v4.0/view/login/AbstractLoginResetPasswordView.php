<?php
/**
 * Description of AbstractLoginResetPasswordView
 *
 * @author General Internet
 * @copyright  2016 General Internet
 * @version    4.0.0
 */
class AbstractLoginResetPasswordView extends GI_View {
    
    /**
     * @var GI_Form
     */
    protected $form;

    public function __construct(GI_Form $form) {
        $this->form = $form;
        
        parent::__construct();
        $this->buildForm();
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
            'required' => true
        ), $fieldSettings);
        $this->form->addField('password', 'password', $defaultFieldSettings);
    }
    
    protected function addRePasswordField($fieldSettings = array()){
        $defaultFieldSettings = $this->overWriteSettings(array(
            'displayName' => Lang::getString('re_enter_new_password'),
            'placeHolder' => Lang::getString('re_enter_new_password'),
            'value' => '',
            'required' => true
        ), $fieldSettings);
        $this->form->addField('password_conf', 'password', $defaultFieldSettings);
    }
    
    protected function addSubmitBtn(){
        $this->form->addHTML('<span class="submit_btn">' . Lang::getString('save_password') . '</span>');
    }

    public function buildForm() {
        $user = Login::getUser();
        if($user && $user->requiresPassReset()){
            $this->addHTML('<div class="alert_message red"><p>For your security, we require you to reset your password.</p></div>');
        }
        $this->addHTML('<h3>Enter a New Password</h3>');
        $this->addPasswordRules();
        $this->addPasswordField();
        $this->addRePasswordField();
        $this->addSubmitBtn();
    }
    
    protected function addPasswordRules(){
        $this->addHTML('<h4 class="sml_text">Your password</h4>');
        $this->addHTML('<ul class="simple_list sml_text">');
        $this->addHTML('<li>Cannot be the same as your current password.</li>');
        $minLength = ProjectConfig::getPassMinLength();
        if($minLength > 1){
            $this->addHTML('<li>Must be at least ' . $minLength . ' characters long.</li>');
        }
        
        $forceUpper = ProjectConfig::getPassReqUpper();
        if($forceUpper){
            $this->addHTML('<li>Must contain at least 1 uppercase letter.</li>');
        }
        
        $forceLower = ProjectConfig::getPassReqLower();
        if($forceLower){
            $this->addHTML('<li>Must contain at least 1 lowercase letter.</li>');
        }
        
        $forceSymbol = ProjectConfig::getPassReqSymbol();
        if($forceSymbol){
            $this->addHTML('<li>Must contain at least 1 symbol. (ex. #,@,!,?)</li>');
        }
        
        $forceNum = ProjectConfig::getPassReqNum();
        if($forceNum){
            $this->addHTML('<li>Must contain at least 1 number.</li>');
        }
        
        $this->addHTML('<li>Cannot contain any whitespace.</li>');
        $this->addHTML('</ul>');
    }

    public function buildView() {           
        $this->addHTML($this->form->getForm());
    }
    
    public function beforeReturningView() {
        $this->buildView();
    }
    
}
