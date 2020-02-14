<?php

/**
 * Description of AbstractContactOrg
 *
 * @author General Internet
 * @copyright  2019 General Internet
 * @version    4.1.0
 */
abstract class AbstractContactOrg extends AbstractContact {
    /* @var AbstractContactInd */

    protected $primaryIndividual = NULL;
    protected $currentUserIsLinkedToThis = NULL;
    protected $childContactInds = NULL;
    
    protected $publicEmailModel = NULL;
    protected $publicAddressModel = NULL;
    protected $publicPhoneModel = NULL;

    /**
     * @return AbstractContactInd - main individual linked to this company
     */
    public function getPrimaryIndividual() {
        if (empty($this->primaryIndividual)) {
            $this->primaryIndividual = ContactFactory::getModelById($this->getProperty('contact_org.primary_individual_id'));
        }
        return $this->primaryIndividual;
    }

    public function getPrimaryIndividualName() {
        $primaryIndividual = $this->getPrimaryIndividual();
        if (!empty($primaryIndividual)) {
            return $primaryIndividual->getName();
        }
        return '';
    }

    public function setPrimaryIndividual(AbstractContactInd $contactInd) {
        $this->setProperty('contact_org.primary_individual_id', $contactInd->getId());
        $this->primaryIndividual = $contactInd;
    }

    /** @return AbstractContactInd[] */
    public function getChildContactInds() {
        if (is_null($this->childContactInds)) {
            $search = ContactFactory::search();
            $tableName = $search->prefixTableName('contact');
            $search->join('contact_relationship', 'c_contact_id', $tableName, 'id', 'REL');
            $search->join('contact_type', 'id', $tableName, 'contact_type_id', 'TYPE');

            $search->filter('REL.p_contact_id', $this->getId())
                    ->filter('TYPE.ref', 'ind');

            $search->groupBy('id')
                    ->orderBy('REL.id', 'ASC');
            $this->childContactInds = $search->select();
        }
        return $this->childContactInds;
    }

    public function addCustomFiltersToDataSearch(GI_DataSearch $dataSearch) {
        $dataSearch = parent::addCustomFiltersToDataSearch($dataSearch);
        if (ProjectConfig::getIsFranchisedSystem() && !Permission::verifyByRef('view_franchise_index')) {
            static::addFranchiseFilterToDataSearch($dataSearch);
        }
        return $dataSearch;
    }

    public static function addFranchiseFilterToDataSearch(GI_DataSearch $dataSearch) {
        $franchiseType = TypeModelFactory::getTypeModelByRef('franchise', 'contact_org_type');
        $currentFranchise = Login::getCurrentFranchise();
        if (!empty($currentFranchise)) {
            $dataSearch->filterGroup()
                    ->filter('id', $currentFranchise->getProperty('id'))
                    ->orIf()
                    ->filterNotEqualTo('org.contact_org_type_id', $franchiseType->getProperty('id'))
                    ->closeGroup()
                    ->andIf();
        } else {
            $dataSearch->filterNotEqualTo('org.contact_org_type_id', $franchiseType->getProperty('id'));
        }
        return $dataSearch;
    }

    public static function addNameFilterToDataSearch($name, \GI_DataSearch $dataSearch, $contactTableAlias = NULL) {
        $filterColumns = array(
            'org.title',
            'org.doing_bus_as'
        );
        if (ProjectConfig::getContactUseFullyQualifiedName()) {
            $fqnCol = 'fully_qualified_name';
            if (!empty($contactTableAlias)) {
                $fqnCol = $contactTableAlias . '.' . $fqnCol;
            }
            $filterColumns[] = $fqnCol;
        }
        $dataSearch->filterGroup()
                ->filterTermsLike($filterColumns, $name)
                ->filter('org.status', 1)
                ->closeGroup();

        $dataSearch->orderByLikeScore($filterColumns, $name);

        parent::addNameFilterToDataSearch($name, $dataSearch, $contactTableAlias);
    }

    public function getFormView(GI_Form $form) {
        $formView = new ContactOrgFormView($form, $this);
        $this->setUploadersOnFormView($formView);
        return $formView;
    }

    public function getDetailView() {
        $detailView = new ContactOrgDetailView($this);
        return $detailView;
    }

    public function getSummaryView($relationship = NULL) {
        $summaryView = new ContactOrgSummaryView($this, $relationship);
        return $summaryView;
    }

    public function setPropertiesFromForm(\GI_Form $form) {
        parent::setPropertiesFromForm($form);
        $title = filter_input(INPUT_POST, 'title');
        $doingBusAs = filter_input(INPUT_POST, 'doing_bus_as');
        $this->setProperty('contact_org.title', $title);
        $this->setProperty('contact_org.doing_bus_as', $doingBusAs);
        return true;
    }

