<?php
/**
 * Description of AbstractLoginForgotPasswordView
 *
 * @author General Internet
 * @copyright  2016 General Internet
 * @version    2.0
 */
class AbstractLoginForgotPasswordView extends MainWindowView {
    
    /**
     * @var GI_Form
     */
    protected $form;
    protected $thanks = false;
    protected $url = NULL;
    protected $addLoginLink = true;

    public function __construct(GI_Form $form) {
        $this->form = $form;
        
        parent::__construct();
        $this->buildForm();
        $this->addSiteTitle(Lang::getString('reset_password'));
        $this->setWindowTitle(Lang::getString('reset_password'));
    }
    
    public function setThanks($thanks){
        $this->thanks = $thanks;
        return $this;
    }
    
    public function setURL($url){
        $this->url = $url;
        return $this;
    }
    
    public function setAddLoginLink($addLoginLink){
        $this->addLoginLink = $addLoginLink;
        return $this;
    }

    protected function overWriteSettings($defaultFieldSettings, $fieldSettings = array()){
        foreach($fieldSettings as $setting => $value){
            $defaultFieldSettings[$setting] = $value;
        }
        return $defaultFieldSettings;
    }
    
    protected function addEmailField($fieldSettings = array()){
        $this->form->setBotValidation(true);
        /*
        $this->form->addHTML('<div style="position: absolute; left: -9999px;">');
        $this->form->addField("email", "email", array(
            'displayName' => 'Email',
            'placeHolder' => 'Email',
            'autoComplete' => false
            )
        );
        $this->form->addHTML('</div>');
         */
        $defaultFieldSettings = $this->overWriteSettings(array(
            'displayName' => 'Email',
            'placeHolder' => 'Email',
            'autoFocus' => true,
            'value' => '',
            'required' => true
        ), $fieldSettings);
        
        $this->form->addField('rEmail', 'email', $defaultFieldSettings);
    }
    
    protected function addLoginActions(){
        $this->form->addHTML('<p>Enter your email and you will receive a link to reset your password.</p>');
    }
    
    protected function addSubmitBtn(){
        $this->form->addHTML('<span class="submit_btn" tabindex="0">'.Lang::getString('reset_password').'</span>');
    }
    
    protected function addResetMessage(){
        $this->addHTML('<p>An email has been sent to the address provided, check your junk/spam folder if you don’t receive an email within the next 5 minutes.</p>');
        if(DEV_MODE && !empty($this->url)){
            $this->addHTML('<p>DEV MODE ON: <i><a href="' . $this->url . '" title="Reset Password">Reset Password</a></i></p>');
        }
    }
    
    protected function buildForm() {
        $this->addEmailField();
        $this->addLoginActions();
        $this->addSubmitBtn();
    }
    
    protected function addForm(){
        if($this->thanks){
            $this->addResetMessage();
        } else {
            $this->addHTML($this->form->getForm());
        }
    }
    
    protected function addViewBodyContent(){
        $this->addForm();
        $this->addLoginLink();
    }
    
    protected function addLoginLink(){
        if(!$this->addLoginLink){
            return;
        }
        $loginURL = GI_URLUtils::buildURL(array(
            'controller' => 'login',
            'action' => 'index'
        ));
        if($this->thanks){
            $this->addHTML('<a href="' . $loginURL . '" title="' . Lang::getString('log_in') . '" class="other_btn login_action_btn" >' . Lang::getString('log_in') . '</a></p>');
        } else {
            $this->addHTML('<p>Remembered your password? <a href="' . $loginURL . '" title="' . Lang::getString('log_in') . '" class="login_action_btn">' . Lang::getString('log_in') . '</a></p>');
        }
    }

}
