<?php
/**
 * Description of AbstractContactOrgFranchise
 *
 * @author General Internet
 * @copyright  2017 General Internet
 * @version    3.0.0
 */
abstract class AbstractContactOrgFranchise extends AbstractContactOrg {
    
    protected $franchiseOwners;
    protected $franchisePrimaryOwner;
    
    public function getFranchiseOwners() {
        if (empty($this->franchiseOwners)) {
            $contactTableName = ContactFactory::getDbPrefix() . 'contact';
            $search = ContactFactory::search();
            $search->join('contact_relationship', 'c_contact_id', $contactTableName, 'id', 'REL')
                    ->join('contact_relationship_type', 'id', 'REL', 'contact_relationship_type_id', 'RELT')
                    ->filter('RELT.ref', 'franchise_owner')
                    ->filter('REL.p_contact_id', $this->getProperty('id'))
                    ->orderBy('REL.id');
            $this->franchiseOwners = $search->select();
        }
        return $this->franchiseOwners;
    }
    
    public function getFranchisePrimaryOwner() {
        if (empty($this->franchisePrimaryOwner)) {
            $franchiseOwners = $this->getFranchiseOwners();
            if (!empty($franchiseOwners)) {
                $this->franchisePrimaryOwner = $franchiseOwners[0];
            }
        }
        return $this->franchisePrimaryOwner;
    }

    public function getViewTitle($plural = true) {
        if (!Permission::verifyByRef('view_franchises')) {
            return parent::getViewTitle($plural);
        }
        $title = 'Franchise';
        if ($plural) {
            $title .= 's';
        }
        return $title;
    }
    
    public function getIsViewable() {
        if (parent::getIsViewable()) {
            if (!Permission::verifyByRef('view_franchises')) {
                $userFranchise = Login::getCurrentFranchise();
                if (!empty($userFranchise)) {
                    if ($userFranchise->getProperty('id') == $this->getProperty('id')) {
                        return true;
                    }
                }
                return false;
            }
            return true;
        }
        return false;
    }

    public function getIsEditable() {
        if (parent::getIsEditable()) {
            if (!Permission::verifyByRef('edit_franchises')) {
                $userFranchise = Login::getCurrentFranchise();
                if (!empty($userFranchise)) {
                    if ($userFranchise->getProperty('id') == $this->getProperty('id')) {
                        return true;
                    }
                }
                return false;
            }
            return true;
        }
        return false;
    }
    
    public function getIsAddable() {
        if (parent::getIsAddable()) {
            if (!Permission::verifyByRef('add_franchises')) {
                return false;
            }
            return true;
        }
        return false;
    }

    public function getFormView(GI_Form $form) {
        $formView = new ContactOrgFranchiseFormView($form, $this);
        $this->setUploadersOnFormView($formView);
        return $formView;
    }
    
    public function getDetailView() {
        $view = new ContactOrgFranchiseDetailView($this);
        $view->setAddCategories(false);
        $view->setAddDiscounts(false);
        $view->setAddPurchaseOrders(false);
        $view->setAddSalesOrders(false);
        $view->setAddInternal(false);
        $view->setAddDefaultCurrency(false);
        $view->setAddInterestRates(false);
        if (!Permission::verifyByRef('franchise_head_office')) {
            $view->setAddContactEvents(false);
        }
        return $view;
    }

    public function handleFormSubmission($form, $pId = NULL) {
        if ($form->wasSubmitted() && $this->validateForm($form)) {
            if (!$this->setPropertiesFromForm($form)) {
                return false;
            }
            $uploader = $this->getUploader($form);
            $imgUploader = $this->getImageUploader($form);
            if (!$this->save()) {
                return false;
            }
            if (!$this->handleContactInfoFormSubmission($form)) {
                return false;
            }
            if ($uploader) {
                $uploader->setTargetFolder($this->getFolder());
                FolderFactory::putUploadedFilesInTargetFolder($uploader);
            }

            if ($imgUploader) {
                $imgUploader->setTargetFolder($this->getImageFolder());
                FolderFactory::putUploadedFilesInTargetFolder($imgUploader);
            }

            if (Permission::verifyByRef('add_franchises') || Permission::verifyByRef('edit_franchises')) {
                if (!$this->handleFranchiseOwnerFormSubmission($form)) {
                    return false;
                }
            }
            return true;
        }
        return false;
    }

