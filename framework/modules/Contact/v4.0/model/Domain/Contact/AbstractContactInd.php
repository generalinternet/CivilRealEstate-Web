<?php
/**
 * Description of AbstractContactInd
 *
 * @author General Internet
 * @copyright  2019 General Internet
 * @version    4.1.0
 */
abstract class AbstractContactInd extends AbstractContact {
    
    protected $parentContactOrg;
    protected $parentContactOrgs;
    
    public function getParentContactOrgs() {
        if (is_null($this->parentContactOrgs)) {
            $search = ContactFactory::search();
            $search->filterByTypeRef('org');
       //     $search->filter('org.primary_individual_id', $this->getId());
            $tableName = $search->prefixTableName('contact');
            $search->join('contact_relationship', 'p_contact_id', $tableName, 'id', 'REL');
            $search->filter('REL.c_contact_id', $this->getId());
            $search->orderBy('id', 'ASC');
            $this->parentContactOrgs = $search->select();
        }
        return $this->parentContactOrgs;
    }
    
    public function getParentContactOrg() {
        if (empty($this->parentContactOrg)) {
            $parentContactOrgs = $this->getParentContactOrgs();
            if (!empty($parentContactOrgs)) {
                $this->parentContactOrg = $parentContactOrgs[0];
            }
        }
        return $this->parentContactOrg;
    }
    
    public function setParentContactOrg(AbstractContactOrg $parentOrg) {
        $this->parentContactOrg = $parentOrg;
    }


    public static function addNameFilterToDataSearch($name, \GI_DataSearch $dataSearch, $contactTableAlias = NULL) {
        $filterColumns = array(
            'ind.first_name',
            'ind.last_name'
        );
        if (ProjectConfig::getContactUseFullyQualifiedName()) {
            $fqnCol = 'fully_qualified_name';
            if(!empty($contactTableAlias)){
                $fqnCol = $contactTableAlias . '.' . $fqnCol;
            }
            $filterColumns[] = $fqnCol;
        }
        $dataSearch->filterGroup()
                ->filterTermsLike($filterColumns, $name)
                ->filter('ind.status', 1)
                ->closeGroup();
        
        $dataSearch->orderByLikeScore($filterColumns, $name);
        
        parent::addNameFilterToDataSearch($name, $dataSearch, $contactTableAlias);
    }

    public function getFormView(GI_Form $form) {
        $formView = new ContactIndFormView($form, $this);
        $this->setUploadersOnFormView($formView);
        if (ProjectConfig::getIsFranchisedSystem()) { //TODO - move this logic to child if class for franchise owner is created
            $sourceUserId = $this->getProperty('source_user_id');
            if (!empty($sourceUserId) && $sourceUserId == Login::getUserId()) { //it's me!
                $formView->setHideLoginEmail(true);
                $formView->setHideRole(true);
                $formView->setHideInternal(true);
                $formView->setHideDefaultCurrency(true);
                $formView->setHideContactCategories(true);
            }
        }
        return $formView;
    }

    public function setPropertiesFromForm(\GI_Form $form) {
        parent::setPropertiesFromForm($form);
        $firstName = filter_input(INPUT_POST, 'first_name');
        $lastName = filter_input(INPUT_POST, 'last_name');
        $this->setProperty('contact_ind.first_name', $firstName);
        $this->setProperty('contact_ind.last_name', $lastName);
        return true;
    }

    public function getName() {
        $firstName = $this->getProperty('contact_ind.first_name');
        $lastName = $this->getProperty('contact_ind.last_name');
        $name = $firstName . ' ' . $lastName;
        return $name;
    }

    public static function getUITableCols() {
        $tableColArrays = array(
            array(
                'header_title' => '',
                'method_name' => 'getAvatarHTML',
                'cell_url_method_name' => 'getViewURL',
                'css_class' => 'avatar_cell',
                'css_header_class' => 'avatar_cell'
            ),
            //Name
            array(
                'header_title' => 'Name',
                'method_name' => 'getName',
                'cell_url_method_name' => 'getViewURL',
                'css_class' => ''
            ),
            //Address
            array(
                'header_title' => 'Address',
                'method_name' => 'getAddress',
            ),
            //Phone Number
            array(
                'header_title' => 'Phone Number',
                'method_name' => 'getPhoneNumber',
            ),
            //Email Address
            array(
                'header_title' => 'Email',
                'method_name' => 'getEmailAddress',
            )
        );
        $UITableCols = array();
        foreach ($tableColArrays as $tableColArray) {
            $UITableCols[] = UITableCol::buildUITableColFromArray($tableColArray);
        }
        return $UITableCols;
    }

