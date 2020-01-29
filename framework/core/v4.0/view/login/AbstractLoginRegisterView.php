<?php
/**
 * Description of AbstractLoginRegisterView
 *
 * @author General Internet
 * @copyright  2016 General Internet
 * @version    2.0.0
 */
class AbstractLoginRegisterView extends MainWindowView {
    
    /**
     * @var GI_Form
     */
    protected $form;
    /**
     * @var User
     */
    protected $user = NULL;
    protected $addWrapper = false;
    protected $addViewFooter = false;
    protected $doNotRedirect = false;

    public function __construct(GI_Form $form, AbstractUser $user) {
        $this->form = $form;
        $this->user = $user;
        parent::__construct();
        $this->buildForm();
        $this->addSiteTitle(Lang::getString('sign_up'));
        $this->setWindowTitle(Lang::getString('sign_up'));
    }
    
    public function setAddWrapper($addWrapper){
        $this->addWrapper = $addWrapper;
        return $this;
    }
    
    public function setDoNotRedirect($doNotRedirect){
        $this->doNotRedirect = $doNotRedirect;
        return $this;
    }

    protected function buildForm() {
        $formView = $this->user->getFormView($this->form, $this->user);
        $formView->setRegisterForm(true);
        if(ProjectConfig::registerRequiresConfirmation()){
            $formView->setShowPasswordFields(false);
        }
        $formView->buildForm();
        $this->form->addHTML('<span class="submit_btn" tabindex="0">' . Lang::getString('sign_up') . '</span>');
    }
    
    protected function addViewBodyContent(){
        $this->addHTML($this->form->getForm());
        $this->addLoginLink();
    }
                
    protected function addLoginLink(){
        $loginURL = GI_URLUtils::buildURL(array(
            'controller' => 'login',
            'action' => 'index'
        ));
        $this->addHTML('<p>Already have an account? <a href="' . $loginURL . '" title="' . Lang::getString('log_in') . '"  class="login_action_btn">' . Lang::getString('log_in') . '</a></p>');
    }

}