    public function handleFormSubmission($form, $pId = NULL) {
        $newModel = false;
        if (empty($this->getProperty('id'))) {
            $newModel = true;
        }
        $saved = parent::handleFormSubmission($form, $pId);
        if (!$saved) {
            return false;
        }
        if ($saved && $newModel && !Permission::verifyByRef('view_contacts')) {
            $assignedToContact = AssignedToContactFactory::buildNewModel('assigned_to');
            $assignedToContact->setProperty('contact_id', $this->getProperty('id'));
            $assignedToContact->setProperty('user_id', Login::getUserId());
            if (!$assignedToContact->save()) {
                return false;
            }
        }
        return $saved;
    }

    public function getPhoneNumber() {
        $phoneNumber = '';
        $contactInfoArray = ContactInfoFactory::getContactInfosByContact($this, 'phone_num');
        if (!empty($contactInfoArray)) {
            $contactInfo = $contactInfoArray[0];
            $phoneNumber = $contactInfo->getProperty('contact_info_phone_num.phone');
        }
        return $phoneNumber;
    }

    public function getName() {
        $doingBusAs = $this->getProperty('contact_org.doing_bus_as');
        if (!empty($doingBusAs)) {
            return $doingBusAs;
        }
        $title = $this->getProperty('contact_org.title');
        return $title;
    }

    public function getRealName() {
        $title = $this->getProperty('contact_org.title');
        return $title;
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
            //Title
            array(
                'header_title' => 'Title',
                'method_name' => 'getTitle',
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
        );
        $UITableCols = array();
        foreach ($tableColArrays as $tableColArray) {
            $UITableCols[] = UITableCol::buildUITableColFromArray($tableColArray);
        }
        return $UITableCols;
    }

    public function getTitle($dbaHasPriority = true) {
        $dba = $this->getProperty('contact_org.doing_bus_as');
        if ($dbaHasPriority && !empty($dba)) {
            return $dba;
        }
        $title = $this->getProperty('contact_org.title');
        return $title;
    }

    public function getViewTitle($plural = true) {
        $title = 'Organization';
        if ($plural) {
            $title .= 's';
        }
        return $title;
    }

//    public function getLinkedContactsDetailView($linkedTypeRefs = NULL) {
//        if (empty($linkedTypeRefs)) {
//            $linkedTypeRefs = array(
//                'ind' => 'child',
//                'loc' => 'child',
//            );
//        }
//        return parent::getLinkedContactsDetailView($linkedTypeRefs);
//    }

    public function getContactRelationshipsDetailView($linkedTypeRefs = NULL) {
        if (empty($linkedTypeRefs)) {
            $linkedTypeRefs = array(
                'ind' => 'child',
                'loc' => 'child',
            );
        }
        return parent::getContactRelationshipsDetailView($linkedTypeRefs);
    }

    protected function setUserValues(AbstractUser $user) {
        $title = $this->getProperty('contact_org.title');
        $doingBusAs = $this->getProperty('contact_org.doing_bus_as');
        if (empty($doingBusAs)) {
            $doingBusAs = $title;
        }
        $user->setProperty('first_name', $doingBusAs);
        $user->setProperty('last_name', '');
        return parent::setUserValues($user);
    }

    public function getEmailAddress($role = 'primary') {
        $contactInfos = AbstractContactInfoFactory::getContactInfosByContact($this, 'email_address', false, $role);
        if (!empty($contactInfos)) {
            $contactInfo = $contactInfos[0];
            $emailAddress = $contactInfo->getProperty('contact_info_email_addr.email_address');
            return $emailAddress;
        }
        return NULL;
    }

    public function isOrganization() {
        return true;
    }

    protected function handleImportFromQBFormFields(GI_Form $form) {
        if (parent::handleImportFromQBFormFields($form)) {
            $contactQB = $this->getContactQB();
            if (empty($contactQB)) {
                return false;
            }
            $company = $contactQB->getProperty('company');
            $this->setProperty('contact_org.title', $company);
            return true;
        }
        return false;
    }

    protected function handleExportToQBFormFields(GI_Form $form, AbstractContactQB $contactQB) {
        if (parent::handleExportToQBFormFields($form, $contactQB)) {
            $contactQB->setProperty('company', $this->getProperty('contact_org.title'));
            $contactQB->setProperty('display_name', $this->getProperty('contact_org.title'));
            return true;
        }
        return false;
    }

    public function getIconClass() {
        return strtolower($this->getTypeTitle());
    }

    /** @return string */
    public function getPublicViewURL() {
        $contactCat = $this->getContactCat();
        if (empty($contactCat)) {
            return NULL;
        }
        return $contactCat->getPublicViewURL();
    }