    public function getViewTitle($plural = true) {
        $title = 'Individual';
        if ($plural) {
            $title .= 's';
        }
        return $title;
    }

    public function getDetailView() {
        $detailView = new ContactIndDetailView($this);
        return $detailView;
    }

    public function getSummaryView($relationship = NULL) {
        $summaryView = new ContactIndSummaryView($this, $relationship);
        return $summaryView;
    }

//    public function getLinkedContactsDetailView($linkedTypeRefs = NULL) {
//        if (empty($linkedTypeRefs)) {
//            $linkedTypeRefs = array(
//                'org' => 'parent',
//            );
//        }
//        return parent::getLinkedContactsDetailView($linkedTypeRefs);
//    }
    
    public function getContactRelationshipsDetailView($linkedTypeRefs = NULL) {
        if (empty($linkedTypeRefs)) {
            $linkedTypeRefs = array(
                'org' => 'parent',
            );
        }
        return parent::getContactRelationshipsDetailView($linkedTypeRefs);
    }
    
    protected function setUserValues(AbstractUser $user){
        $firstName = $this->getProperty('contact_ind.first_name');
        $lastName = $this->getProperty('contact_ind.last_name');
        $user->setProperty('first_name', $firstName);
        $user->setProperty('last_name', $lastName);
        return parent::setUserValues($user);
    }
    
    public function getAvatarClass(){
        $avatarClass = parent::getAvatarClass();
        return $avatarClass . ' double_initials';
    }

    public function getInitials(){
        $firstName = $this->getProperty('contact_ind.first_name');
        $lastName = $this->getProperty('contact_ind.last_name');
        $initials = substr($firstName, 0, 1) . substr($lastName, 0, 1);
        return $initials;
    }
    
    public function handleFormSubmission($form, $pId = NULL) {
        $newModel = false;
        if (empty($this->getProperty('id'))) {
            $newModel = true;
        }
        if (!parent::handleFormSubmission($form, $pId)) {
            return false;
        }
        
        
        if ($newModel && !Permission::verifyByRef('view_contacts')) {
            $assignedToContact = AssignedToContactFactory::buildNewModel('assigned_to');
            $assignedToContact->setProperty('contact_id', $this->getProperty('id'));
            $assignedToContact->setProperty('user_id', Login::getUserId());
            if (!$assignedToContact->save()) {
                return false;
            }
        }
        return true;
    }

    public function isIndividual() {
        return true;
    }

    protected function handleImportFromQBFormFields(GI_Form $form) {
        if (parent::handleImportFromQBFormFields($form)) {
            $contactQB = $this->getContactQB();
            if (empty($contactQB)) {
                return false;
            }
            $firstName = $contactQB->getProperty('first_name');
            $lastName = $contactQB->getProperty('last_name');
            
            $this->setProperty('contact_ind.first_name', $firstName);
            $this->setProperty('contact_ind.last_name', $lastName);
            
            return true;
        }
        return false;
    }

    protected function handleExportToQBFormFields(GI_Form $form, AbstractContactQB $contactQB) {
        if (parent::handleExportToQBFormFields($form, $contactQB)) {
            $contactQB->setProperty('first_name', $this->getProperty('contact_ind.first_name'));
            $contactQB->setProperty('last_name', $this->getProperty('contact_ind.last_name'));
            //TODO - display name field
            return true;
        }
        return false;
    }
    
    public function getIconClass() {
        return strtolower($this->getTypeTitle());
    }

    /** @return string */
  
//    public function getPublicViewURL() {
//       
//        //Look for associated orgs
//        $linkedTypeRef = 'org';
//        $relation = 'parent';
//        $linkedRelationships = $this->getContactRelationships($linkedTypeRef, $relation);
//        if (!empty($linkedRelationships)) {
//            //Get the first org model
//            $firstLink = $linkedRelationships[0];
//            if (!empty($firstLink)) {
//                $parentOrgId = $linkedRelationships[0]->getProperty('p_contact_id');
//                if (!empty($parentOrgId)) {
//                    return GI_URLUtils::buildURL(array(
//                        'controller' => 'contact',
//                        'action' => 'viewPublicInfo',
//                        'id' => $parentOrgId,
//                    ));
//                }
//            }
//        }
//        
//        return GI_URLUtils::buildURL(array(
//                    'controller' => 'contact',
//                    'action' => 'viewPublicInfo',
//                    'id' => $this->getId(),
//                ));
//    }
    
    /** @return \ContactPublicDetailView */
    public function getPublicDetailView() {
        return parent::getPublicDetailView();
    }

    /*     * * Profile */

    public function getProfileDetailView() {
        return new ContactIndProfileDetailView($this);
    }

