<?php

/**
 * Description of AbstractContactCat
 *
 * @author General Internet
 * @copyright  2019 General Internet
 * @version    4.1.0
 */
abstract class AbstractContactCat extends GI_Model {
    
    /** @var AbstractContact */
    protected $contact = NULL;
    
    protected $applicationRequired = true;
    protected $profileRequired = true;
    protected $usesPublicProfile = NULL;
    protected $usesPayment = NULL;
    
    protected static $newUserDefaultRoleSystemTitle = 'limited_user';
    protected static $isSuspendable = false;
    protected static $applicationTypeRef = 'application';


    /** @return AbstractContact */
    public function getContact() {
        if (empty($this->contact)) {
            $this->contact = ContactFactory::getModelById($this->getProperty('contact_id'));
        }
        return $this->contact;
    }
    
    public function setContact(AbstractContact $contact) {
        $this->contact = $contact;
        $this->setProperty('contact_id', $contact->getId());
    }
    
    /**
     * @param GI_Form $form
     * @return \ContactCatFormView
     */
    public function getFormView($form, $otherData = array()) {
        $formView = new ContactCatFormView($form, $this, $otherData);
        return $formView;
    }
    
    public function getDetailView() {
        return NULL;
    }
    
    /**
     * @param boolean $plural
     * @return string
     */
    public function getViewTitle($plural = true) {
        $title = 'Category';
        if ($plural) {
            $title = 'Categories';
        }
        return $title;
    }
    
    /**
     * @param GI_Form $form
     * @param AbstractContact $contact
     * @return \AbstractContactCat|boolean
     */
    public function handleFormSubmission(GI_Form $form, AbstractContact $contact) {
        if (!($form->wasSubmitted() && $this->validateForm($form))) {
            return false;
        }
        $this->setPropertiesFromForm($form);
        $this->setProperty('contact_id', $contact->getProperty('id'));
        if ($this->save()) {
            return $this;
        }
        return NULL;
    }

    public function setPropertiesFromForm(GI_Form $form) {
        return true;
    }

    /**
     * 
     * @param GI_DataSearch $dataSearch
     * @param string $type
     * @param array $redirectArray
     * @return \ContactCatSearchFormView
     */
    public static function getSearchForm(GI_DataSearch $dataSearch, $type = NULL, &$redirectArray = array()){
        $form = new GI_Form('contact_cat_search');
        if(!empty($type)){
            $dataSearch->setSearchValue('contact_cat_type', $type);
        }
        $searchView = static::getSearchFormView($form, $dataSearch);
        
        static::filterSearchForm($dataSearch, $form);
        
        if($form->wasSubmitted() && $form->validate()){
            $queryId = $dataSearch->getQueryId();
            
            if(empty($redirectArray)){
                $redirectArray = array(
                    'controller' => 'contact',
                    'action' => 'catIndex',
                    'targetId' => 'list_bar',
                );
                
                if(!empty($type)){
                    $redirectArray['type'] = $type;
                }
            }
            
            $redirectArray['queryId'] = $queryId;
            if(GI_URLUtils::getAttribute('ajax')){
                if(GI_URLUtils::getAttribute('redirectAfterSearch')){
                    //Set new Url for search
                    unset($redirectArray['ajax']);
                    $redirectArray['fullView'] = 1;
                    $redirectArray['newUrl'] = GI_URLUtils::buildURL($redirectArray);
                    $redirectArray['newUrlTargetId'] = 'list_bar';
                    $redirectArray['jqueryAction'] = 'clearMainPanel();';
                } else {
                    $redirectArray['ajax'] = 1;
                    GI_URLUtils::redirect($redirectArray);
                }
            } else {
                GI_URLUtils::redirect($redirectArray);
            }
        }
        return $searchView;
    }
  
    /**
     * @param GI_Form $form
     * @param GI_DataSearch $dataSearch
     * @return \ContactCatSearchFormView
     */
    protected static function getSearchFormView(GI_Form $form, GI_DataSearch $dataSearch = NULL){
        $searchValues = array();
        if($dataSearch){
            $searchValues = $dataSearch->getSearchValues();
        }
        $searchValues['queryId'] = $dataSearch->getQueryId();
        $searchView = new ContactCatSearchFormView($form, $searchValues);
        return $searchView;
    }
    
