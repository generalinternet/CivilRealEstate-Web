<?php
/**
 * Description of AbstractContactIndProfileFormView
 *
 * @author General Internet
 * @copyright  2019 General Internet
 * @version    4.0.0
 */

abstract class AbstractContactIndProfileFormView extends MainWindowView {
    /* @var GI_Form */
    protected $form;
    /* @var AbstractContactInd */
    protected $contactInd;
    /* @var AbstractContactOrg */
    protected $parentContactOrg;
    /* @var AbstractUser */
    protected $user;
    protected $formBuilt = false;
    protected $isPrimaryIndividual = false;
    protected $ajax = false;
    protected $avatarUploader = NULL;
    
    public function __construct(GI_Form $form, AbstractContactInd $contactInd, AbstractContactOrg $parentContactOrg = NULL, AbstractUser $user = NULL) {
        parent::__construct();
        $this->form = $form;
        $this->contactInd = $contactInd;
        $this->parentContactOrg = $parentContactOrg;
        if (!empty($parentContactOrg) && $parentContactOrg->getProperty('contact_org.primary_individual_id') === $contactInd->getId()) {
            $this->isPrimaryIndividual = true;
        }
        $this->setListBarURL($contactInd->getProfileListBarURL());
        if (empty($user)) {
            $user = UserFactory::buildNewModel('unconfirmed');
        }
        $this->user = $user;
        if (empty($contactInd->getId())) {
            $title = 'Add ';
        } else {
            $title = 'Edit ';
        }
        $title .= 'Person';
        $this->setWindowTitle($title);
    }

    public function setAjax($ajax) {
        $this->ajax = $ajax;
    }

    public function isAjax() {
        return $this->ajax;
    }

    public function buildForm() {
        if (!$this->formBuilt) {
            $this->buildFormBody();
            $this->buildFormFooter();
            $this->formBuilt = true;
        }
    }

    public function buildFormBody() {
        if (!$this->isPrimaryIndividual) {
            $this->buildContactSection();
            $this->form->addHTML('<hr />');
        }
        $this->buildUserSection();
    }
    
    protected function buildContactSection() {
        $this->form->addHTML('<h2>Contact</h2>');
        $this->form->addHTML('<div class="flex_row">')
                ->addHTML('<div class="flex_col">');
        $this->addFirstNameField();
        $this->form->addHTML('</div>')
                ->addHTML('<div class="flex_col">');
        $this->addLastNameField();
        $this->form->addHTML('</div>')
                ->addHTML('</div>');
        
        $this->form->addHTML('<div class="flex_row">')
                ->addHTML('<div class="flex_col">');
        $this->addContactInfoSection();
        $this->form->addHTML('</div>')
                ->addHTML('</div>');
        
    }

    protected function addFirstNameField($overwriteSettings = array()) {
        $fieldSettings = GI_Form::overWriteSettings(array(
            'displayName'=>'First Name',
            'required'=>true,
            'value'=>$this->contactInd->getProperty('contact_ind.first_name'),
        ), $overwriteSettings);
        $this->form->addField('first_name', 'text', $fieldSettings);
    }
    
    protected function addLastNameField($overwriteSettings = array()) {
        $fieldSettings = GI_Form::overWriteSettings(array(
            'displayName'=>'Last Name',
            'required'=>false,
            'value'=>$this->contactInd->getProperty('contact_ind.last_name'),
        ), $overwriteSettings);
        $this->form->addField('last_name', 'text', $fieldSettings);
    }
    