    protected function getProfileFormViewObject(GI_Form $form, $curStep = 1) {
        $user = $this->getUser();
        $parentContactOrg = $this->getParentContactOrg();
        $view = new ContactIndProfileFormView($form, $this, $parentContactOrg, $user);
        if (!empty($user)) {
            $avatarUploader = $user->getAvatarUploader($form);
            $view->setAvatarUploader($avatarUploader);
        }
        return $view;
    }

    /**
     * @param GI_Form $form
     * @param type $step
     * @return boolean
     */
    public function handleProfileFormSubmission(GI_Form $form, $step = 1) {
        if ($form->wasSubmitted() && $this->validateProfileForm($form, $step)) {
            if (!$this->handleContactSectionProfileFormSubmission($form)) {
                return false;
            }
            if (!$this->handleUserSectionProfileFormSubmission($form)) {
                return false;
            }
            return true;
        }
        return false;
    }
    
    protected function handleContactSectionProfileFormSubmission(GI_Form $form) {
        $firstName = filter_input(INPUT_POST, 'first_name');
        if (!empty($firstName)) {
            $lastName = filter_input(INPUT_POST, 'last_name');
            $this->setProperty('contact_ind.first_name', $firstName);
            $this->setProperty('contact_ind.last_name', $lastName);
            if (!$this->save()) {
                return false;
            }
            
            if (!$this->handleContactInfoFormSubmission($form)) {
                return false;
            }
        }
        return true;
    }

    protected function handleUserSectionProfileFormSubmission(GI_Form $form) {
        $loginEmail = filter_input(INPUT_POST, 'login_email');
        if (!empty($loginEmail)) {
            $user = $this->getUser();
            if (empty($user)) {
                return false;
            }
            $newUser = false;
            if (empty($user->getId())) {
                $newUser = true;
            }
            $avatarUploader = $user->getAvatarUploader($form);
            $password = filter_input(INPUT_POST, 'new_password');
            
            if (!empty($password)) {
                $salt = $user->generateSalt();
                $user->setProperty('salt', $salt);
                $pass = $user->generateSaltyPass($password, $salt);
                $user->setProperty('pass', $pass);
            }
            $user->setProperty('first_name', $this->getProperty('contact_ind.first_name'));
            $user->setProperty('last_name', $this->getProperty('contact_ind.last_name'));
            $user->setProperty('email', $loginEmail);

            if (!$user->save()) {
                return false;
            }
            
            if ($newUser) {
                $this->setProperty('source_user_id', $user->getId());
                if (!$this->save()) {
                    return false;
                }
            }

            $roleId = filter_input(INPUT_POST, 'role_id');
            if (!empty($roleId)) {
                $role = RoleFactory::getModelById($roleId);
                if (empty($role)) {
                    return false;
                }
                if (!RoleFactory::linkRoleToUser($role, $user)) {
                    return false;
                }
            } else {
                if ($newUser) {
                    $parentContatOrg = $this->getParentContactOrg();
                    if (empty($parentContatOrg)) {
                        return false;
                    }
                    $contactCat = $parentContatOrg->getContactCat();
                    if (empty($contactCat)) {
                        return false;
                    }
                    $defaultRole = $contactCat->getNewUserDefaultRole();
                    if (!empty($defaultRole) && !RoleFactory::linkRoleToUser($defaultRole, $user)) {
                        return false;
                    }
                }
            }

            if ($avatarUploader) {
                $avatarUploader->setTargetFolder($user->getImageFolder());
                FolderFactory::putUploadedFilesInTargetFolder($avatarUploader);
            }


            if (empty($newUser) || (!empty($user->getId()) && ($user->getId() != Login::getUserId()))) {
                $permissionIds = explode(',', filter_input(INPUT_POST, 'permission_ids'));
                $desiredPermissions = array();
                foreach ($permissionIds as $permissionId) {
                    $permission = PermissionFactory::getModelById($permissionId);
                    if ($permission) {
                        $desiredPermissions[] = $permission;
                    }
                }
                PermissionFactory::adjustUserPermissions($user, $desiredPermissions);
            }
        }
        return true;
    }