    /** @return \ContactPublicDetailView */
    public function getPublicDetailView() {
        return parent::getPublicDetailView();
    }

    /*     * * Profile */

    public function getProfileDetailView() {
        $contactCat = $this->getContactCat();
        if (!empty($contactCat)) {
            return $contactCat->getProfileDetailView();
        }
        return NULL;
    }

    protected function getProfileFormViewObject(GI_Form $form, $curStep = 1) {
        $contactCat = $this->getContactCat();
        if (!empty($contactCat)) {
            return $contactCat->getProfileFormView($form, false, $curStep);
        }
        return NULL;
    }

    /**
     * @param GI_Form $form
     * @param type $step
     * @return boolean
     */
    public function handleProfileFormSubmission(GI_Form $form, $step = 1) {
        if ($form->wasSubmitted() && $this->validateProfileForm($form, $step)) {
            switch ($step) {
                case 10:
                    return $this->handleProfileBasicFormSubmission($form);
                case 40:
                    return $this->handleMySettingsFormSubmission($form);
                default:
                    $contactCat = $this->getContactCat();
                    if (!empty($contactCat)) {
                        return $contactCat->handleProfileFormSubmission($form, $step);
                    }
                    break;
            }
        }
        return false;
    }

    protected function handleProfileBasicFormSubmission(GI_Form $form) {
        $contactCat = $this->getContactCat();
        if (empty($contactCat)) {
            return false;
        }
        $companyName = trim(filter_input(INPUT_POST, 'company_name'));
        $displayName = trim(filter_input(INPUT_POST, 'display_name'));
        $firstName = trim(filter_input(INPUT_POST, 'first_name'));
        $lastName = trim(filter_input(INPUT_POST, 'last_name'));

        $this->setProperty('display_name', $displayName);
        $this->setProperty('contact_org.title', $companyName);

        $primaryIndividual = $this->getPrimaryIndividual();
        if (!empty($firstName) || !empty($lastName)) {
            if (empty($primaryIndividual)) {
                $primaryIndividual = ContactFactory::buildNewModel('ind');
            }
        }
        if (!empty($primaryIndividual)) {
            $primaryIndividual->setProperty('contact_ind.first_name', $firstName);
            $primaryIndividual->setProperty('contact_ind.last_name', $lastName);
            if (!$primaryIndividual->save()) {
                return false;
            }
            $this->setPrimaryIndividual($primaryIndividual);
        }
        if (empty($this->getId()) && !$this->setDefaultAdvancedSettings()) {
            return false;
        }
        if (!$this->save()) {
            return false;
        }

        if (!empty($primaryIndividual) && !ContactFactory::linkContactAndContact($this, $primaryIndividual)) {
            return false;
        }

        $contactCat->setProperty('contact_id', $this->getId());
        if (!$contactCat->save()) {
            return false;
        }

        if (!$this->handleContactInfoFormSubmission($form)) {
            return false;
        }
        return true;
    }

    protected function handleMySettingsFormSubmission(GI_Form $form) {
        $contactInd = ContactFactory::getIndividualByParentOrgAndUser($this, Login::getUser());
        if (empty($contactInd)) {
            return false;
        }
        if (!$contactInd->handleProfileFormSubmission($form)) {
            return false;
        }
        return true;
    }

    public function validateProfileForm(GI_Form $form, $step = 1) {
        switch ($step) {
            case 10:
                return $this->validateProfileBasicForm($form);
            case 40:
                return $this->validateProfileMySettingsForm($form);
            default:
                $contactCat = $this->getContactCat();
                if (!empty($contactCat)) {
                    return $contactCat->validateProfileForm($form, $step);
                }
                break;
        }
        return false;
    }

    protected function validateProfileBasicForm(GI_Form $form) {
        if ($form->wasSubmitted() && $form->validate()) {
            return true;
        }
        return false;
    }

    protected function validateProfileMySettingsForm(GI_Form $form) {
        if ($form->wasSubmitted() && $form->validate()) {
            $contactInd = ContactFactory::getIndividualByParentOrgAndUser($this, Login::getUser());
            if (empty($contactInd)) {
                return false;
            }
            if (!$contactInd->validateProfileForm($form)) {
                return false;
            }
            return true;
        }
        return false;
    }

    public function getProfileUICardView() {
        return new ContactOrgProfileUICardView($this);
    }

    public function getPublicProfileUICardView() {
        $contactCat = $this->getContactCat();
        if (!empty($contactCat)) {
            $cardView = $contactCat->getPublicProfileUICardView();
            if (!empty($cardView)) {
                return $cardView;
            }
        }
        return parent::getPublicProfileUICardView();
    }