    protected function addContactInfoSection() {
        $pTypeRefs = array();
        $contactInfos = $this->contactInd->getContactInfoArrayFromForm($this->form);
        $this->form->addHTML('<div class="auto_columns">');
        foreach ($contactInfos as $pTypeRef => $contactInfos) {
            $pTypeRefs[] = $pTypeRef;
            $pType = ContactInfoFactory::buildNewModel($pTypeRef);
            if(empty($contactInfos)){
                continue;
            }
            $formBlockAlignment = $contactInfos[0]->getFormBlockAlignment();
            
            $this->form->addHTML('<div class="' . $formBlockAlignment . '">');
            $this->form->startFieldset($pType->getTypeTitle());
            
            $contactInfoWrapClass = '';

            $addAddrElementWrap = true;
            if($formBlockAlignment == 'multi_column'){
                $contactInfoWrapClass .= ' auto_columns';
            } else {
                $addAddrElementWrap = false;
            }
            
            $this->form->addHTML('<div class="contact_infos_wrap ' . $pTypeRef . ' ' . $contactInfoWrapClass . '">');
            
            $itemCount = 0;
            foreach ($contactInfos as $contactInfo) {
                $contactInfo->setFieldSuffix($itemCount);
                $contactInfoFormView = $contactInfo->getFormView($this->form);
                $contactInfoFormView->setPType($pTypeRef);
                $contactInfoFormView->buildForm();
                $itemCount++;
            }
            
            if($this->contactInd->multiInfoEnabled($pTypeRef)){
                $addContactInfoURL = GI_URLUtils::buildURL(array(
                    'controller' => 'contact',
                    'action' => 'addContactInfo',
                    'type'=> $pTypeRef
                ));
                $this->form->addHTML('<a href="' . $addContactInfoURL . '" class="custom_btn add_contact_info">'.GI_StringUtils::getIcon('add').'<span class="btn_text">' . $pType->getTypeTitle() . '</span></a>');
            }
            
            $this->form->addHTML('</div>');
            
            $this->form->endFieldset();
                
            $this->form->addHTML('</div>');
        }
        $this->form->addHTML('</div>');
        
        $this->form->addField('p_type_refs', 'hidden', array(
            'value' => implode(',', $pTypeRefs)
        ));
    }


    protected function buildUserSection() {
        $this->form->addHTML('<h2>System Access</h2>');
        $this->form->addHTML('<div class="auto_columns">');
            $this->addLoginEmailField();
            $this->addAvatarUploader();
            $this->form->addHTML('<div class="column">');
                $this->addRoleField();
                $this->addPermissionsField();
            $this->form->addHTML('</div>');
        $this->form->addHTML('</div>');
        $this->addPasswordFields();
    }

    protected function addLoginEmailField($overwriteSettings = array()) {
        $fieldSettings = GI_Form::overWriteSettings(array(
                    'value' => $this->user->getProperty('email'),
                    'required' => false,
                        ), $overwriteSettings);
        $this->form->addField('login_email', 'email', $fieldSettings);
    }

    protected function addRoleField() {
        if (empty($this->user)) {
            return;
        }
        if (empty($this->parentContactOrg)) {
            return;
        }
        $contactCat = $this->parentContactOrg->getContactCat();
        if (empty($contactCat)) {
            return;
        }

        $hiddenField = true;
        $options = NULL;
        $roleId = NULL;
        $roles = NULL;
        if ($contactCat->isInternal() && Permission::verifyByRef('assign_roles')) {
            if ((empty($this->user) || empty($this->user->getId())) || ($this->user->getId() !== Login::getUserId())) {
                $options = Role::buildRoleOptions();
                if (count($options) > 1) {
                    $hiddenField = false;
                }
            }
        } else {
            return; //For security - so that users cannot change role id via console.
        }
        if (!empty($this->user) && !empty($this->user->getId())) {
            $roles = RoleFactory::getRolesByUser($this->user);
        }
        if (!empty($roles)) {
            $currentRole = $roles[0];
            $roleId = $currentRole->getProperty('id');
        }
        if ($hiddenField) {
            if (empty($roleId)) {
                $defaultRole = $contactCat->getNewUserDefaultRole();
                if (!empty($defaultRole)) {
                    $roleId = $defaultRole->getId();
                }
            }
            if (!empty($roleId)) {
                $this->form->addField('role_id', 'hidden', array(
                    'value' => $roleId,
                ));
            }
        } else {
            $this->form->addHTML('<br />');
            $this->form->addField('role_id', 'dropdown', array(
                'options' => $options,
                'value' => $roleId,
                'displayName' => 'Role',
                'required' => true,
            ));
        }
    }

