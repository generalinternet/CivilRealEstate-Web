<?php
/**
 * Description of AbstractContactIndConfirmEmailFormView
 *
 * @author General Internet
 * @copyright  2019 General Internet
 * @version    4.1.0
 */

abstract class AbstractContactIndConfirmEmailFormView extends MainWindowView {
    
    protected $form;
    /* @var AbstractContactInd */
    protected $contactInd;
    /* @var AbstractContactOrg */
    protected $parentContactOrg;
    protected $formBuilt = false;
    protected $title = '';
    protected $orgLoginEmail;


    public function __construct(GI_Form $form, AbstractContactInd $contactInd, AbstractContactOrg $parentContactOrg) {
        parent::__construct();
        $this->form = $form;
        $this->contactInd = $contactInd;
        $this->parentContactOrg = $parentContactOrg;
        $this->setWindowTitle('Send Access Invitation');
    }
    
    public function setOrgLoginEmail($orgLoginEmail) {
        $this->orgLoginEmail = $orgLoginEmail;
    }
    
    protected function addViewBodyContent() {
        $this->buildForm();
        $this->addHTML($this->form->getForm(''));
    }
    
    public function buildForm() {
        if (!$this->formBuilt) {
            $this->buildFormHeader();
            $this->buildFormBody();
            $this->buildFormFooter();
            $this->formBuilt = true;
        }
        
    }
    
    protected function buildFormHeader() {
        $this->form->addHTML('<h3>Send Access Invitation to '.$this->contactInd->getName().'?</h3>');
    }

    protected function buildFormBody() {
        $this->addMessage();
        $this->addLoginEmailField();
        $user = $this->contactInd->getUser();
        if (empty($user) || empty($user->getId())) {
            $contactCat = $this->parentContactOrg->getContactCat();
            if (!empty($contactCat) && $contactCat->isInternal()) {
                $this->addRoleField();
            }
        }
    }

    protected function buildFormFooter() {
        $this->addButtons();
    }

    protected function addButtons() {
        $this->form->addHTML('<div class="center_btns wrap_btns">');
        $this->addSubmitBtn();
        $this->addCancelBtn();
        $this->form->addHTML('</div>');
    }

    public function addSubmitBtn() {
        $this->form->addHTML('<span class="submit_btn">Send Email</span>');
    }

    public function addCancelBtn() {
        $this->form->addHTML('<span class="other_btn gray close_gi_modal">Cancel</span>');
    }
    
    protected function addMessage() {
        $this->form->addHTML('<p>The email will contain a link to a page where ' . $this->contactInd->getProperty('contact_ind.first_name') . ' will be able to set a password and gain access to the system. Please confirm the email address you want '. $this->contactInd->getProperty('contact_ind.first_name') . ' to use to log in (this is also where the email will be sent).</p>');
    }

    protected function addLoginEmailField($overwriteSettings = array()) {
        $value = $this->contactInd->getLoginEmail();
        if (empty($value)) {
            if (!empty($this->orgLoginEmail)) {
                $value = $this->orgLoginEmail;
            }
            if (empty($value)) {
                $value = $this->contactInd->getEmailAddress();
            }
        }
        $fieldSettings = GI_Form::overWriteSettings(array(
            'value'=>$value,
            'displayName'=>'Login Email',
            'required'=>true,
        ), $overwriteSettings);
        $this->form->addField('login_email', 'email', $fieldSettings);
    }
    
    protected function addRoleField($overwriteSettings = array()) {
        $options = RoleFactory::buildRoleOptions();
        $fieldSettings = GI_Form::overWriteSettings(array(
            'options'=>$options,
            'displayName'=>'Role',
            'required'=>true,
            'hideNull'=>true,
        ), $overwriteSettings);
        $this->form->addField('role_id', 'dropdown', $fieldSettings);
        
    }
        
}