    public function getIsUserLinkedToThis(AbstractUser $user = NULL) {
        if (empty($user)) {
            if (!is_null($this->currentUserIsLinkedToThis)) {
                return $this->currentUserIsLinkedToThis;
            }
            $user = Login::getUser();
            $currentUserUsed = true;
        } else {
            $currentUserUsed = false;
        }
        if (empty($user)) {
            return false;
        }

        $search = ContactRelationshipFactory::search();
        $tableName = $search->prefixTableName('contact_relationship');
        $search->filter('p_contact_id', $this->getId());
        $search->join('contact', 'id', $tableName, 'c_contact_id', 'CON');
        $search->join('contact_type', 'id', 'CON', 'contact_type_id', 'TYPE');

        $search->filter('CON.source_user_id', $user->getId())
                ->filter('TYPE.ref', 'ind');

        $search->setPageNumber(1)
                ->setItemsPerPage(1);
        $count = $search->count();
        if (!empty($count)) {
            if ($currentUserUsed) {
                $this->currentUserIsLinkedToThis = true;
            }
            return true;
        }
        if ($currentUserUsed) {
            $this->currentUserIsLinkedToThis = false;
        }
        return false;
    }

    public function getIsViewable() {
//        if (Permission::verifyByRef('view_contacts') || $this->getProperty('uid') == Login::getUserId()) {
//            return true;
//        }
//        if ($this->getIsUserLinkedToThis()) {
//            return true;
//        }
//        return parent::getIsViewable();

        if ($this->getIsUserLinkedToThis()) {
            return true;
        }
        $contactCat = $this->getContactCat();
        if (!empty($contactCat)) {
            return $contactCat->isViewable();
        }
        return parent::getIsViewable();
    }

    public function getIsEditable() {
        if (Permission::verifyByRef('edit_contacts') || $this->getProperty('uid') == Login::getUserId()) {
            return true;
        }
        if ($this->getIsUserLinkedToThis()) {
                return true;
            }
            return parent::getIsEditable();
    }
    
    /** @return AbstractContactOrgProfilePublicProfileDetailView */
    public function getPublicProfileDetailView() {
        $cat = $this->getContactCat();
        if (!empty($cat)) {
            $view = $cat->getPublicProfileDetailView();
        }
        if (empty($view)) {
            $view = new ContactOrgProfilePublicProfileDetailView($this);
        }
        return $view;
    }

    public function getPublicProfileFormView(GI_Form $form) {
        $cat = $this->getContactCat();
        if (!empty($cat)) {
            $view = $cat->getPublicProfileFormView($form);
        }
        if (empty($view)) {
            $view = new ContactOrgProfilePublicProfileFormView($form, $this);
        }
        return $view;
    }
    
    public function getPublicProfileAccentColour() {
        $colour = $this->getProperty('contact_org.pub_accent_colour');
        if (!empty($colour)) {
            return $colour;
        }
        return GI_Colour::getRandomColour();
    }
    
    public function getPublicProfileBusinessName($usePrivateIfNull = false) {
        $publicName = $this->getProperty('contact_org.pub_biz_name');
        if (!empty($publicName)) {
            return $publicName;
        }
        if ($usePrivateIfNull) {
            return $this->getProperty('display_name');
        }
        return '';
    }
    
    public function getPublicProfileOwnerName($usePrivateIfNull = false) {
        $ownerName = $this->getProperty('contact_org.pub_owner_name');
        if (!empty($ownerName)) {
            return $ownerName;
        }
        if ($usePrivateIfNull) {
            return $this->getPrimaryIndividualName();
        }
        return '';
    }
    
    public function getPublicProfileWebsiteURL() {
        return $this->getProperty('contact_org.pub_website_url');
    }
    
    public function getPublicProfileVideoURL() {
        return $this->getProperty('contact_org.pub_video_url');
    }
    
    public function getPublicProfileBusinessDescription() {
        return $this->getProperty('contact_org.pub_biz_description');
    }

    public function getPublicAddressModel($usePrivateIfNull = false) {
        if (empty($this->publicAddressModel)) {
            $id = $this->getProperty('contact_org.pub_address_id');
            $model = NULL;
            if (!empty($id)) {
                $model = ContactInfoFactory::getModelById($id);
            } else {
                $model = ContactInfoFactory::buildNewModel('address');
                if ($usePrivateIfNull) {
                    $privateModel = $this->getContactInfo('address');
                    if (!empty($privateModel)) {
                        $model->setPropertiesFromModel($privateModel);
                    }
                }
            }
            if (!empty($model)) {
                $model->setFieldPrefix('public_address');
                $this->publicAddressModel = $model;
            }
        }
        return $this->publicAddressModel;
    }