    public function setPropertiesFromForm(GI_Form $form) {
        $title = filter_input(INPUT_POST, 'title');
        $this->setProperty('contact_org.title', $title);
        $dba = filter_input(INPUT_POST, 'doing_bus_as');
        $this->setProperty('contact_org.doing_bus_as', $dba);
        $colour = filter_input(INPUT_POST, 'colour');
        $currencyId = filter_input(INPUT_POST, 'default_currency_id');
        $this->setProperty('contact.colour', $colour);
        if (!empty($currencyId)) {
            $this->setProperty('default_currency_id', $currencyId);
        }
        $this->setProperty('use_default_rate', 1);
        return true;
    }
    
    public function validateForm(\GI_Form $form) {
        if (!($form->wasSubmitted() && $form->validate())) {
            return false;
        }
        if ($this->formValidated) {
            return true;
        }
        $addOwnerSection = filter_input(INPUT_POST, 'add_owner_section');
        if (empty($addOwnerSection)) {
            $this->formValidated = true;
            return $this->formValidated;
        }
        $editCredentials = (int) filter_input(INPUT_POST, 'edit_login_credentials');

        if ($editCredentials == 1) {
            $submittedPass = filter_input(INPUT_POST, 'owner_password');
            $confirmSubmittedPass = filter_input(INPUT_POST, 'owner_confirm_password');
            if ($submittedPass !== $confirmSubmittedPass) {
                $form->addFieldError('owner_confirm_password', 'password_mismatch', 'Passwords do not match');
            }
        }
        
        $existingOwner = filter_input(INPUT_POST, 'existing_owner');
        if($existingOwner){
            $existingOwnerId = filter_input(INPUT_POST, 'existing_owner_id');
            if(empty($existingOwnerId)){
                $form->addFieldError('existing_owner_id', 'required', 'Required field.');
            }
        } else {
            $firstName = filter_input(INPUT_POST, 'owner_first_name');
            $lastName = filter_input(INPUT_POST, 'owner_last_name');
            if(empty($firstName)){
                $form->addFieldError('owner_first_name', 'required', 'Required field.');
            }
            if(empty($lastName)){
                $form->addFieldError('owner_last_name', 'required', 'Required field.');
            }
        }

        if ($form->fieldErrorCount()) {
            return false;
        }
        $this->formValidated = true;
        return $this->formValidated;
    }
    