    public function validateProfileForm(GI_Form $form, $step = 1) {
        if ($form->wasSubmitted() && $form->validate()) {
            $userId = NULL;
            $user = $this->getUser();
            if (!empty($user)) {
                $userId = $user->getId();
            }
            $errors = 0;
            $loginEmail = filter_input(INPUT_POST, 'login_email');
            if (!empty($userId)) {
                if (empty($loginEmail)) {
                    $errors += 1;
                    $form->addFieldError('login_email', 'empty', 'You must specify a login email address.');
                }
            }
            if (!empty($loginEmail)) {
                $userSearch = UserFactory::search();
                $userSearch->filter('email', $loginEmail);
                if (!empty($userId)) {
                    $userSearch->filterNotEqualTo('id', $userId);
                }
                $userSearch->setPageNumber(1)
                        ->setItemsPerPage(1);
                $conflictCount = $userSearch->count(true);
                if (!empty($conflictCount)) {
                    $errors += 1;
                    $form->addFieldError('login_email', 'conflict', 'This email address is already assigned to another person.');
                }
                
                $newPassword = filter_input(INPUT_POST, 'new_password');
                $repeatPassword = filter_input(INPUT_POST, 'repeat_password');
                
                if (!empty($newPassword) && !($newPassword === $repeatPassword)) {
                    $errors += 1;
                    $form->addFieldError('new_password', 'mismatch', 'The passwords do not match');
                }
                
            }
            if ($errors > 0) {
                return false;
            }
            return true;
        }
        return false;
    }

    public function validateSendConfirmEmailForm(GI_Form $form) {
        if ($form->wasSubmitted() && $form->validate()) {
            $errors = 0;

            $loginEmail = filter_input(INPUT_POST, 'login_email');
            
            $search = UserFactory::search();
            $search->filter('email', $loginEmail);
            
            $sourceUserId = $this->getProperty('source_user_id');
            if (!empty($sourceUserId)) {
                $search->filterNotEqualTo('id', $sourceUserId);
            }
            $count =  $search->count();
            if (!empty($count)) {
                $errors++;
                $form->addFieldError('login_email', 'duplicate_email', 'This email address is already being used by another person. Please enter a different one.');
            }
            
            if (!empty($errors)) {
                return false;
            }
            return true;
        }
        return false;
    }
    
    public function handleSendConfirmEmailForm(GI_Form $form, AbstractContactOrg $parentContactOrg) {
        if ($form->wasSubmitted() && $this->validateSendConfirmEmailForm($form)) {
            $contactCat = $parentContactOrg->getContactCat();
            if (empty($contactCat)) {
                return false;
            }
            $loginEmail = filter_input(INPUT_POST, 'login_email');
            $newUser = false;
            $user = $this->getUser();
            if (empty($user)) {
                $user = UserFactory::buildNewModel('unconfirmed');
            }
            $user->setProperty('first_name', $this->getProperty('contact_ind.first_name'));
            $user->setProperty('last_name', $this->getProperty('contact_ind.last_name'));
            if (empty($user->getId())) {
                $newUser = true;
                $user->setProperty('confirmed',0);
                $user->setProperty('alert_by_email', 0);
                $user->setProperty('alert_by_sms', 0);
                $user->setProperty('bos_admin', 0);
                $user->setProperty('force_pass_reset', 0);
            }
            
            //set email
            $user->setProperty('email', $loginEmail);
            
            if (!$user->save()) {
                return false;
            }
            
            if ($newUser) {
                $this->setProperty('source_user_id', $user->getId());
                if (!$this->save()) {
                    return false;
                }
                $roleId = filter_input(INPUT_POST, 'role_id');
                $role = NULL;
                if (!empty($roleId)) {
                    $role = RoleFactory::getModelById($roleId);
                } 
                if (empty($role)) {
                    $role = $contactCat->getNewUserDefaultRole();
                }
                if (empty($role)) {
                    return false;
                }
                if (!RoleFactory::linkRoleToUser($role, $user)) {
                    return false;
                }
            }
            
            if (!$user->sendConfirmEmailAddressEmail()) {
                return false;
            }
            
            return true;
        }
        return false;
    }
    
    public function getLoginEmail() {
        $user = $this->getUser();
        if (!empty($user)) {
            return $user->getProperty('email');
        }
        return '';
    }
    
    public function getSendConfirmEmailFormView(GI_Form $form, AbstractContactOrg $parentContactOrg) {
        return new ContactIndConfirmEmailFormView($form, $this, $parentContactOrg);
    }

    public function getProfileViewURLAttrs() {
        $parentContactOrg = $this->getParentContactOrg();
        if (!empty($parentContactOrg)) {
            $attrs = $parentContactOrg->getProfileViewURLAttrs();
            $attrs['tab'] = 'people';
            return $attrs;
        }
        return NULL;
    }

    public function getProfileMySettingsDetailView() {
        return new ContactProfileMySettingsDetailView($this);
    }

    public function isSuspendable() {
        $parentContactOrg = $this->getParentContactOrg();
        if (!empty($parentContactOrg) && $parentContactOrg->isSuspendable()) {
            return true;
        }
        return false;
    }

}