    public function getPublicEmailModel($usePrivateIfNull = false) {
        if (empty($this->publicEmailModel)) {
            $id = $this->getProperty('contact_org.pub_email_id');
            $model = NULL;
            if (!empty($id)) {
                $model = ContactInfoFactory::getModelById($id);
            } else {
                $model = ContactInfoFactory::buildNewModel('email_address');
                if ($usePrivateIfNull) {
                    $privateModel = $this->getContactInfo('email_address');
                    if (!empty($privateModel)) {
                        $model->setPropertiesFromModel($privateModel);
                    }
                }
            }
            if (!empty($model)) {
                $model->setFieldPrefix('public_email');
                $this->publicEmailModel = $model;
            }
        }
        return $this->publicEmailModel;
    }

    public function getPublicPhoneModel($usePrivateIfNull = false) {
        if (empty($this->publicPhoneModel)) {
            $id = $this->getProperty('contact_org.pub_phone_id');
            $model = NULL;
            if (!empty($id)) {
                $model = ContactInfoFactory::getModelById($id);
            } else {
                $model = ContactInfoFactory::buildNewModel('phone_num');
                if ($usePrivateIfNull) {
                    $privateModel = $this->getContactInfo('phone_num');
                    if (!empty($privateModel)) {
                        $model->setPropertiesFromModel($privateModel);
                    }
                }
            }
            if (!empty($model)) {
                $model->setFieldPrefix('public_phone');
                $this->publicPhoneModel = $model;
            }
        }
        return $this->publicPhoneModel;
    }

    /**
     * @param GI_Form $form
     * @return AbstractGI_Uploader
     */
    public function getPublicLogoUploader(GI_Form $form = NULL) {
        if ($this->getProperty('id')) {
            $appendName = 'edit_' . $this->getId();
        } else {
            $appendName = 'add';
        }
        $uploader = GI_UploaderFactory::buildImageUploader('contact_public_logo_' . $appendName);
        $uploader->setFilesLabel('Avatar');
        $uploader->setBrowseLabel('Upload Avatar');
        $folder = $this->getPublicLogoFolder();
        $uploader->setTargetFolder($folder);
        if (!empty($form)) {
            $uploader->setForm($form);
        }
        return $uploader;
    }

    public function getAddPersonURLAttrs() {
        return array(
            'controller' => 'contactprofile',
            'action' => 'addperson',
            'id'=>$this->getId(),
        );
    }
    
    protected function setDefaultAdvancedSettings() {
        return parent::setDefaultAdvancedSettings();
    }

    public function getIsProfileComplete() {
        if (!empty($this->getProperty('profile_complete'))) {
            return true;
        }
        return false;
    }
    
    public function setProfileIsComplete($isComplete = true) {
        if ($isComplete) {
            $this->setProperty('profile_complete', 1);
        } else {
            $this->setProperty('profile_complete', 0);
        }
    }

    public static function addNameFilterToProfileDataSearch($name, \GI_DataSearch $dataSearch, $contactTableAlias = NULL) {
        $filterColumns = array();
        if (empty($contactTableAlias)) {
            $filterColumns[] = 'display_name';
        } else {
            $filterColumns[] = $contactTableAlias . '.display_name';
        }
        $filterColumns[] = 'org.title';
        $filterColumns[] = 'org.doing_bus_as';
        if (ProjectConfig::getContactUseFullyQualifiedName()) {
            $fqnCol = 'fully_qualified_name';
            if (!empty($contactTableAlias)) {
                $fqnCol = $contactTableAlias . '.' . $fqnCol;
            }
            $filterColumns[] = $fqnCol;
        }
        
        if (empty($contactTableAlias)) {
            $contactTableName = $dataSearch->prefixTableName('contact');
        } else {
            $contactTableName = $contactTableAlias;
        }
        
       $relationShipJoin =  $dataSearch->createLeftJoin('contact_relationship', 'p_contact_id', $contactTableName, 'id', 'CONREL');
       $dataSearch->ignoreStatus('CONREL');
       $relationShipJoin->filter('CONREL.status', 1);
       $childContactJoin =  $dataSearch->createLeftJoin('contact', 'id', 'CONREL', 'c_contact_id', 'CIND_T');
       $dataSearch->ignoreStatus('CIND_T');
       $childContactJoin->filter('CIND_T.status', 1);
       $indChildContactJoin = $dataSearch->createLeftJoin('contact_ind', 'parent_id', 'CIND_T', 'id', 'CIND');
       $dataSearch->ignoreStatus('CIND');
       $indChildContactJoin->filter('CIND.status', 1);
       
       $filterColumns[] = 'CIND.first_name';
       $filterColumns[] = 'CIND.last_name';

        $dataSearch->filterGroup()
                ->filterTermsLike($filterColumns, $name)
                ->filter('org.status', 1)
                ->closeGroup();

        $dataSearch->orderByLikeScore($filterColumns, $name);

        parent::addNameFilterToProfileDataSearch($name, $dataSearch, $contactTableAlias);
    }