    protected function handleFranchiseOwnerFormSubmission(GI_Form $form) {
        if (!$form->wasSubmitted() && $this->validateForm($form)) {
            return false;
        }
        $addOwnerSection = filter_input(INPUT_POST, 'add_owner_section');
        if (empty($addOwnerSection)) {
            return true;
        }
        $existingOwner = filter_input(INPUT_POST, 'existing_owner');
        $ownerUser = NULL;
        if($existingOwner){
            $existingOwnerId = filter_input(INPUT_POST, 'existing_owner_id');
            $ownerUser = UserFactory::getModelById($existingOwnerId);
            if(empty($ownerUser)){
                return false;
            }
            $loginEmail = $ownerUser->getProperty('email');
            $firstName = $ownerUser->getProperty('first_name');
            $lastName = $ownerUser->getProperty('last_name');
            $phoneNum = '';
            $ownerContact = $ownerUser->getContact();
        } else {
            $ownerContact = $this->getFranchisePrimaryOwner();
            if($ownerContact){
                $ownerUser = $ownerContact->getUser();
            }
            $loginEmail = filter_input(INPUT_POST, 'owner_login_email');
            $firstName = filter_input(INPUT_POST, 'owner_first_name');
            $lastName = filter_input(INPUT_POST, 'owner_last_name');
            $phoneNum = filter_input(INPUT_POST, 'owner_phone_number');
            if (empty($ownerUser)) {
                $userSearch = UserFactory::search();
                $userSearch->filter('email', $loginEmail);
                $existingUserArray = $userSearch->select();
                if (!empty($existingUserArray)) {
                    $ownerUser = $existingUserArray[0];
                    //TODO - alert user that user already exists, and that system is assigning exisitng user as owner of the franchise
                } else {
                    $ownerUser = UserFactory::buildNewModel('user');
                }
            }
            $ownerUser->setProperty('first_name', $firstName);
            $ownerUser->setProperty('last_name', $lastName);
            $ownerUser->setProperty('language', 'english');
            $editCredentials = (int) filter_input(INPUT_POST, 'edit_login_credentials');

            if ($editCredentials == 1) {
                $submittedPass = filter_input(INPUT_POST, 'owner_password');
                $ownerUser->setProperty('email', $loginEmail);
                $salt = $ownerUser->generateSalt();
                $saltyPass = $ownerUser->generateSaltyPass($submittedPass, $salt);
                $ownerUser->setProperty('pass', $saltyPass);
                $ownerUser->setProperty('salt', $salt);
            }
        }
        
        if (empty($ownerContact)) {
            $ownerContact = ContactFactory::buildNewModel('ind');
        }
        
        $franchiseId = $this->getId();
        
        $ownerUser->setProperty('franchise_id', $franchiseId);
        
        if (!$ownerUser->save()) {
            return false;
        }

        $ownerContact->setProperty('default_currency_id', $this->getProperty('default_currency_id'));
        $ownerContact->setProperty('franchise_id', $franchiseId);
        $ownerContact->setProperty('source_user_id', $ownerUser->getId());
        $ownerContact->setProperty('internal', 1);
        if(!$existingOwner){
            $ownerContact->setProperty('contact_ind.first_name', $firstName);
            $ownerContact->setProperty('contact_ind.last_name', $lastName);
        }
        
        if (!$ownerContact->save()) {
            return false;
        }

        $internalContactCat = $ownerContact->getContactCatModelByType('internal');
        if (empty($internalContactCat)) {
            $internalContactCat = ContactCatFactory::buildNewModel('internal');
            $internalContactCat->setProperty('contact_id', $ownerContact->getProperty('id'));
            if (!$internalContactCat->save()) {
                return false;
            }
        }
        
        $phoneNumberModel = $ownerContact->getContactInfo('phone_num');
        if (empty($phoneNumberModel)) {
            $phoneNumberModel = ContactInfoFactory::buildNewModel('phone_num');
            $phoneNumberModel->setProperty('contact_id', $ownerContact->getProperty('id'));
        }
        $phoneNumberModel->setProperty('contact_info_phone_num.phone', $phoneNum);
        if (!$phoneNumberModel->save()) {
            return false;
        }

        $relationshipModel = ContactRelationshipFactory::establishRelationship($this, $ownerContact, 'franchise_owner', false);
        if (empty($relationshipModel)) {
            return false;
        }
        $relationshipModel->setProperty('title', 'Owner');
        if (!$relationshipModel->save()) {
            return false;
        }

        $roleSearch = RoleFactory::search();
        $roleSearch->filter('title', 'Franchise Admin');
        $roleArray = $roleSearch->select();
        if (empty($roleArray)) {
            return false;
        }
        $franchiseAdminRole = $roleArray[0];
        $currentUserRole = $ownerUser->getRole();
        if($currentUserRole && $currentUserRole->getId() != $franchiseAdminRole->getId()){
            //@todo if the user already has a role that is NOT franchise admin, do something different? add permissions?
        } else {
            if (!RoleFactory::linkRoleToUser($franchiseAdminRole, $ownerUser)) {
                return false;
            }
        }

        return true;
    }
    
        /**
     * @param string $contactTypeRef
     * @return array
     */
    public function getBreadcrumbs($contactTypeRef = NULL) {
        $breadcrumbs = array();
        if (empty($contactTypeRef)) {
             $contactTypeRef = $this->getTypeRef();
        }
        if($contactTypeRef != 'contact'){
            $breadcrumbs[] = array(
                'label' => 'Head Office',
            );
        }
        $viewTitle = $this->getViewTitle();
        if (!Permission::verifyByRef('view_franchise_index')) {
            $contactTypeRef = 'org';
            $sampleOrg = ContactFactory::buildNewModel($contactTypeRef);
            $viewTitle = $sampleOrg->getViewTitle();
        }
        $bcLink = GI_URLUtils::buildURL(array(
            'controller' => 'contact',
            'action' => 'index',
            'type' => $contactTypeRef
        ));
        $breadcrumbs[] = array(
            'label' => $viewTitle,
            'link' => $bcLink
        );
        $contactId = $this->getId();
        if (!is_null($contactId)) {
            $breadcrumbs[] = array(
                'label' => $this->getName(),
                'link' => $this->getViewURL()
            );
        }
        return $breadcrumbs;
    }
    
    public function save() {
        if (parent::save()) {
            if (!($this->markAsInternal() && $this->setFranchiseId() && parent::save())) {
                return false;
            }
            return true;
        }
        return false;
    }
    
    protected function setFranchiseId() {
        if (empty($this->getId())) {
            false;
        }
        $this->setProperty('franchise_id', $this->getId());
        return true;
    }

}