    protected function addPermissionsField($overWriteSettings = array(), $overWriteAutocompProps = array()) {
        if (Permission::verifyByRef('set_permissions') && (is_null($this->user->getProperty('id')) || !$this->user || $this->user->getProperty('id') != Login::getUserId())) {
            $userPermissions = PermissionFactory::getPermissionsLinkedToUser($this->user);
            $userPermissionIds = array();
            foreach ($userPermissions as $userPermission) {
                $userPermissionIds[] = $userPermission->getId();
            }

            $autocompProps = array(
                'controller' => 'autocomplete',
                'action' => 'permission',
                'ajax' => 1
            );
            foreach ($overWriteAutocompProps as $prop => $val) {
                $autocompProps[$prop] = $val;
            }
            $autocompURL = GI_URLUtils::buildURL($autocompProps);

            $fieldSettings = GI_Form::overWriteSettings(array(
                        'autocompURL' => $autocompURL,
                        'value' => implode(',', $userPermissionIds),
                        'displayName' => Lang::getString('permissions'),
                        'placeHolder' => Lang::getString('permissions'),
                        'autocompMultiple' => true
                            ), $overWriteSettings);
            $this->form->addHTML('<br />');
            $this->form->addField('permission_ids', 'autocomplete', $fieldSettings);
        }
    }

    protected function addPasswordFields() {
        $this->form->addHTML('<hr/>');

        //the below forces firefox to respect [autocomplete="off"]
        $this->form->addHTML('<input type="text" style="display:none" />');
        $this->addPasswordField();
        $this->addRepeatPasswordField();

        $showCannotBeSame = true;
        if (!$this->user->getId() || empty($this->user->getProperty('pass'))) {
            $showCannotBeSame = false;
        }
        $this->form->addHTML(GI_StringUtils::getPasswordRules('new_password', 'repeat_password', $showCannotBeSame));
    }

    protected function addPasswordField($overwriteSettings = array()){
        $passwordText = Lang::getString('new_password');
        $passwordRequired = false;        
        $fieldSettings = GI_Form::overWriteSettings(array(
            'displayName' => $passwordText,
            'placeHolder' => $passwordText,
            'required' => $passwordRequired,
            'autoComplete' => false,
            'inputAutoCompleteVal' => 'new-password'
        ), $overwriteSettings);
        $this->form->addField('new_password', 'password', $fieldSettings);
    }
    
    protected function addRepeatPasswordField($overwriteSettings = array()){
        $rePasswordText = Lang::getString('re_enter_new_password');
        $passwordRequired = false;

        
        $fieldSettings = GI_Form::overWriteSettings(array(
            'displayName' => $rePasswordText,
            'placeHolder' => $rePasswordText,
            'required' => $passwordRequired,
                    'autoComplete' => false,
                    'description' => 'Re-enter your password to confirm.',
                    'inputAutoCompleteVal' => 'new-password'
                        ), $overwriteSettings);
        $this->form->addField('repeat_password', 'password', $fieldSettings);
    }
    
    protected function addAvatarUploader() {
        if (!empty($this->avatarUploader)) {
            $this->form->addHTML($this->avatarUploader->getHTMLView());
        }
    }

    public function setAvatarUploader(AbstractGI_Uploader $avatarUploader) {
        $this->avatarUploader = $avatarUploader;
        return $this;
    }

    protected function buildFormFooter() {
        $this->addSubmitButton();
    }

    protected function addSubmitButton() {
        $this->form->addHTML('<span class="submit_btn">Save</span>');
    }


    protected function addViewBodyContent() {
        $this->buildForm();
        $this->addHTML($this->form->getForm(''));
    }
    
    public function getSubmittedNextStep() {
        return NULL;
    }
    
    public function getNextChildStep() {
        return NULL;
    }
    
    
}