    //TODO - modify to use different helper functions
    //if does not have permission, user can view any contact org they either
    //created, are assigned to, or are a part of
    //they can also see all other 'child' contacts that are part of an org that they can see.
    public function addCustomFiltersToProfileDataSearch(GI_DataSearch $dataSearch) {
        if (!Permission::verifyByRef('view_contacts')) {
            if (!Permission::verifyByRef('view_contact_clients')) {
                $this->addClientRestrictionFiltersToProfileDataSearch($dataSearch);
            }

            if (!Permission::verifyByRef('view_contact_vendors')) {
                //Exclude vendors except for ones created by login id
                $this->addVendorRestrictionFiltersToProfileDataSearch($dataSearch);
            }

            if (!Permission::verifyByRef('view_contact_internals')) {
                //Exclude internal contacts except for ones created by login id
                $this->addInternalRestrictionFiltersToProfileDataSearch($dataSearch);
            }
        }
        
        $dataSearch->groupBy('id');
        return $dataSearch;
    }

    public function addClientRestrictionFiltersToProfileDataSearch(GI_DataSearch $dataSearch) {
        $userId = Login::getUserId();
        $sourceContact = ContactFactory::getBySourceUserId($userId);
        if (empty($sourceContact)) {
            GI_URLUtils::redirectToAccessDenied();
        }
        $clientType = TypeModelFactory::getTypeModelByRef('client', 'contact_cat_type');
        if (!$dataSearch->isJoinedWithTable('CCNOTCLIENT') || !$dataSearch->isJoinedWithTable('CCCLIENT') || !$dataSearch->isJoinedWithTable('CREL')) {
            $this->addContactCatJoinsToClientDataSearch($dataSearch);
        }

        $parentClientJoin = $dataSearch->createJoin('contact_cat', 'contact_id', 'CREL', 'p_contact_id', 'PCCCLIENT', 'left');
        $parentClientJoin->filter('PCCCLIENT.contact_cat_type_id', $clientType->getProperty('id'));
        $parentNonClientJoin = $dataSearch->createJoin('contact_cat', 'contact_id', 'CREL', 'p_contact_id', 'PCCNOTCLIENT', 'left');
        $parentNonClientJoin->filterNotEqualTo('PCCNOTCLIENT.contact_cat_type_id', $clientType->getProperty('id'));

        $dataSearch->filterGroup()
                ->filterGroup() //contact is a client and is assigned to user (CASE 1)
                    ->andIf()
                    ->filter('CCCLIENT.status', 1)
                    ->join('assigned_to_contact', 'contact_id', 'CCCLIENT', 'contact_id', 'ASSTO', 'left')
                    ->filter('ASSTO.user_id', $userId)
                    ->filter('ASSTO.status', 1)
                ->closeGroup() //close CASE 1
                    ->orIf()
                    ->filterGroup() //cases 2-4
                        ->filterGroup()//parent + mine + client (CASE 2)
                            ->andIf()
                            ->join('assigned_to_contact', 'contact_id', 'PCCCLIENT', 'contact_id', 'ASSTO2', 'left')
                            ->filter('CREL.status', 1)
                            ->filter('PCCCLIENT.status', 1)
                            ->filter('ASSTO2.status', 1)
                            ->filter('ASSTO2.user_id', $userId)
                        ->closeGroup()//close CASE 2
                        ->orIf()
                        ->filterGroup()//no parent + not client (CASE 3)
                            ->andIf()
                            ->filterNullOr('CREL.status')
                            ->filterNullOr('PCCCLIENT.status')
                            ->filter('CCNOTCLIENT.status', 1)
                            ->filterNullOr('CCCLIENT.status')
                        ->closeGroup()//close CASE 3
                        ->orIf()
                        ->filterGroup()  //no parent + no cat (CASE 4)
                            ->andIf()
                            ->filterNullOr('CREL.status')
                            ->filterNullOr('PCCCLIENT.status')
                            ->filterNullOr('CCNOTCLIENT.status')
                            ->filterNullOr('CCCLIENT.status')
                        ->closeGroup() //close no parent + no cat
                        ->orIf()
                        ->filterGroup() //parent + (parent + child) not client (CASE 5)
                            ->andIf()
                            ->filter('CREL.status', 1)
                            ->filter('PCCNOTCLIENT.status', 1)
                            ->filter('CCNOTCLIENT.status', 1)
                        ->closeGroup()
                        ->andIf()
                    ->closeGroup() //close cases 2-5
                ->orIf()
                ->filterGroup() // Created by login user (CASE 6)
                    ->filter('uid', $userId) // Created by login user
                ->closeGroup()
                ->orIf()
                ->filterNullOr('CCCLIENT.status') //for locations or other contacts that do not have a contact cat
                ->closeGroup()
                ->andIf();
        $dataSearch->groupBy('id');
        return $dataSearch;
    }