    /**
     * @param GI_DataSearch $dataSearch
     * @param GI_Form $form
     * @return boolean
     */
    protected static function filterSearchForm(GI_DataSearch $dataSearch, GI_Form $form = NULL){
        $contact = ContactFactory::buildNewModel('contact');
        
        return $contact->filterSearchForm($dataSearch, $form);
    }
    
    

    
    public static function getUITableCols() {
        $tableColArrays = array(
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
            ),
//            //Categories
//            array(
//                'header_title' => 'Categories',
//                'method_name' => 'getContactCatText',
//            )
        );
        $UITableCols = array();
        foreach ($tableColArrays as $tableColArray) {
            $UITableCols[] = UITableCol::buildUITableColFromArray($tableColArray);
        }
        return $UITableCols;
    }
    
    public static function getUIRolodexCols() {
        $tableColArrays = array(
            //Avatar
            array(
                'method_name' => 'getAvatarHTML',
                'css_class' => 'avatar_cell',
            ),
            //Name With Phone number
            array(
                'method_name' => 'getNameWithPhoneNumber',
            ),
            //Address
            array(
                'method_name' => 'getAddress',
            ),
        );
        $UIRolodexCols = array();
        foreach ($tableColArrays as $tableColArray) {
            $UIRolodexCols[] = UITableCol::buildUITableColFromArray($tableColArray);
        }
        return $UIRolodexCols;
    }

    public static function getProfileUIRolodexCols() {
        $tableColArrays = array(
            //Avatar
            array(
                'method_name' => 'getAvatarHTML',
                'css_class' => 'avatar_cell',
            ),
            array(
                'method_name' => 'getDisplayName',
            ),
            //Name With Phone number
            array(
                'method_name' => 'getNameWithPhoneNumber',
            ),
            //Address
            array(
                'method_name' => 'getAddress',
            ),
        );
        $UIRolodexCols = array();
        foreach ($tableColArrays as $tableColArray) {
            $UIRolodexCols[] = UITableCol::buildUITableColFromArray($tableColArray);
        }
        return $UIRolodexCols;
    }

    public function getBreadcrumbs(){
        $breadcrumbs = array();
        $bcIndexLink = GI_URLUtils::buildURL(array(
            'controller' => 'contact',
            'action' => 'catIndex'
        ));
        $breadcrumbs[] = array(
            'label' => 'All Contacts',
            'link' => $bcIndexLink
        );
        $curTypeRef = $this->getTypeRef();
        if(!empty($curTypeRef) && $curTypeRef != 'category'){
            $bcLink = GI_URLUtils::buildURL(array(
                'controller' => 'contact',
                'action' => 'catIndex',
                'type' => $curTypeRef
            ));
            $breadcrumbs[] = array(
                'label' => $this->getViewTitle(),
                'link' => $bcLink
            );
        }
        /*
        $curId = $this->getProperty('id');
        if (!is_null($curId)) {
            $breadcrumbs[] = array(
                'label' => $this->getTitle(),
                'link' => $this->getViewURL()
            );
        }
        */
        return $breadcrumbs;
    }
    
    public function getApplicationRequired() {
        return $this->applicationRequired;
    }
    
    public function getProfileRequired() {
        return $this->profileRequired;
    }
    
    public function validateForm(\GI_Form $form) {
        return parent::validateForm($form);
    }
    
    protected function getIsViewable() {
        if(Permission::verifyByRef('view_contacts') || Permission::verifyByRef('view_contact_'.$this->getTypeRef().'s')) {
            return true;
        }
        return false;
    }
    
    protected function getIsAddable() {
        if(Permission::verifyByRef('add_contacts') || Permission::verifyByRef('add_contact_'.$this->getTypeRef().'s')) {
            return true;
        }
        return false;
    }
    
    protected function getIsEditable() {
        //Note: internal has custom getIsEditable function
        if ($this->isAddable()) { //Addable permission is required
            if(Permission::verifyByRef('edit_contacts') || Permission::verifyByRef('edit_contact_'.$this->getTypeRef().'s')) {
                return true;
            }
        }
        return false;
    }
    
    protected function getIsDeleteable() {
        if(Permission::verifyByRef('delete_contacts') || Permission::verifyByRef('delete_contact_'.$this->getTypeRef().'s')) {
            return true;
        }
        return false;
    }

    public function isIndexViewable() {
        if(Permission::verifyByRef('view_contacts_index') || Permission::verifyByRef('view_contact_'.$this->getTypeRef().'_index')) {
            return true;
        }
        return false;
    }
    
    public function isEventViewable() {
        if(Permission::verifyByRef('view_c_events') || Permission::verifyByRef('view_c_'.$this->getTypeRef().'_events')) {
            return true;
        }
        return false;
    }
    
    public function isEventAddable() {
        if(Permission::verifyByRef('add_c_events') || Permission::verifyByRef('add_c_'.$this->getTypeRef().'_events')) {
            return true;
        }
        return false;
    }
    
    public function isEventEditable() {
        if ($this->isEventAddable()) { //Addable permission is required
            if(Permission::verifyByRef('edit_c_events') || Permission::verifyByRef('edit_c_'.$this->getTypeRef().'_events')) {
                return true;
            }
        }
        return false;
    } 
    
    public function isEventDeleteable() {
        if(Permission::verifyByRef('delete_c_events') || Permission::verifyByRef('delete_c_'.$this->getTypeRef().'_events')) {
            return true;
        }
        return false;
    }
    
    public function isEventIndexViewable() {
        if(Permission::verifyByRef('view_c_events_index') || Permission::verifyByRef('view_c_'.$this->getTypeRef().'_events_index')) {
            return true;
        }
        return false;
    }
    
    /**
     * 
     * @param AbstractContact $contact
     * @return boolean
     */
    public function isEditableByContact(AbstractContact $contact) {
        if (empty($contact->getId())) {
            return $this->isAddable();
        } else {
            return ($this->isEditable() || $contact->getProperty('uid') == Login::getUserId());
        }
    }
    
    /**
     * @param Mixed[] $contactAutoCompResult
     * @return Mixed[]
     */
    public function addDataToContactAutoCompResult($contactAutoCompResult) {
        return $contactAutoCompResult;
    }
    
    public function getIndexURLAttrs($withPageNumber = false){
        $indexURLAttributes = array(
            'controller' => 'contact',
            'action' => 'catIndex',
            'type' => $this->getTypeRef(),
        );
        $attributes = GI_URLUtils::getAttributes();
        if (isset($attributes['queryId'])) {
            $indexURLAttributes['queryId'] = $attributes['queryId'];
        }
        if ($withPageNumber && isset($attributes['pageNumber'])) {
            $indexURLAttributes['pageNumber'] = $attributes['pageNumber'];
        }
        return $indexURLAttributes;
    }
    
    public function getListBarURL($otherAttributes = NULL) {
        if (!$this->isIndexViewable()) {
            return NULL;
        }
        $listURLAttributes = $this->getIndexURLAttrs();
        $listURLAttributes['targetId'] = 'list_bar';
        $listURLAttributes['fullView'] = 1;
        $contact = $this->getContact();
        if (!empty($contact)) {
            $listURLAttributes['curId'] = $contact->getId();
        }
        if (isset($otherAttributes['type'])) {
            //overrite type
            $listURLAttributes['type'] = $otherAttributes['type'];
        }
        
        return GI_URLUtils::buildURL($listURLAttributes);
    }
    
    /**
     * 
     * @param GI_DataSearch $dataSearch
     * @param string $type
     * @param array $redirectArray
     * @return \ContactCatSearchFormView
     */
    public static function getQnASearchForm(GI_DataSearch $dataSearch, $type = NULL, &$redirectArray = array()){
        $form = new GI_Form('qna_contact_cat_search');
        if(!empty($type)){
            $dataSearch->setSearchValue('contact_cat_type', $type);
        }
        $searchView = static::getQnASearchFormView($form, $dataSearch);
        
        static::filterQnASearchForm($dataSearch, $form);
        
        if($form->wasSubmitted() && $form->validate()){
            $queryId = $dataSearch->getQueryId();
            
            if(empty($redirectArray)){
                $redirectArray = array(
                    'controller' => 'qna',
                    'action' => 'indexVendor',
                );
                
                if(!empty($type)){
                    $redirectArray['type'] = $type;
                }
            }
            
            $redirectArray['queryId'] = $queryId;
            if(GI_URLUtils::getAttribute('ajax')){
                if(GI_URLUtils::getAttribute('redirectAfterSearch')){
                    //Set new Url for search
                    unset($redirectArray['ajax']);
                    $redirectArray['fullView'] = 1;
                    $redirectArray['newUrl'] = GI_URLUtils::buildURL($redirectArray);
                    $redirectArray['newUrlRedirect'] = 1;
                } else {
                    $redirectArray['ajax'] = 1;
                    GI_URLUtils::redirect($redirectArray);
                }
            } else {
                GI_URLUtils::redirect($redirectArray);
            }
        }
        return $searchView;
    }
  
    /**
     * @param GI_Form $form
     * @param GI_DataSearch $dataSearch
     * @return \ContactCatSearchFormView
     */
    protected static function getQnASearchFormView(GI_Form $form, GI_DataSearch $dataSearch = NULL){
        $searchValues = array();
        if($dataSearch){
            $searchValues = $dataSearch->getSearchValues();
        }
        $searchValues['queryId'] = $dataSearch->getQueryId();
        $searchView = new QnAContactSearchFormView($form, $searchValues);
        return $searchView;
    }
    
    /**
     * @deprecated - //TODO -remove
     * @param GI_DataSearch $dataSearch
     * @param GI_Form $form
     * @return boolean
     */
    public static function filterQnASearchForm(GI_DataSearch $dataSearch, GI_Form $form = NULL){
        $searchType = $dataSearch->getSearchValue('search_type');
        if (empty($searchType) || $searchType === 'basic') {
            //Basic Search
            $basicSearchField = $dataSearch->getSearchValue('basic_search_field');
            if(!empty($basicSearchField)){
                static::addQnABasicSearchFieldFilterToDataSearch($basicSearchField, $dataSearch);
            }
        } else {
            //Advanced Search
            $allSearchTagArray = array();
            $tags = $dataSearch->getSearchValue('tags');
            if (!empty($tags)) {
                $allSearchTagArray = explode(',', $tags);
            }

            $locTags = $dataSearch->getSearchValue('loc_tags');
            if (!empty($locTags)) {
                $allSearchTagArray = array_merge($allSearchTagArray, explode(',', $locTags));
            }
            if(!empty($allSearchTagArray)){
                static::addQnATagsFilterToDataSearch($allSearchTagArray, $dataSearch);
            }

            $name = $dataSearch->getSearchValue('name');
            if(!empty($name)){
                Contact::addNameFilterToDataSearch($name, $dataSearch);
            }
        }
        
        if(!is_null($form) && $form->wasSubmitted() && $form->validate()){
            $dataSearch->clearSearchValues();
            $searchType = filter_input(INPUT_POST, 'search_type');
            if (empty($searchType) || $searchType === 'basic') {
                $dataSearch->setSearchValue('search_type', 'basic');
                $basicSearchField = filter_input(INPUT_POST, 'basic_search_field');
                $dataSearch->setSearchValue('basic_search_field', $basicSearchField);
            } else {
                $dataSearch->setSearchValue('search_type', 'advanced');
                $name = filter_input(INPUT_POST, 'search_name');
                $dataSearch->setSearchValue('name', $name);
                $tags = filter_input(INPUT_POST, 'search_tags', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY);
                $dataSearch->setSearchValue('tags', implode(',', $tags));
                $locTags = filter_input(INPUT_POST, 'search_loc_tags', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY);
                $dataSearch->setSearchValue('loc_tags', implode(',', $locTags));
            }
        }
        
        return true;
    }
    
    public static function addQnATagsFilterToDataSearch($tags, GI_DataSearch $dataSearch){
        $contactTableName = ContactFactory::getDbPrefix() . 'contact';
        $dataSearch->join('item_link_to_tag', 'item_id', $contactTableName, 'id', 'iltt')
                ->join('tag', 'id', 'iltt', 'tag_id', 'tag')
                ->filter('iltt.table_name', 'contact')
                ->filterIn('tag.id', $tags)
                ->groupBy('id');
    }
    
    public static function addQnABasicSearchFieldFilterToDataSearch($basicSearchField, GI_DataSearch $dataSearch){
        //For now search by only name
        //@todo: search by tag title
        Contact::addNameFilterToDataSearch($basicSearchField, $dataSearch);
    }
    
    public function getApplicationURLAttrs() {
        $typeRefsArray = ContactCatFactory::getTypeRefArray($this->getTypeRef());
        $baseTypeRef = $typeRefsArray[0];
        return array(
            'controller'=>'contactprofile',
            'action'=>'application',
            'type'=>$baseTypeRef,
        );
    }
    
    public function getApplicationURL() {
        return GI_URLUtils::buildURL($this->getApplicationURLAttrs());
    }

    public function getProfileDetailView() {
        $contact = $this->getContact();
        return new ContactOrgProfileDetailView($contact);
    }

    public function getProfileFormView(\GI_Form $form, $buildForm = true, $curStep = 1, $curTab = 0) {
        $formView = $this->getProfileFormViewObject($form, $curStep);
        $formView->setCurTab($curTab);
        if ($buildForm) {
            $formView->buildForm();
        }
        return $formView;
    }

    protected function getProfileFormViewObject(GI_Form $form, $curStep = 1) {
        $contact = $this->getContact();
        if (empty($contact)) {
            return NULL;
        }
        $view = new ContactOrgProfileFormView($form, $contact);
        $view->setCurStep($curStep);
        return $view;
    }

        /**
     * @param GI_Form $form
     * @param type $step
     * @return boolean
     */
    public function handleProfileFormSubmission(GI_Form $form, $step = 1) {
        if ($form->wasSubmitted() && $this->validateForm($form, $step)) {
            switch ($step) {
                case 10:
                    return true;
                case 20:
                    return $this->handleProfileAdvancedFormSubmission($form);
            }

            return true;
        }
        return false;
    }

    public function validateProfileForm(GI_Form $form, $step = 1) {
        if ($form->wasSubmitted() && $form->validate()) {

            switch ($step) {
                case 10:
                    return true;
                case 20:
                    return $this->validateProfileAdvancedForm($form);
            }

            return true;
        }
        return false;
    }

    protected function validateProfileAdvancedForm(GI_Form $form) {
        if ($form->wasSubmitted() && $form->validate()) {
            $errors = 0;

            if (!empty($errors)) {
                return false;
            }
            return true;
        }
        return false;
    }



    protected function handleProfileAdvancedFormSubmission(GI_Form $form) {
        if ($form->wasSubmitted()) {
            if (!$this->setPropertiesFromProfileAdvancedForm($form)) {
                return false;
            }
            return true;
        }
        return false;
    }

    protected function setPropertiesFromProfileAdvancedForm(GI_Form $form) {
        $contact = $this->getContact();
        if (empty($contact)) {
            return false;
        }
        $defaultCurrencyId = filter_input(INPUT_POST, 'default_currency_id');
        if (!empty($defaultCurrencyId)) {
            $contact->setProperty('default_currency_id', $defaultCurrencyId);
            if (!$contact->save()) {
                return false;
            }
        }
        
        if (!$contact->handleContactSubCatTagFieldSubmission($form)) {
            return false;
        }
        
        return true;
    }



    
    public function isInternal() {
        return false;
    }
    
    public function isClient() {
        return false;
    }
    
    public function isVendor() {
        return false;
    }
    
    public function getNewUserDefaultRole() {
        $roleSystemTitle = $this->getNewUserDefaultRoleSystemTitle();
        return RoleFactory::getRoleBySystemTitle($roleSystemTitle);
    }
    
    public function getNewUserDefaultRoleSystemTitle() {
        $typeSystemTitle = '';
        $typeModel = $this->getTypeModel();
        if (!empty($typeModel)) {
            $typeSystemTitle = $typeModel->getProperty('default_user_role_ref');
        }
        if (!empty($typeSystemTitle)) {
            return $typeSystemTitle;
        }
        return static::$newUserDefaultRoleSystemTitle;
    }

    /** @return AbstractWindowView */
    public function getPublicProfileDetailView() {
        return NULL;
    }

    public function getPublicProfileFormView(GI_Form $form) {
        return NULL;
    }

    public function getUsesPublicProfile() {
        if (empty($this->usesPublicProfile)) {
            $usesProfile = false;
            $this->usesPublicProfile = $usesProfile;
        }
        return $this->usesPublicProfile;
    }
    
    public function getUsesPayment() {
        if (empty($this->usesPayment)) {
            $usesPayment = false;
            $this->usesPayment = $usesPayment;
        }
        return $this->usesPayment;
    }

    /**
     * 
     * @param GI_DataSearch $dataSearch
     * @param string $type
     * @param array $redirectArray
     * @return \ContactProfileSearchFormView
     */
    public static function getProfileSearchForm(GI_DataSearch $dataSearch, $type, AbstractContact $sampleContact, &$redirectArray = array()) {
        $form = new GI_Form('contact_search');
        $searchView = static::getProfileSearchFormView($form, $dataSearch);
        $sampleContact->filterProfileSearchForm($dataSearch, $form);

        if ($form->wasSubmitted() && $form->validate()) {
            $queryId = $dataSearch->getQueryId();

            if (empty($redirectArray)) {
                $redirectArray = array(
                    'controller' => 'contactprofile',
                    'action' => 'index',
                    'type' => $type,
                );
            }

            $redirectArray['queryId'] = $queryId;
            if (GI_URLUtils::getAttribute('ajax')) {
                if (GI_URLUtils::getAttribute('redirectAfterSearch')) {
                    //Set new Url for search
                    unset($redirectArray['ajax']);
                    $redirectArray['fullView'] = 1;
                    $redirectArray['newUrl'] = GI_URLUtils::buildURL($redirectArray);
                    $redirectArray['newUrlTargetId'] = 'list_bar';
                    $redirectArray['jqueryAction'] = 'clearMainPanel();';
                } else {
                    $redirectArray['ajax'] = 1;
                    GI_URLUtils::redirect($redirectArray);
                }
            } else {
                GI_URLUtils::redirect($redirectArray);
            }
        }
        return $searchView;
    }

    /**
     * @param GI_Form $form
     * @param GI_DataSearch $dataSearch
     * @return \ContactProfileSearchFormView
     */
    protected static function getProfileSearchFormView(GI_Form $form, GI_DataSearch $dataSearch = NULL) {
        $searchValues = array();
        if ($dataSearch) {
            $searchValues = $dataSearch->getSearchValues();
        }
        $searchValues['queryId'] = $dataSearch->getQueryId();
        $searchValues['search_type'] = 'advanced';
        $searchView = new ContactProfileSearchFormView($form, $searchValues);
        return $searchView;
    }

    public function getPublicProfileUICardView() {
        return NULL;
    }
    
    public function getPublicViewURL() {
        $attrs = $this->getPublicViewURLAttrs();
        if (!empty($attrs)) {
            return GI_URLUtils::buildURL($attrs);
        }
        return NULL;
    }

    protected function getPublicViewURLAttrs() {
        return NULL;
    }

    /**
     * 
     * @param GI_DataSearch $dataSearch
     * @param string $type
     * @param array $redirectArray
     */
    public static function getPublicSearchForm(GI_DataSearch $dataSearch, AbstractContactOrg $sampleContact, &$redirectArray = array()){
        return NULL;
    }

    /**
     * @param GI_Form $form
     * @param GI_DataSearch $dataSearch
     */
    protected static function getPublicSearchFormView(GI_Form $form, GI_DataSearch $dataSearch = NULL) {
        return NULL;
    }

    /**
     * @param GI_DataSearch $dataSearch
     * @param GI_Form $form
     * @return boolean
     */
    protected static function filterPublicSearchForm(GI_DataSearch $dataSearch, GI_Form $form = NULL) {
        return false;
    }

    public function isSuspendable() {
        return static::$isSuspendable;
    }
    
    public function getChangeSubFormView(GI_Form $form, $buildForm = true, $curStep = 1) {
        return NULL;
    }

    public function handleChangeSubscriptionFormSubmission(GI_Form $form, $step = 1) {
        if ($form->wasSubmitted() && $this->validateChangeSubscriptionForm($form, $step)) {
            
            
            return true;
        }
        return false;
    }
    
    public function validateChangeSubscriptionForm(GI_Form $form, $step = 1) {
        if ($form->wasSubmitted() && $form->validate()) {
            //TODO
            return true;
        }
        return false;
    }

    public function getChangeSubscriptionStepNavURLAttrs($step = 1, $ajax = true) {
        $contact = $this->getContact();
        if (empty($contact)) {
            return array();
        }
        $attrs = $contact->getChangeSubscriptionURLAttrs();
        $attrs['step'] = $step;
        if ($ajax) {
            $attrs['ajax'] = 1;
        }

        return $attrs;
    }
    
    public function getApplicationTypeRef() {
        return static::$applicationTypeRef;
    }

}
