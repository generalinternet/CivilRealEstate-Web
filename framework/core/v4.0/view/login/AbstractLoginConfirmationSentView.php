<?php
/**
 * Description of AbstractLoginConfirmationSentView
 *
 * @author General Internet
 * @copyright  2019 General Internet
 * @version    4.0.0
 */
abstract class AbstractLoginConfirmationSentView extends MainWindowView {
    
    protected $user;
    protected $ajax = false;
    protected $addWrapper = false;
    
    public function __construct(AbstractUser $user) {
        parent::__construct();
        $this->user = $user;
        $this->addSiteTitle(Lang::getString('email_confirmation_sent'));
        $this->setWindowTitle(Lang::getString('email_confirmation_sent'));
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
        $this->addHTML('<p>If the email you provided matches our records, an email with confirmation instructions has been sent to your address. Please allow a few minutes for the message to arrive, and check your junk mail folder before requesting that the instructions be sent again.</p>');
        $resendURL = GI_URLUtils::buildURL(array(
            'controller' => 'login',
            'action' => 'sendConfirmationEmail',
            'id' => $this->user->getProperty('id')
        ));
        $this->addHTML('<p>If the email does not arrive after a few minutes, click <a href="'.$resendURL.'">here</a> to send it again.</p>');
        
        if(ProjectConfig::bypassEmailConfirmation()){
            $confirmURL = GI_URLUtils::buildURL(array(
                'controller' => 'login',
                'action' => 'confirmEmail',
                'id' => $this->user->getProperty('id'),
                'code' => $this->user->getProperty('confirm_code')
            ));
            $this->addHTML('<a href="' . $confirmURL . '" class="other_btn">Confirm Email</a>');
        }
    }
    
}