    public function addVendorRestrictionFiltersToProfileDataSearch(GI_DataSearch $dataSearch) {
        $userId = Login::getUserId();
        $vendorType = TypeModelFactory::getTypeModelByRef('vendor', 'contact_cat_type');
        if (!$dataSearch->isJoinedWithTable('VCONCAT')) {
            $this->addContactCatJoinsToVendorDataSearch($dataSearch);
        }
        $dataSearch->filterGroup()
                ->filterNotEqualTo('VCONCAT.contact_cat_type_id', $vendorType->getProperty('id'))
                ->orIf()
                ->filter('uid', $userId) // Created by login user
                ->closeGroup()
                ->andIf();
        return $dataSearch;
    }

    public function addInternalRestrictionFiltersToProfileDataSearch(GI_DataSearch $dataSearch) {
        $userId = Login::getUserId();
        $internalType = TypeModelFactory::getTypeModelByRef('internal', 'contact_cat_type');
        if(!$dataSearch->isJoinedWithTable('ICONCAT')){
            $this->addContactCatJoinsToInternalDataSearch($dataSearch);
        }
        $dataSearch->filterGroup()
                    ->filterNotEqualTo('ICONCAT.contact_cat_type_id', $internalType->getProperty('id'))
                    ->orIf()
                    ->filter('uid', $userId) // Created by login user
                ->closeGroup()
                ->andIf();
        return $dataSearch;
    }
    
        /**
     * @param string $term
     * @return array
     */
    public function getProfileAutocompResult($term = NULL, $useAddrBtn = false, $addressInfo = array()){
        $name = $this->getName();
        $displayName = $this->getDisplayName();
        $fullyQualifiedName = $this->getFullyQualifiedName();
        $autoResultName = GI_StringUtils::markTerm($term, $displayName);
        $typeTitle = $this->getTypeTitle();
        $contactCat = $this->getContactCat();
        
        //TODO - individual name
        $primaryIndividualName = $this->getPrimaryIndividualName();
        
        if($contactCat){
            $typeTitle = $contactCat->getTypeTitle();
        }
        $autoResult = '<span class="result_text">';
        $autoResult .= GI_StringUtils::getIcon($this->getIconClass());
        $autoResult .= '<span class="inline_block">';
        $autoResult .= $autoResultName;
        if (!empty ($name) && $name != $displayName) {
            $autoResult .= '<span class="sub">';
            $autoResult .= GI_StringUtils::markTerm($term, $name);
            $autoResult .= '</span>';
        }
        if (ProjectConfig::getContactUseFullyQualifiedName() && !empty($fullyQualifiedName) && $fullyQualifiedName != $displayName) {
            $autoResult .= '<span class="sub">';
            $autoResult .= '(' . GI_StringUtils::markTerm($term, $fullyQualifiedName) . ')';
            $autoResult .= '</span>';
        }
        if (!empty($primaryIndividualName) && $primaryIndividualName != $displayName) {
            $autoResult .= '<span class="sub">';
            $autoResult .= GI_StringUtils::markTerm($term, $primaryIndividualName);
            $autoResult .= '</span>';
        }
        $autoResult .= '<span class="sub">';
        $autoResult .= $typeTitle;
        $autoResult .= '</span>';
        $autoResult .= '</span>';

        $id = $this->getId();
        $result = array(
            'label' => $displayName,
            'value' => $id,
            'autoResult' => $autoResult
        );
        if ($useAddrBtn) {
            $addrTypeRef = NULL;
            if(isset($addressInfo['addrTypeRef'])){
                $addrTypeRef = $addressInfo['addrTypeRef'];
            }
            
            $addresses = $this->getContactInfoAddresses($addrTypeRef);
            if ($addresses) {
                $addrSelector = false;
                $addrPicker = '';
                if(count($addresses)>1){
                    $addrSelector = true;
                    
                    $addrPicker = '<div class="show_content_in_modal addr_picker_wrap">';
                        $addrPicker .= '<div class="modal_content medium_sized">';
                            $addrPicker .= '<h3 class="main_head">' . $name . ' Addresses</h3>';
                            $addrPicker .= '<div class="content_padding">';
                                $addrPicker .= '<div class="auto_columns">';
                                foreach($addresses as $address){
                                    if(isset($addressInfo['addrFieldPrefix'])){
                                        $address->setFieldPrefix($addressInfo['addrFieldPrefix']);
                                    }
                                    if(isset($addressInfo['addrFieldSuffix'])){
                                        $address->setFieldSuffix($addressInfo['addrFieldSuffix']);
                                    }
                                    $addrPicker .= $this->getUseAddrBtn($address, true);
                                }
                                $addrPicker .= '</div>';
                                $addrPicker .= '<div class="wrap_btns"><span class="other_btn close_gi_modal gray">Close</span></div>';
                            $addrPicker .= '</div>';
                        $addrPicker .= '</div>';
                        $addrPicker .= '<div class="modal_preview inline_block" title="Use Address">';
                            $addrPicker .= '<span class="inline_block addr_picker_btn">Pick Address</span>';
                        $addrPicker .= '</div>';
                    $addrPicker .= '</div>';
                }
                $address = $addresses[0];
                if(isset($addressInfo['addrFieldPrefix'])){
                    $address->setFieldPrefix($addressInfo['addrFieldPrefix']);
                }
                if(isset($addressInfo['addrFieldSuffix'])){
                    $address->setFieldSuffix($addressInfo['addrFieldSuffix']);
                }
                if ($addrSelector) {
                    $result['addrBtn'] = $addrPicker;
                } else {
                    $addrBtn = $this->getUseAddrBtn($address);
                    $result['addrBtn'] = $addrBtn;
                }

                $addressView = $address->getDetailView();
                $result['addrView'] = $addressView->getHTMLView();
            }
        }
        $defaultCurrency = $this->getDefaultCurrency();
        if (!empty($defaultCurrency)) {
            $result['cur_id'] = $defaultCurrency->getId();
        }
        return $this->addTaxValuesToAutocompResult($result);
    }

    /**
     * @param GI_DataSearch $dataSearch
     * @param GI_Form $form
     * @return boolean
     */
    public static function filterPublicSearchForm(GI_DataSearch $dataSearch, GI_Form $form = NULL) {
        $name = $dataSearch->getSearchValue('name');
        if (!empty($name)) {
            static::addNameFilterToPublicDataSearch($name, $dataSearch);
        }

        $address = $dataSearch->getSearchValue('address');
        if (!empty($address)) {
            static::addAddressFilterToPublicDataSearch($address, $dataSearch);
        }
        $tags = $dataSearch->getSearchValue('tags');
        if (!empty($tags)) {
            static::addTagsFilterToDataSearch($tags, $dataSearch);
        }

        if (!is_null($form) && $form->wasSubmitted() && $form->validate()) {
            $dataSearch->clearSearchValues();
            $dataSearch->setSearchValue('search_type', 'advanced');

            $name = filter_input(INPUT_POST, 'search_name');
            $dataSearch->setSearchValue('name', $name);

            $address = filter_input(INPUT_POST, 'search_address');
            $dataSearch->setSearchValue('address', $address);

            $tags = filter_input(INPUT_POST, 'search_tags', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY);
            $dataSearch->setSearchValue('tags', $tags);
        }

        return true;
    }

    public static function addNameFilterToPublicDataSearch($name, \GI_DataSearch $dataSearch, $contactTableAlias = NULL) {
        $filterColumns = array(
            'org.pub_biz_name',
            'org.pub_owner_name',
        );
        $dataSearch->filterGroup()
                ->filterTermsLike($filterColumns, $name)
                ->filter('org.status', 1)
                ->closeGroup();

        $dataSearch->orderByLikeScore($filterColumns, $name);
    }

    public static function addAddressFilterToPublicDataSearch($address, GI_DataSearch $dataSearch) {
        $contactTableName = $dataSearch->prefixTableName('contact');
        $dataSearch->join('contact_org', 'parent_id', $contactTableName, 'id', 'ORG_123');
        $columns = array(
            'ADDR.addr_street',
            'ADDR.addr_city',
            'ADDR.addr_code'
        );
        $dataSearch->leftJoin('contact_info', 'id', 'ORG_123', 'pub_address_id', 'INFOADDR')
                ->leftJoin('contact_info_address', 'parent_id', 'INFOADDR', 'id', 'ADDR')
                ->filterTermsLike($columns, $address)
                ->orderByLikeScore($columns, $address);
    }

    public function isSuspendable() {
        $contactCat = $this->getContactCat();
        if (!empty($contactCat) && $contactCat->isSuspendable()) {
            return true;
        }
        return false;
    }

    /**
     * 
     * @return AbstractContactApplication
     */
    public function getApplication() {
        if (empty($this->application)) {
            $this->application = ContactApplicationFactory::getModelByContactOrg($this);
        }
        return $this->application;
    }

}
