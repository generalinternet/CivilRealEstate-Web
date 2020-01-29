<?php
/**
 * Description of AbstractContact
 *
 * @author General Internet
 * @copyright  2019 General Internet
 * @version    4.1.0
 */
abstract class AbstractContact extends GI_Model {
    
    protected $allTags = NULL;
    protected $subCatTag = NULL;
    protected $contactCat = NULL;
    
    protected $hasAtLeastOneContactInfo = NULL;
    
    protected $defaultInfoTypeRefs = array(
        'email_address',
        'phone_num',
        'address'
    );
    
    protected $multiInfoEnabledRefs = array(
        'email_address',
        'phone_num',
        'address'
    );
    
    /** @var ContactTerms */
    protected $terms = NULL;
    
    /** @var User */
    protected $sourceUser = NULL;
    /** @var AbstractInvDiscount[] */
    protected $invDiscounts = NULL;
    /** @var AbstractCurrency */
    protected $defaultCurrency = NULL;
    /** @var AbstractPricingRegion */
    protected $defaultPricingRegion = NULL;
    
    /** @var AbstractContact[] */
    protected $childContacts = NULL;
    
    /** @var AbstractContact[] */
    protected $parentContacts = NULL;
    
    protected $isClient = NULL;
    protected $isVendor = NULL;
    protected $isShipper = NULL;
    protected $contactQB = NULL;
    protected $isInternal = NULL;
    protected $defaultContactCatTypeRef = NULL;
    protected $hasBills = NULL;
    protected $hasInvoices = NULL;
    
    protected $paymentProcessor = NULL;
    protected $paymentSettings = array();
    protected $isSuspended = NULL;
    protected $subscriptions = NULL;
    protected $application = NULL;
    
    public function setDefaultContactCatTypeRef($defaultContactCatTypeRef){
        $this->defaultContactCatTypeRef = $defaultContactCatTypeRef;
        return $this;
    }
    
    /**
     * @param array $infoTypeRefs Array of Contact Info Type Refs
     * @return \AbstractContact
     */
    public function setDefaultInfoTypeRefs($infoTypeRefs){
        $this->defaultInfoTypeRefs= $infoTypeRefs;
        return $this;
    }
    
    /**
     * @return array $infoTypeRefs Array of Contact Info Type Refs
     */
    public function getDefaultInfoTypeRefs(){
        return $this->defaultInfoTypeRefs;
    }
    
    public function multiInfoEnabled($infoTypeRef){
        if(in_array($infoTypeRef, $this->multiInfoEnabledRefs)){
            return true;
        }
        return false;
    }
    
    /**
     * @param GI_Form $form
     * @return AbstractGI_Uploader
     */
    protected function getUploader(GI_Form $form = NULL){
        if($this->getProperty('id')){
            $appendName = 'edit_' . $this->getId();
        } else {
            $appendName = 'add';
        }
        
        $uploader = GI_UploaderFactory::buildUploader('contact_' . $appendName);
        $folder = $this->getFolder();
        
        $uploader->setTargetFolder($folder);
        if (!empty($form)) {
            $uploader->setForm($form);
        }
        return $uploader;
    }
    
    /**
     * @param GI_Form $form
     * @return AbstractGI_Uploader
     */
    protected function getImageUploader(GI_Form $form = NULL){
        if($this->getProperty('id')){
            $appendName = 'edit_' . $this->getId();
        } else {
            $appendName = 'add';
        }
        
        $imgUploader = GI_UploaderFactory::buildImageUploader('contact_img_' . $appendName);
        $imgUploader->setFilesLabel('Image');
        $imgUploader->setBrowseLabel('Upload Image');
        $imgFolder = $this->getImageFolder();
        $imgUploader->setTargetFolder($imgFolder);
        if (!empty($form)) {
            $imgUploader->setForm($form);
        }
        
        return $imgUploader;
    }
    
    /**
     * @return Folder
     */
    public function getImageFolder(){
        $imgFolder = $this->getSubFolderByRef('contact_images', array(
            'title' => 'Images'
        ));
        return $imgFolder;
    }
    
    /**
     * @param GI_Form $form
     * @param array $otherData
     * @return \ContactFormView
     */
    public function getFormView(GI_Form $form) {
        $formView = new ContactFormView($form, $this);
        $this->setUploadersOnFormView($formView);
        return $formView;
    }
    
    /**
     * @param AbstractContactFormView $formView
     */
    protected function setUploadersOnFormView(AbstractContactFormView $formView){
        $form = $formView->getForm();
        $uploader = $this->getUploader($form);
        $formView->setUploader($uploader);
        $imgUploader = $this->getImageUploader($form);
        $formView->setImageUploader($imgUploader);
    }
    
    /** @return \ContactDetailView */
    public function getDetailView() {
        $detailView = new ContactDetailView($this);
        return $detailView;
    }
    
    /** @return \ContactDiscountView */
    public function getDiscountView() {
        $detailView = new ContactDiscountView($this);
        return $detailView;
    }
    
    public function getDefaultCurrency() {
        if (empty($this->defaultCurrency)) {
            $this->defaultCurrency = CurrencyFactory::getModelById($this->getProperty('default_currency_id'));
        }
        return $this->defaultCurrency;
    }
    
    public function getDefaultPricingRegion(){
        if (empty($this->defaultPricingRegion)) {
            $contactCat = $this->getContactCat();
            $pricingRegionId = $this->setProperty('contact_cat_client.default_pricing_region_id');
            if(!empty($pricingRegionId)){
                $this->defaultPricingRegion = CurrencyFactory::getModelById($pricingRegionId);
            }
        }
        return $this->defaultPricingRegion;
    }
    
    public function getChildContacts() {
        if (empty($this->childContacts)) {
            $this->childContacts = ContactFactory::getChildContactArrayByParent($this);
        }
        return $this->childContacts;
    }
    
    public function getParentContacts() {
        if (empty($this->parentContacts)) {
            $this->parentContacts = ContactFactory::getParentContactArrayByChild($this);
        }
        return $this->parentContacts;
    }
    
    /**
     * @param GI_Form $form
     * @param GI_DataSearch $dataSearch
     * @return \ContactSearchFormView
     */
    protected static function getSearchFormView(GI_Form $form, GI_DataSearch $dataSearch = NULL){
        $searchValues = array();
        if($dataSearch){
            $searchValues = $dataSearch->getSearchValues();
        }
        $searchValues['queryId'] = $dataSearch->getQueryId();
        $searchView = new ContactSearchFormView($form, $searchValues);
        return $searchView;
    }
    
    /**
     * @param GI_DataSearch $dataSearch
     * @param GI_Form $form
     * @return boolean
     */
    public static function filterSearchForm(GI_DataSearch $dataSearch, GI_Form $form = NULL){
        $searchType = $dataSearch->getSearchValue('search_type');
        if (empty($searchType) || $searchType === 'basic') {
            //Basic Search
            $basicSearchField = $dataSearch->getSearchValue('basic_search_field');
            if(!empty($basicSearchField)){
                static::addBasicSearchFieldFilterToDataSearch($basicSearchField, $dataSearch);
            }
        } else {
            //Advanced Search
            $name = $dataSearch->getSearchValue('name');
            if(!empty($name)){
                static::addNameFilterToDataSearch($name, $dataSearch);
            }

            $categories = $dataSearch->getSearchValue('categories');
            if(!empty($categories)){
                static::addCategoriesFilterToDataSearch($categories, $dataSearch);
            }

            $address = $dataSearch->getSearchValue('address');
            if(!empty($address)){
                static::addAddressFilterToDataSearch($address, $dataSearch);
            }

            $email = $dataSearch->getSearchValue('email');
            if(!empty($email)){
                static::addEmailFilterToDataSearch($email, $dataSearch);
            }

            $phone = $dataSearch->getSearchValue('phone');
            if(!empty($phone)){
                static::addPhoneFilterToDataSearch($phone, $dataSearch);
            }
            
            $tags = $dataSearch->getSearchValue('tags');
            if(!empty($tags)){
                static::addTagsFilterToDataSearch($tags, $dataSearch);
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

                $address = filter_input(INPUT_POST, 'search_address');
                $dataSearch->setSearchValue('address', $address);

                $email = filter_input(INPUT_POST, 'search_email');
                $dataSearch->setSearchValue('email', $email);

                $phone = filter_input(INPUT_POST, 'search_phone');
                $dataSearch->setSearchValue('phone', $phone);

                $categories = filter_input(INPUT_POST, 'search_categories', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY);
                $dataSearch->setSearchValue('categories', $categories);
                
                $tags = filter_input(INPUT_POST, 'search_tags', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY);
                $dataSearch->setSearchValue('tags', $tags);
            }
        }
        
        return true;
    }
    
    /**
     * 
     * @param GI_DataSearch $dataSearch
     * @param string $type
     * @param array $redirectArray
     * @return \ContactSearchFormView
     */
    public static function getSearchForm(GI_DataSearch $dataSearch, $type = NULL, &$redirectArray = array()){
        $form = new GI_Form('contact_search');
        $searchView = static::getSearchFormView($form, $dataSearch);
        
        static::filterSearchForm($dataSearch, $form);
        
        if($form->wasSubmitted() && $form->validate()){
            $queryId = $dataSearch->getQueryId();
            
            if(empty($redirectArray)){
                $redirectArray = array(
                    'controller' => 'contact',
                    'action' => 'index'
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
    
    public static function addNameFilterToDataSearch($name, GI_DataSearch $dataSearch, $contactTableAlias = NULL){
        if(get_called_class() != 'Contact'){
            return;
        }
        $ind = ContactFactory::buildNewModel('ind');
        $org = ContactFactory::buildNewModel('org');
        $loc = ContactFactory::buildNewModel('loc');
        $dataSearch->filterGroup()
                ->filterGroup()
                ->andIf();
        $ind->addNameFilterToDataSearch($name, $dataSearch, $contactTableAlias);
        $dataSearch->closeGroup()
                ->orIf()
                ->filterGroup()
                ->andIf();
        $org->addNameFilterToDataSearch($name, $dataSearch, $contactTableAlias);
        $dataSearch->closeGroup()
                ->orIf()
                ->filterGroup()
                ->andIf();
        $loc->addNameFilterToDataSearch($name, $dataSearch, $contactTableAlias);
        $dataSearch->closeGroup()
                ->closeGroup()
                ->andIf();
    }
    
    public static function addAddressFilterToDataSearch($address, GI_DataSearch $dataSearch){
        $contactTable = $dataSearch->prefixTableName('contact');
        $columns = array(
            'ADDR.addr_street',
            'ADDR.addr_city',
            'ADDR.addr_code'
        );
        $dataSearch->leftJoin('contact_info', 'contact_id', $contactTable, 'id', 'INFOADDR')
                ->leftJoin('contact_info_address', 'parent_id', 'INFOADDR', 'id', 'ADDR')
                ->filterTermsLike($columns, $address)
                ->orderByLikeScore($columns, $address);
    }
    
    public static function addEmailFilterToDataSearch($email, GI_DataSearch $dataSearch){
        $contactTable = $dataSearch->prefixTableName('contact');
        $columns = array(
            'EMAIL.email_address'
        );
        $dataSearch->leftJoin('contact_info', 'contact_id', $contactTable, 'id', 'INFOEMAIL')
                ->leftJoin('contact_info_email_addr', 'parent_id', 'INFOEMAIL', 'id', 'EMAIL')
                ->filterTermsLike($columns, $email)
                ->orderByLikeScore($columns, $email);
    }
    
    public static function addPhoneFilterToDataSearch($phone, GI_DataSearch $dataSearch){
        $contactTable = $dataSearch->prefixTableName('contact');
        $columns = array(
            'PHONE.phone'
        );
        $dataSearch->leftJoin('contact_info', 'contact_id', $contactTable, 'id', 'INFOPHONE')
                ->leftJoin('contact_info_phone_num', 'parent_id', 'INFOPHONE', 'id', 'PHONE')
                ->filterTermsLike($columns, $phone)
                ->orderByLikeScore($columns, $phone);
    }
    
    public static function addCategoriesFilterToDataSearch($categories, GI_DataSearch $dataSearch){
        $contactTableName = ContactFactory::getDbPrefix() . 'contact';
        $dataSearch->join('contact_cat', 'contact_id', $contactTableName, 'id', 'cat')
                ->filterIn('cat.contact_cat_type_id', $categories)
                ->groupBy('id');
    }

    public static function addTagsFilterToDataSearch($tags, GI_DataSearch $dataSearch) {
        $contactTableName = ContactFactory::getDbPrefix() . 'contact';
        $tagLinkJoin = $dataSearch->createInnerJoin('item_link_to_tag', 'item_id', $contactTableName, 'id', 'cltt');
        $tagLinkJoin->filter('cltt.table_name', 'contact');
        $dataSearch->join('tag', 'id', 'cltt', 'tag_id', 'tag')
                ->filterIn('tag.id', $tags)
                ->groupBy('id');
    }

    public static function addBasicSearchFieldFilterToDataSearch($basicSearchField, GI_DataSearch $dataSearch) {
        $dataSearch->filterGroup()
                ->filterGroup();
        static::addNameFilterToDataSearch($basicSearchField, $dataSearch);
        $dataSearch->closeGroup()
                ->orIf()
                ->filterGroup();
        static::addAddressFilterToDataSearch($basicSearchField, $dataSearch);
        $dataSearch->filter('ADDR.status', 1);
        $dataSearch->filter('INFOADDR.status', 1);
        $dataSearch->closeGroup()
                ->orIf()
                ->filterGroup();
        static::addEmailFilterToDataSearch($basicSearchField, $dataSearch);
        $dataSearch->filter('EMAIL.status', 1);
        $dataSearch->filter('INFOEMAIL.status', 1);
        $dataSearch->closeGroup()
                ->orIf()
                ->filterGroup();
        static::addPhoneFilterToDataSearch($basicSearchField, $dataSearch);
        $dataSearch->filter('PHONE.status', 1);
        $dataSearch->filter('INFOPHONE.status', 1);
        $dataSearch->closeGroup()
                ->closeGroup()
                ->andIf();
    }
    
    public function validateForm(\GI_Form $form) {
        $formValid = parent::validateForm($form);
        if(!$this->validateUserFields($form)){
            $formValid = false;
        }
        if (!$this->validateContactCatForms($form)) {
            $formValid = false;
        }
        if(!$this->validateContactInfoForms($form)){
            $formValid = false;
        }
        return $formValid;
    }
    
    /**
     * @param GI_Form $form
     * @return boolean
     */
    protected function setPropertiesFromForm(GI_Form $form){
        $colour = filter_input(INPUT_POST, 'colour');
        $this->setProperty('colour', $colour);
        $defaultCurrencyId = filter_input(INPUT_POST, 'default_currency_id');
        if (!empty($defaultCurrencyId)) {
            $this->setProperty('default_currency_id', $defaultCurrencyId);
        }
        
        $notes = filter_input(INPUT_POST, 'notes');
        $this->setProperty('notes', $notes);
        return true;
    }

//    protected function setInternalPropertyFromForm(GI_Form $form) {
//        $hasPermission = false;
//        if (Permission::verifyByRef('mark_contact_as_internal')) {
//            $hasPermission = true;
//        }
//        if (empty($this->getProperty('id')) || $hasPermission) {
//            $internal = filter_input(INPUT_POST, 'internal');
//            if (!is_null($internal)) {
//                $this->setProperty('contact.internal', $internal);
//                if ($hasPermission) {
//                    if ($this->isOrganization() && $internal != $this->getProperty('internal')) {
//                        $this->internalChanged = true;
//                    }
//                }
//            }
//        }
//    }

    /**
     * @param GI_Form $form
     * @param int $pId
     * @return boolean
     */
    public function handleFormSubmission($form, $pId = NULL) {
        if (!($form->wasSubmitted() && $this->validateForm($form))) {
            return false;
        }
        if(!$this->setPropertiesFromForm($form)){
            return false;
        }
        
        $uploader = $this->getUploader($form);
        $imgUploader = $this->getImageUploader($form);
        
        if($this->save()){
            
            if (!$this->handleContactSubCatTagFieldSubmission($form)) {
                return false;
            }
            
            if(!$this->handleContactInfoFormSubmission($form)) {
                return false;
            }
            
            if($uploader){
                $uploader->setTargetFolder($this->getFolder());
                FolderFactory::putUploadedFilesInTargetFolder($uploader);
            }
            
            if($imgUploader){
                $imgUploader->setTargetFolder($this->getImageFolder());
                FolderFactory::putUploadedFilesInTargetFolder($imgUploader);
            }
            
            $this->saveContactAsUserFromForm();
            if(!empty($pId)){ //link the contact as a "child" to the contact with id = pId
                $pContact = ContactFactory::getModelById($pId);
                if(!empty($pContact)){
                    $linkResult = ContactFactory::linkContactAndContact($pContact, $this);
                    if(!$linkResult){
                        return false;
                    }
                }
            }
            if (!$this->handleTagFormSubmission($form)) {
                return false;
            }
            
            if (!$this->handleContactCatFormSubmission($form)) {
                return false;
            }
            
            return true;
        } else {
            return false;
        }
    }
    
    protected function handleContactInfoFormSubmission($form) {
        $pTypeRefs = filter_input(INPUT_POST, 'p_type_refs');
        if (empty($pTypeRefs)) {
            return true;
        }
        $pTypeRefsArray = explode(',', $pTypeRefs);
        foreach ($pTypeRefsArray as $pTypeRef) {
            //get the existing contact info array by type ref, with id as key
            $existingContactInfos = $this->getContactInfoArray($pTypeRef, true)[$pTypeRef];
            //get the suffix array using the pTypeRef
            $suffixArray = filter_input(INPUT_POST, $pTypeRef, FILTER_DEFAULT, FILTER_REQUIRE_ARRAY);
            if (!empty($suffixArray)) {
                $pos = 0;
                foreach ($suffixArray as $suffix) {
                    $idFromForm = filter_input(INPUT_POST, $pTypeRef . '_id_' . $suffix);
                    if (empty($idFromForm)) {
                        //make a new model
                        $contactInfo = ContactInfoFactory::buildNewModel($pTypeRef);
                    } else {
                        //get model from existing array
                        if (!isset($existingContactInfos[$idFromForm])) {
                            return false;
                        }
                        $contactInfo = $existingContactInfos[$idFromForm];
                        unset($existingContactInfos[$idFromForm]);
                    }
                    $contactId = $this->getId();
                    $contactInfo->setProperty('contact_id', $contactId);
                    $contactInfo->setFieldSuffix($suffix);
                    $contactInfo->setProperty('pos', $pos);
                    $pos++;
                    $contactInfo = $contactInfo->handleFormSubmission($form);
                    if (empty($contactInfo)) {
                        return false;
                    }
                }
                foreach ($existingContactInfos as $contactInfoToDelete) {
                    if(!$contactInfoToDelete->softDelete()){
                        return false;
                    }
                }
            }
        }
        return true;
    }

    /**
     * @param GI_Form $form
     * @return boolean
     */
    protected function handleTagFormSubmission(GI_Form $form) {
        if ($form->wasSubmitted()) {
            /** Commented out because outdated **/
            /*
            $allTags = $this->getAllTags();
            $preExistingTags = $this->getTags();
            $submittedTags = array();
            foreach ($allTags as $allTag) {
                $allTagId = $allTag->getProperty('id');
                $tagInput = filter_input(INPUT_POST, 'tag_' . $allTagId);
                if (!empty($tagInput)) {
                    $submittedTags[$allTagId] = $allTag;
                }
            }
            foreach ($submittedTags as $key => $submittedTag) {
                if (isset($preExistingTags[$key])) {
                    unset($preExistingTags[$key]);
                    unset($submittedTags[$key]);
                }
            }
            foreach ($preExistingTags as $preExistingTag) {
                if (!$preExistingTag->getIsSystem() && !$this->removeTag($preExistingTag)) {
                    return false;
                }
            }
            foreach ($submittedTags as $tagToAdd) {
                if (!$this->addTag($tagToAdd)) {
                    return false;
                }
            }
             */
            
            //Get submmited tags
            $submittedTagIds = $this->getContactTagIdArray($form);
            //Get ids from DB
            $preExistingTags = $this->getTags();
            foreach ($submittedTagIds as $submittedTagId) {
                if (isset($preExistingTags[$submittedTagId])) {
                    unset($preExistingTags[$submittedTagId]);
                    unset($submittedTagIds[$submittedTagId]);
                }
            }
            foreach ($preExistingTags as $preExistingTag) {
                if (!$preExistingTag->getIsSystem() && !$this->removeTag($preExistingTag)) {
                    return false;
                }
            }
            foreach ($submittedTagIds as $tagIdToAdd) {
                $tagToAdd = TagFactory::getModelById($tagIdToAdd);
                if (!$this->addTag($tagToAdd)) {
                    return false;
                }
            }
            
       }
        return true;
    }

    protected function handleContactCatFormSubmission(GI_Form $form) {
        if ($form->wasSubmitted() && $form->validate()) {
            $existingCategoryModels = ContactCatFactory::getModelsByContact($this);
            $modelsToHandle = array();
            $modelsToIgnore = array();
            $selectedCategoryType = filter_input(INPUT_POST, 'categories');
            if (!empty($selectedCategoryType)) {
                $selectedCategoryTypeRefsArray = array($selectedCategoryType);
            } else {
                $selectedCategoryTypeRefsArray = array();
            }
            
            $hiddenTypeRefsString = filter_input(INPUT_POST, 'hidden_categories');
            $hiddenTypeRefsArray = explode(',', $hiddenTypeRefsString);
            if (!empty($existingCategoryModels)) {
                foreach ($existingCategoryModels as $key=>$existingModel) {
                    if (empty($selectedCategoryTypeRefsArray)) {
                        $selectedPosition = false;
                    } else {
                        $selectedPosition = array_search($existingModel->getTypeRef(), $selectedCategoryTypeRefsArray);
                    }
                    if (empty($hiddenTypeRefsArray)) {
                        $hiddenPosition = false;
                    } else {
                        $hiddenPosition = array_search($existingModel->getTypeRef(), $hiddenTypeRefsArray);
                    }
                    
                    if ($selectedPosition !== false) {
                        $modelsToHandle[$existingModel->getTypeRef()] = $existingModel;
                        unset($existingCategoryModels[$key]);
                        unset ($selectedCategoryTypeRefsArray[$selectedPosition]);
                    } else if ($hiddenPosition !== false) {
                        $modelsToIgnore[$existingModel->getTypeRef()] = $existingModel;
                        unset($existingCategoryModels[$key]);
                        unset($hiddenTypeRefsArray[$hiddenPosition]);
                    }
                }
            }
            if (!empty($selectedCategoryTypeRefsArray)) {
                foreach ($selectedCategoryTypeRefsArray as $addTypeRef) {
                    $buildNewModel = true;
                    $softDeletedSearch = ContactCatFactory::search();
                    $softDeletedSearch->filterByTypeRef($addTypeRef)
                            ->filter('contact_id', $this->getProperty('id'))
                            ->filter('status', 0);
                    $softDeletedArray = $softDeletedSearch->select();
                    if (!empty($softDeletedArray)) {
                        $softDeletedModel = $softDeletedArray[0];
                        if ($softDeletedModel->unSoftDelete()) {
                            $modelsToHandle[$addTypeRef] = $softDeletedModel;
                            $buildNewModel = false;
                        }
                    }
                    if ($buildNewModel) {
                        $modelsToHandle[$addTypeRef] = ContactCatFactory::buildNewModel($addTypeRef);
                    }
                }
            }
            if (!empty($modelsToHandle)) {
                foreach ($modelsToHandle as $modelToHandle) {
                    if (!$modelToHandle->handleFormSubmission($form, $this)) {
                        return false;
                    } 
                }
            }
            if (!empty($existingCategoryModels)) {
                foreach ($existingCategoryModels as $modelToDelete) {
                    if (!$modelToDelete->softDelete()) {
                        return false;
                    }
                }
            }
            return true;
        }
        return false;
    }
    
    /**
     * Add/update new categories and remove old categories
     */
    public function updateContactCats(GI_Form $form) {
        $selectedCategoryType = filter_input(INPUT_POST, 'contact_cat_type_ref');
        if (!empty($selectedCategoryType)) {
            $selectedCategoryTypeRefsArray = array($selectedCategoryType);
        } else {
            $selectedCategoryTypeRefsArray = array();
        }
        $existingCategoryModels = ContactCatFactory::getModelsByContact($this);
        $modelsToHandle = array();
        if (!empty($existingCategoryModels)) {
            foreach ($existingCategoryModels as $key=>$existingModel) {
                $selectedPosition = array_search($existingModel->getTypeRef(), $selectedCategoryTypeRefsArray);
                if ($selectedPosition !== false) {
                    $modelsToHandle[$existingModel->getTypeRef()] = $existingModel;
                    unset($existingCategoryModels[$key]);
                    unset($selectedCategoryTypeRefsArray[$selectedPosition]);
                }
            }
        }
        if (!empty($selectedCategoryTypeRefsArray)) {
            foreach ($selectedCategoryTypeRefsArray as $addTypeRef) {
                $buildNewModel = true;
                $softDeletedSearch = ContactCatFactory::search();
                $softDeletedSearch->filterByTypeRef($addTypeRef)
                        ->filter('contact_id', $this->getProperty('id'))
                        ->filter('status', 0);
                $softDeletedArray = $softDeletedSearch->select();
                if (!empty($softDeletedArray)) {
                    $softDeletedModel = $softDeletedArray[0];
                    if ($softDeletedModel->unSoftDelete()) {
                        $modelsToHandle[$addTypeRef] = $softDeletedModel;
                        $buildNewModel = false;
                    }
                }
                if ($buildNewModel) {
                    $modelsToHandle[$addTypeRef] = ContactCatFactory::buildNewModel($addTypeRef);
                }
            }
        }
        if (!empty($modelsToHandle)) {
            foreach ($modelsToHandle as $modelToHandle) {
                if (!empty($form)) {
                    if (!$modelToHandle->handleFormSubmission($form, $this)) {
                        return false;
                    }
                } else {
                    $modelToHandle->setProperty('contact_id', $this->getProperty('id'));
                    if (!$modelToHandle->save()) {
                        return false;
                    }
                }
            }
        }
        if (!empty($existingCategoryModels)) {
            foreach ($existingCategoryModels as $modelToDelete) {
                if (!$modelToDelete->softDelete()) {
                    return false;
                }
            }
        }
        return true;
        
    }
    
//    /**
//     * @deprecated - //TODO - remove
//     * @param GI_Form $form
//     * @param type $type
//     * @return boolean
//     */
//    protected function handleQnATagFormSubmission(GI_Form $form, $type = 'question') {
//        $sampleQuestion = QuestionFactory::buildNewModel($type);
//        if (!$sampleQuestion->handleTagInContactFormSubmission($form, $this)) {
//            return false;
//        }
//        return true;
//    }

    /**
     * @param GI_Form $form
     * @return array
     */
    public function getContactInfoArrayFromForm(GI_Form $form){
        $contactInfoArray = array();
        if($form->wasSubmitted()){
            $pTypeRefs = filter_input(INPUT_POST, 'p_type_refs');
            if (!empty($pTypeRefs)) {
                $pTypeRefsArray = explode(',', $pTypeRefs);
                foreach ($pTypeRefsArray as $pTypeRef) {
                    $contactInfoArray[$pTypeRef] = array();
                    
                    $suffixArray = filter_input(INPUT_POST, $pTypeRef, FILTER_DEFAULT, FILTER_REQUIRE_ARRAY);
                    if (!empty($suffixArray)) {
                        foreach ($suffixArray as $suffix) {
                            $contactInfoId = filter_input(INPUT_POST, $pTypeRef . '_id_' . $suffix);
                            if(empty($contactInfoId)){
                                $contactInfo = ContactInfoFactory::buildNewModel($pTypeRef);
                            } else {
                                $contactInfo = ContactInfoFactory::getModelById($contactInfoId);
                            }
                            $contactInfo->setFieldSuffix($suffix);
                            $contactInfoArray[$pTypeRef][] = $contactInfo;
                        }
                    } else {
                        $contactInfoArray[$pTypeRef][] = ContactInfoFactory::buildNewModel($pTypeRef);
                    }
                }
            }
        }
        if(empty($contactInfoArray)){
            return $this->getContactInfoArray();
        } else {
            return $contactInfoArray;
        }
    }

    /**
     * @param string $typeRef
     * @param boolean $idAsKey
     * @return array
     */
    public function getContactInfoArray($typeRef = NULL, $idAsKey = false) {
        $contactInfos = array();
        $infoTypeRefs = $this->getDefaultInfoTypeRefs();
        if (is_null($this->getId())) {
            if (!empty($typeRef)) {
                $contactInfos[$typeRef] = array(ContactInfoFactory::buildNewModel($typeRef));
            } else {
                foreach($infoTypeRefs as $infoTypeRef){
                    $contactInfos[$infoTypeRef] = array(
                        ContactInfoFactory::buildNewModel($infoTypeRef)
                    );
                }
            }
        } else {
            if (!empty($typeRef)) {
                $contactInfos[$typeRef] = ContactInfoFactory::getContactInfosByContact($this, $typeRef, $idAsKey);
            } else {
                foreach($infoTypeRefs as $infoTypeRef){
                    $contactInfos[$infoTypeRef] = ContactInfoFactory::getContactInfosByContact($this, $infoTypeRef, $idAsKey);
                    if(empty($contactInfos[$infoTypeRef])){
                        $contactInfos[$infoTypeRef] = array(
                            ContactInfoFactory::buildNewModel($infoTypeRef)
                        );
                    }
                }
            }
        }
        return $contactInfos;
    }

    /**
     * @param string $preferedTypeRef
     * @return AbstractContactInfoAddress[]
     */
    public function getContactInfoAddresses($preferedTypeRef = NULL){
        $addresses = ContactInfoFactory::getContactAddresses($this, $preferedTypeRef);
        return $addresses;
    }
    
    /**
     * @return string
     */
    public function getName() {
        return '';
    }
    
    public function getDisplayName() {
        return $this->getProperty('display_name');
    }
    
    /**
     * @return string
     */
    public function getRealName(){
        return $this->getName();
    }
    
    public function getFullyQualifiedName() {
        return $this->getProperty('fully_qualified_name');
    }

    /**
     * @param string $role
     * @return string
     */
    public function getCountry() {
        $countryCode = $this->getCountryCode();
        $country = GeoDefinitions::getCountryNameFromCode($countryCode);
        return $country;
    }
    
    /**
     * @param boolean $includeCountry
     * @param string $typeRef
     * @return string
     */
    public function getRegion($includeCountry = false, $typeRef = 'address') {
        $addressInfo = $this->getContactInfo($typeRef, false, true);
        if($addressInfo){
            return $addressInfo->getRegion($includeCountry);
        }
        return NULL;
    }
    
    /**
     * @param string $typeRef
     * @return string
     */
    public function getRegionCode($typeRef = 'address') {
        $addressInfo = $this->getContactInfo($typeRef, false, true);
        if($addressInfo){
            return $addressInfo->getProperty('contact_info_address.addr_region');
        }
        return NULL;
    }
    
    /**
     * @param boolean $breaklines
     * @param string $typeRef
     * @return string
     */
    public function getAddress($breaklines = false, $typeRef = 'address') {
        $addressInfo = $this->getContactInfo($typeRef, false, true);
        if($addressInfo){
            return $addressInfo->getAddressString($breaklines);
        }
        return NULL;
    }

    /**
     * @param string $typeRef
     * @return string
     */
    public function getCountryCode($typeRef = 'address') {
        $addressInfo = $this->getContactInfo($typeRef, false, true);
        if($addressInfo){
            return $addressInfo->getProperty('contact_info_address.addr_country');
        }
        return NULL;
    }
    
    public function getEditURL() {
        return GI_URLUtils::buildURL(array(
            'controller' => 'contact',
            'action' => 'edit',
            'id' => $this->getId()
        ));
    }
    
    public function getDeleteURL() {
        return GI_URLUtils::buildURL(array(
            'controller' => 'contact',
            'action' => 'delete',
            'id' => $this->getId()
        ));
    }
    
    /**
     * @param boolean $plural
     * @return string
     */
    public function getViewTitle($plural = true) {
        $title = 'Contact';
        if ($plural) {
            $title .= 's';
        }
        return $title;
    }

    /**
     * @return ContactSummaryView
     */
    public function getSummaryView($relationship = NULL) {
        $summaryView = new ContactSummaryView($this, $relationship);
        return $summaryView;
    }

    /**
     * @param string $typeRef
     * @param string $relation
     * @return AbstractContactRelationship[]
     */
    public function getContactRelationships($typeRef = NULL, $relation = 'child') {
        $contactRelationshipTableName = dbConfig::getDbPrefix() . 'contact_relationship';
        if ($relation === 'child') {
            if (empty($typeRef)) {
                $relationships = ContactRelationshipFactory::search()
                        ->join('contact', 'id', $contactRelationshipTableName, 'c_contact_id', 'C_C')
                        ->filter('p_contact_id', $this->getId())
                        ->orderBy('id')
                        ->select();
            } else {
                $pTypeRef = $this->getPTypeRef($typeRef);
                $relationshipSearch = ContactRelationshipFactory::search();
                if($typeRef == 'loc' && !Permission::verifyByRef('all_warehouses')){
                    $assWarehouseTypeId = $relationshipSearch->getTypeIdByRefAndTableName('assigned_to_warehouse', 'assigned_to_contact');
                    $assJoin = $relationshipSearch->createLeftJoin('assigned_to_contact', 'contact_id', $contactRelationshipTableName, 'c_contact_id', 'ASS');
                    $assJoin->filter('ASS.user_id', Login::getUserId())
                            ->filter('ASS.assigned_to_contact_type_id', $assWarehouseTypeId);
                    $contactWarehouseTypeId = $relationshipSearch->getTypeIdByRefAndTableName('warehouse', 'contact_loc');
                    $relationshipSearch->join('contact_loc', 'parent_id', $contactRelationshipTableName, 'c_contact_id', 'LOC')
                            ->filterGroup()
                                ->filter('ASS.status', 1)
                                ->orIf()
                                ->filterNotEqualTo('LOC.contact_loc_type_id', $contactWarehouseTypeId)
                            ->closeGroup()
                            ->andIf();
                }
                $relationships = $relationshipSearch->join('contact', 'id', $contactRelationshipTableName, 'c_contact_id', 'C_C')
                        ->join('contact_type', 'id', 'C_C', 'contact_type_id', 'C_CT')
                        ->filter('p_contact_id', $this->getId())
                        ->filter('C_CT.ref', $pTypeRef)
                        ->orderBy('id')
                        ->select();
            }
        } else if ($relation === 'parent') {
            if (empty($typeRef)) {
                $relationships = ContactRelationshipFactory::search()
                        ->join('contact', 'id', $contactRelationshipTableName, 'p_contact_id', 'P_C')
                        ->filter('c_contact_id', $this->getId())
                        ->orderBy('id')
                        ->select();
            } else {
                $pTypeRef = $this->getPTypeRef($typeRef);
                $relationships = ContactRelationshipFactory::search()
                        ->join('contact', 'id', $contactRelationshipTableName, 'p_contact_id', 'P_C')
                        ->join('contact_type', 'id', 'P_C', 'contact_type_id', 'P_CT')
                        ->filter('c_contact_id', $this->getId())
                        ->filter('P_CT.ref', $pTypeRef)
                        ->orderBy('id')
                        ->select();
            }
        } else {
            $relationships = array();
        }
        return $relationships;
    }

    /** @return Tag[] */
    public function getAllTags() {
        if (empty($this->allTags)) {
            $tags = TagFactory::search()
                    ->filterByTypeRef('contact')
                    ->select();
            $this->allTags = $tags;
        }
        return $this->allTags;
    }
    
    public function getSubCategoryTag() {
        if (empty($this->subCatTag)) {
            $tagTableName = TagFactory::getDbPrefix() . 'tag';
            $search = TagFactory::search();
            $search->filterByTypeRef('contact_sub_cat');
            $tagLinkJoin = $search->createLeftJoin('item_link_to_tag', 'tag_id', $tagTableName, 'id', 'CLTT');
            $tagLinkJoin->filter('CLTT.table_name', 'contact');
            $search->filter('CLTT.item_id', $this->getId());
            $results = $search->select();
            if (!empty($results)) {
                $this->subCatTag = $results[0];
            } else {
                $notDefinedTag = TagFactory::getModelByRefAndTypeRef('not_defined', 'contact_sub_cat');
                $this->subCatTag = $notDefinedTag;
            }
        }
        return $this->subCatTag;
    }
    
    /** @return TagListView */
    public function getTagListView() {
        $tags = $this->getTags();
        $tagListView = new TagListView($tags);
        return $tagListView;
    }
    
    /**
     * @param GI_Form $form
     * @return TagListFormView
     */
    public function getTagListFormView($form) {
        $existingTags = $this->getTags();
        $allTags = $this->getAllTags();
        $tagListFormView = new TagListFormView($form, $allTags, $existingTags);
        return $tagListFormView;
    }

    /**
     * @param AbstractTag $tag
     * @return boolean
     */
    public function addTag(AbstractTag $tag) {
        return ContactFactory::linkContactAndTag($this, $tag);
    }

    /**
     * @param AbstractTag $tag
     * @return boolean
     */
    public function removeTag(AbstractTag $tag) {
        return ContactFactory::unlinkContactAndTag($this, $tag);
    }
    
    public function getContactRelationshipsDetailView($linkedTypeRefs = NULL) {
        if (empty($linkedTypeRefs)) {
            $linkedTypeRefs = array(
                'org' => 'child',
                'ind' => 'child',
                'loc' => 'child'
            );
        }
        $linkedRelationships = array();
        foreach ($linkedTypeRefs as $linkedTypeRef => $relation) {
            $linkedRelationships[$linkedTypeRef] = $this->getContactRelationships($linkedTypeRef, $relation);
        }
        $contactRelationshipsDetailView = new ContactRelationshipsDetailView($this, $linkedRelationships, $linkedTypeRefs);
        return $contactRelationshipsDetailView;
    }
    
    
    public function getAssignedToContactsDetailView() {
        $assignedToContacts = AbstractAssignedToContactFactory::search()
                    ->filter('contact_id', $this->getId())
                    ->select();
        $assignedToContactsDetailView = new AssignedToContactsDetailView($this, $assignedToContacts);
        return $assignedToContactsDetailView;
    }
    
    /**
     * @param string $contactTypeRef
     * @return array
     */
    public function getBreadcrumbs($contactTypeRef = NULL) {
        $breadcrumbs = array();
        if(ProjectConfig::useContactCatIndex()){
            $contactCat = $this->getContactCat();
            if(!$contactCat){
//                $catTypeRef = $this->defaultContactCatTypeRef;
//                if(empty($catTypeRef)){
//                    $catTypeRef = 'category';
//                }
//                $contactCat = ContactCatFactory::buildNewModel($catTypeRef);
                $contactCat = $this->getDefaultContactCat();
            }
            $breadcrumbs = $contactCat->getBreadcrumbs();
        } else {
            if (empty($contactTypeRef)) {
                 $contactTypeRef = $this->getTypeRef();
            }
            if($contactTypeRef != 'contact'){
                $breadcrumbs[] = array(
                    'label' => 'Contacts',
                );
            }
            $bcLink = GI_URLUtils::buildURL(array(
                'controller' => 'contact',
                'action' => 'index',
                'type' => $contactTypeRef
            ));
            $breadcrumbs[] = array(
                'label' => $this->getViewTitle(),
                'link' => $bcLink
            );
        }
        
        $contactId = $this->getId();
        if (!is_null($contactId)) {
            $breadcrumbs[] = array(
                'label' => $this->getName(),
                'link' => $this->getViewURL()
            );
        }
        return $breadcrumbs;
    }

    /**
     * @return array
     */
    public function getTypesArray() {
        $typesArray = ContactFactory::getTypesArray($this->getTypeRef());
        return $typesArray;
    }
    
    //TEMP SOLUTION
    public function getPTypeRef($typeRef) {
        $typeRefsArray = ContactFactory::getTypeRefArrayFromTypeRef($typeRef);
        $numberOfRefs = sizeof($typeRefsArray);
        if ($numberOfRefs > 1) {
            $pTypeRef = $typeRefsArray[$numberOfRefs - 2];
            if ($pTypeRef === $typeRef && $numberOfRefs > 2) {
                $pTypeRef = $typeRefsArray[$numberOfRefs - 3];
            }
            return $pTypeRef;
        } else { 
            $pTypeRef = '';
        }
        return $pTypeRef;
    }

    /** @return string */
    public function getSpecificTitle() {
        return $this->getName();
    }
    
    /**
     * @param string $contactInfoTypeRef
     * @return boolean
     */
    public function getHasAtLeastOneContactInfo($contactInfoTypeRef) {
        if (empty($this->hasAtLeastOneContactInfo)) {
            $this->hasAtLeastOneContactInfo = array();
        }
        if (!isset($this->hasAtLeastOneContactInfo[$contactInfoTypeRef]) || ($this->hasAtLeastOneContactInfo[$contactInfoTypeRef] == false)) {
            $contactInfoArray = ContactInfoFactory::search()
                    ->filterByTypeRef($contactInfoTypeRef)
                    ->filter('contact_id', $this->getId())
                    ->select();
            if (empty($contactInfoArray)) {
                $result = false;
            } else {
                $result = true;
            }
            $this->hasAtLeastOneContactInfo[$contactInfoTypeRef] = $result;
        }
        return $this->hasAtLeastOneContactInfo[$contactInfoTypeRef];
    }
    
    /**
     * @return boolean
     */
    public function getAllowAddOnIndex() {
        return true;
    }

    /**
     * @return ContactTerms
     */
    public function getTerms() {
        if (empty($this->terms)) {
            $contactCatClient = ContactCatFactory::getModelByContactAndTypeRef($this, 'client');
            if (!empty($contactCatClient)) {
                $this->terms = $contactCatClient->getTerms();
            }
        }
        return $this->terms;
    }
    
    public function getColour(){
        if($this->getId()){
            return $this->getProperty('colour');
        }
        $colour = GI_Colour::getRandomColour();
        $this->setProperty('colour', $colour);
        return $colour;
    }
    
    /**
     * @param string $typeRef
     * @param boolean $qbLinkedOnly
     * @param boolean $getAnyIfNULL
     * @return ContactInfo
     */
    public function getContactInfo($typeRef = NULL, $qbLinkedOnly = false, $getAnyIfNULL = false){
        $contactInfos = ContactInfoFactory::getContactInfosByContact($this, $typeRef, false, true, $qbLinkedOnly);
        if (!empty($contactInfos)) {
            $contactInfo = $contactInfos[0];
            return $contactInfo;
        } elseif($getAnyIfNULL && $typeRef != 'address'){
            return $this->getContactInfo('address', $qbLinkedOnly);
        }
        return NULL;
    }
    
    public function getEmailAddress(){
        $emailInfo = $this->getContactInfo('email_address');
        if($emailInfo){
            return $emailInfo->getProperty('contact_info_email_addr.email_address');
        }
        return NULL;
    }
    
    public function getPhoneNumber(){
        $phoneInfo = $this->getContactInfo('phone_num');
        if($phoneInfo){
            return $phoneInfo->getProperty('contact_info_phone_num.phone');
        }
        return NULL;
    }
    
    /** @return AbstractContactInfoAddress */
    public function getShippingAddressInfo(){
        return $this->getContactInfo('shipping_address');
    }
    
    /** @return AbstractContactInfoAddress */
    public function getBillingAddressInfo(){
        return $this->getContactInfo('billing_address');
    }
    
    /** @return AbstractContactInfoAddress */
    public function getMailingAddressInfo(){
        return $this->getContactInfo('mailing_address');
    }
    
    public function getIsAddable() {
        if(Permission::verifyByRef('add_contacts')){
            return parent::getIsAddable();
        }
        //Check contact category permission
        $contactCat = $this->getContactCat();
        if (!empty($contactCat)) {
            if ($contactCat->isAddable()) {
                return parent::getIsAddable();
            }
        } else {
            //Add a new contact
            return true;
        }
        return false;
    }
    
    public function getIsViewable() {
        if(Permission::verifyByRef('view_contacts') || $this->getProperty('uid') == Login::getUserId()){
            return parent::getIsViewable();
        }
        
        //Check contact category permission
        $contactCat = $this->getContactCat();
        if (!empty($contactCat)) {
            if ($contactCat->isViewable()) {
                return parent::getIsViewable();
            }
        } 
        if (!$contactCat->isClient()){
            return false;
        }
        
        //TODO - move this to AbstractContactCatClient
        
        $contactSearch = ContactFactory::search();
        $contactSearch->filter('id', $this->getProperty('id'));
        $this->addClientRestrictionFiltersToDataSearch($contactSearch);

        $contactArray = $contactSearch->select();
        if (!empty($contactArray)) {
            $this->viewable = true;
            return true;
        }
        return false;
    }
    
    public function getIsUserLinkedToThis(AbstractUser $user = NULL) {
        if (empty($user)) {
            $user = Login::getUser();
        }
        if (!empty($user)) {
            $sourceUserId = $this->getProperty('source_user_id');
            $userId = $user->getId();
            if (!empty($sourceUserId) && !empty($userId) && $sourceUserId === $userId) {
                return true;
            }
        }
        return false;
    }
    
    public function getIsEditable() {
        if ($this->isAddable()) { //Addable permission is required
            if(Permission::verifyByRef('edit_contacts') || $this->getProperty('uid') == Login::getUserId()){
                return parent::getIsEditable();
            }
//            $assignedToSearch = AssignedToContactFactory::search();
//            $assignedToSearch->filter('user_id', $userId);
//            $assignedToSearch->filter('contact_id', $this->getProperty('id'));
//            $results = $assignedToSearch->select();
//            if (!empty($results)) {
//                return parent::getIsEditable();
//            }
            
            //Check contact category permission
            $contactCat = $this->getContactCat();
            if (!empty($contactCat)) {
                if ($contactCat->isEditable()) {
                    return parent::getIsEditable();
                }
            }
        }
        return false;
    }
    
    public function getIsDeleteable() {
       if(Permission::verifyByRef('delete_contacts')){// $this->getProperty('uid') == Login::getUserId() doesn't apply because even it's mine, the user can't delete it without a permission
            return parent::getIsDeleteable();
        }
            
        //Check contact category permission
        $contactCat = $this->getContactCat();
        if (!empty($contactCat)) {
            if ($contactCat->isDeleteable()) {
                return parent::getIsDeleteable();
            }
        }
        return false;
    }
    
    /**
     * @return User
     */
    public function getUser(){
        if(is_null($this->sourceUser)){
            $contactId = $this->getId();
            $sourceUser = UserFactory::getByContactId($contactId);
            if(empty($sourceUser)){
                $sourceUser = UserFactory::buildNewModel('unconfirmed');
            }
            $this->sourceUser = $sourceUser;
        }
        return $this->sourceUser;
    }
    
    public function getLoginEmail(){
        $user = $this->getUser();
        if($user){
            return $user->getProperty('email');
        }
        return NULL;
    }
    
    protected function saveContactAsUserFromForm() {
        $user = $this->getUser();
        $userId = $user->getId();
        $loginEmail = filter_input(INPUT_POST, 'login_email');
        if ($loginEmail) {
            if (empty($userId) && Permission::verifyByRef('add_users')) {
                $addUser = filter_input(INPUT_POST, 'add_user_to_system');
                if (!empty($addUser)) {
                    $user->setProperty('email', $loginEmail);
                    $this->setUserValues($user);
                    $userPass = filter_input(INPUT_POST, 'user_pass');
                    $salt = $user->generateSalt();
                    $saltyPass = $user->generateSaltyPass($userPass, $salt);
                    $user->setProperty('pass', $saltyPass);
                    $user->setProperty('salt', $salt);
                    if (!$user->save()) {
                        return false;
                    }
                    $userId = $user->getId();
                    $this->setProperty('source_user_id', $userId);
                    $roleId = (int) filter_input(INPUT_POST, 'role_id');
                    if (!empty($roleId)) {
                        if (!$user->setAndSaveUserRoles($roleId)) {
                            return false;
                        }
                    }
                    if (!$this->save()) {
                        return false;
                    }
                }
            } else if (!empty($userId) && Permission::verifyByRef('edit_users')) {
                $user->setProperty('email', $loginEmail);
                $this->setUserValues($user);
                $overwritePassword = filter_input(INPUT_POST, 'overwrite_user_password');
                if (!empty($overwritePassword)) {
                    $userPass = filter_input(INPUT_POST, 'user_pass');
                    $salt = $user->generateSalt();
                    $saltyPass = $user->generateSaltyPass($userPass, $salt);
                    $user->setProperty('pass', $saltyPass);
                    $user->setProperty('salt', $salt);
                }
                if (!$user->save()) {
                    return false;
                }
                $userId = $user->getId();
                $this->setProperty('source_user_id', $userId);
                if (!$this->save()) {
                    return false;
                }
            }
        } else {
            $this->setProperty('source_user_id', NULL);
        }

        return true;
    }

    protected function setUserValues(AbstractUser $user){
        return true;
    }
    
    protected function validateUserFields(GI_Form $form) {
        $existingUser = $this->getUser();
        $existingUserId = $existingUser->getProperty('id');
        $userNew = true;
        if (!empty($existingUserId)) {
            $userNew = false;
        }
        if (Permission::verifyByRef('add_users') && $userNew) {
            $addUserToSystem = filter_input(INPUT_POST, 'add_user_to_system');
            if (empty($addUserToSystem)) {
                return true;
            }
            if (!$this->validateUserEmailField($form)) {
                return false;
            }
            if (!$this->validateUserPasswordFields($form)) {
                return false;
            }
        } else if (Permission::verifyByRef('edit_users') && !$userNew) {
            if (!$this->validateUserEmailField($form)) {
                return false;
            }
            $overwritePassword = filter_input(INPUT_POST, 'overwrite_user_password');
            if (!empty($overwritePassword) && !$this->validateUserPasswordFields($form)) {
                return false;
            }
        }
        return true;
    }

    protected function validateUserEmailField(GI_Form $form) {
        $loginEmail = filter_input(INPUT_POST, 'login_email');
        if ($loginEmail) {
            $existingEmail = UserFactory::existingEmail($loginEmail, $this->getProperty('source_user_id'));
            if ($existingEmail) {
                $form->addFieldError('login_email', 'existing', 'This email is already being used by another user.');
                return false;
            }
        }
        return true;
    }
    
    protected function validateUserPasswordFields(GI_Form $form) {
        $userPass = filter_input(INPUT_POST, 'user_pass');
        $userPassConfirm = filter_input(INPUT_POST, 'user_pass_confirm');
        if ($userPass !== $userPassConfirm) {
            $form->addFieldError('user_pass_confirm', 'no_match', 'The passwords do not match.');
            return false;
        }
        return true;
    }

    protected function validateContactCatForms(GI_Form $form) {
        $selectedCategoryType = filter_input(INPUT_POST, 'categories');
        $hiddenCategoryTypes = filter_input(INPUT_POST, 'hidden_categories');
        if (empty($selectedCategoryType) && empty($hiddenCategoryTypes)) {
            $form->addFieldError('categories', 'at_least_one', 'You must select at least one category.');
            return false;
        }
        if (!empty($selectedCategoryType)) {
            $sampleModel = ContactCatFactory::buildNewModel($selectedCategoryType);
            if (empty($sampleModel)) {
                return false;
            }
            if (!$sampleModel->validateForm($form)) {
                return false;
            }
        }

        return true;
    }
    
    protected function validateContactInfoForms($form) {
        $pTypeRefs = filter_input(INPUT_POST, 'p_type_refs');
        if (empty($pTypeRefs)) {
            return true;
        }
        $pTypeRefsArray = explode(',', $pTypeRefs);
        foreach ($pTypeRefsArray as $pTypeRef) {
            //get the existing contact info array by type ref, with id as key
            $existingContactInfos = $this->getContactInfoArray($pTypeRef, true)[$pTypeRef];
            //get the suffix array using the pTypeRef
            $suffixArray = filter_input(INPUT_POST, $pTypeRef, FILTER_DEFAULT, FILTER_REQUIRE_ARRAY);
            if (!empty($suffixArray)) {
                $pos = 0;
                foreach ($suffixArray as $suffix) {
                    $idFromForm = filter_input(INPUT_POST, $pTypeRef . '_id_' . $suffix);
                    if (empty($idFromForm)) {
                        //make a new model
                        $contactInfo = ContactInfoFactory::buildNewModel($pTypeRef);
                    } else {
                        //get model from existing array
                        if (!isset($existingContactInfos[$idFromForm])) {
                            return false;
                        }
                        $contactInfo = $existingContactInfos[$idFromForm];
                        unset($existingContactInfos[$idFromForm]);
                    }
                    $contactInfo->setFieldSuffix($suffix);
                    $contactInfo->setProperty('pos', $pos);
                    $pos++;
                    if (!$contactInfo->validateForm($form)) {
                        return false;
                    }
                }
            }
        }
        
        return true;
    }

    /**
     * Get related Contact category models
     * @return AbstractContactCat[]
     */
    public function getContactCatModels() {
        return ContactCatFactory::search()
                ->filter('contact_id', $this->getProperty('id'))
                ->orderBy('id', 'ASC')
                ->setPageNumber(1)
                ->setItemsPerPage(1)
                ->select();
    }
    
//    /**
//     * @return \AbstractContactCat */
//    public function getContactCat(){
//        $contactCats = $this->getContactCatModels();
//        if($contactCats){
//            return $contactCats[0];
//        }
//        return NULL;
//    }
    
    public function getContactCat(){ 
        if (empty($this->contactCat)) {
            $array = $this->getContactCatModels();
            if (!empty($array)) {
                $this->contactCat = $array[0];
            }
        }
        return $this->contactCat;
    }
    
    
    /**
     * @return \AbstractContactCat 
     */
    public function getDefaultContactCat(){
        $catTypeRef = $this->defaultContactCatTypeRef;
        if(empty($catTypeRef)){
            $catTypeRef = 'category';
        }
        return ContactCatFactory::buildNewModel($catTypeRef);
    }
    
    
    
    /**
     * @param GI_Form $form
     * @return array
     */
    public function getContactCatModelsFromForm(GI_Form $form){
        $contactCatArray = array();
        if($form->wasSubmitted()){
            $selectedCategoryType = filter_input(INPUT_POST, 'categories');
            $selectedCategoryModel = ContactCatFactory::buildNewModel($selectedCategoryType);
            if (!empty($selectedCategoryModel)) {
                $selectedCategoryModel->setPropertiesFromForm($form);
                $selectedCategoryModel->setProperty('contact_id', $this->getProperty('id'));
                $contactCatArray[] = $selectedCategoryModel;
            }
        } else {
            if (!empty($this->getProperty('id'))) {
                //Get category models from database
                $contactCatArray = $this->getContactCatModels();
            } else {
                //Get category models from default typeref
                //$defaultContactCatModel = ContactCatFactory::buildNewModel($this->defaultContactCatTypeRef);
                $defaultContactCatModel = $this->getDefaultContactCat();
                if (!empty($defaultContactCatModel)) {
                    $defaultContactCatModel->setPropertiesFromForm($form);
                    $contactCatArray[] = $defaultContactCatModel;
                }
            }
        }
        
        return $contactCatArray;
    }
    
    /**
     * Get related Contact category model by type
     * @param string $typeRef
     * @param boolean $allStatus
     * @return AbstractContactCat
     */
    public function getContactCatModelByType($typeRef, $allStatus = false) {
        if (!empty($typeRef)) {
            $exampleTypeModel = ContactCatFactory::buildNewModel($typeRef);
            if (empty($exampleTypeModel)) {
                return NULL;
            }
            $contactCatSearch = ContactCatFactory::search()
                ->filterByTypeRef($typeRef)
                ->filter('contact_id', $this->getProperty('id'));
            if ($allStatus) {
                $contactCatSearch->setAutoStatus(false);
            }
            $contactCats = $contactCatSearch->select();
            if (!empty($contactCats)) {
                return $contactCats[0];
            }
        }
        return NULL;
    }
    
    /**
     * Get related Contact category models including soft-deleted categories
     * @deprecated since 3.0.21
     * @return ContactCats
     */
    public function getContactCatModelsIncludingSoftDeleted() {
        return ContactCatFactory::search()
                ->setAutoStatus(false)
                ->filter('contact_id', $this->getProperty('id'))
                ->select();
    }
    
    /**
     * Get related Contact category array
     * @return string[]
     */
    public function getContactCatTypeRefArray($idAsKey = false) {
        $categoryModels = $this->getContactCatModels();
        if (!empty($categoryModels)) {
            $categoryArray = array();
            foreach ($categoryModels as $categoryModel) {
                if ($idAsKey) {
                    $categoryArray[$categoryModel->getProperty('id')] = $categoryModel->getTypeRef();
                } else {
                    $categoryArray[$categoryModel->getTypeRef()] = $categoryModel->getTypeRef();
                }
            }
            
            return $categoryArray;
        }
        
        return NULL;
    }
    
    /**
     * Get contact categories text
     * @param type $delimeter
     * @return string
     */
    public function getContactCatText($delimeter = ', ') {
        $categoryModels = $this->getContactCatModels();
        if (!empty($categoryModels)) {
            $categoryArray = array();
            foreach ($categoryModels as $categoryModel) {
                $categoryArray[] = $categoryModel->getTypeTitle();
            }
            
            return implode($delimeter, $categoryArray);
        }
        
        return '';
    }
    
    /**
     * Get related Contact events models
     * @return ContactCats
     */
    public function getContactEventModels($type = 'event') {
        $sampleContactEvent = ContactEventFactory::buildNewModel($type);
        $contactEventSearch = ContactEventFactory::search()
                ->filterByTypeRef($type)
                ->filter('contact_id', $this->getProperty('id'));
        $sampleContactEvent->addSortingToDataSearch($contactEventSearch);
        $sampleContactEvent->addContactCatJoinsToDataSearch($contactEventSearch);
        $sampleContactEvent->addCustomFiltersToDataSearch($contactEventSearch);
        return $contactEventSearch->select();
    }
    
    /** 
     * Get contact event index view
     * @return \ContactEventIndexView
     */
    public function getEventListView($eventType = 'event') {
        $contactEvents = $this->getContactEventModels();
        $sampleContactEvent = ContactEventFactory::buildNewModel($eventType);
        $emptyTableView = new UITableView();
        $view = new ContactEventIndexView($contactEvents, $emptyTableView, $sampleContactEvent);
        $view->setAddOuterWrap(false);
        return $view;
    }
    
    public function isEventAddable() {
        if(Permission::verifyByRef('add_c_events')) {
            return parent::getIsAddable();
        }
        //Check contact category permission
        $contactCat = $this->getContactCat();
        if (!empty($contactCat)) {
            if ($contactCat->isEventAddable()) {
                return true;
            }
        }
        return false;
    }
    /** 
     * Get the URL to add contact event
     * @return string
     */
    public function getAddEventURL() {
        return GI_URLUtils::buildURL(array(
            'controller' => 'contactevent',
            'action' => 'add',
            'pId' => $this->getId(),
        ));
    }
    
    /**
     * Gets avatar view
     * 
     * @param string $type
     * @return GI_View
     */
    public function getAvatarView($type = 'avatar') {
        $imgFolder = $this->getImageFolder();
        
        $files = FolderFactory::getFiles($imgFolder);
        if (!empty($files)) {
            $file = $files[0];
            /* @var $file File */
            $avatarView = $file->getView($type);
            return $avatarView;
        }
        return NULL;
    }
    
    /**
     * Get related order models
     * @return ContactCats
     */
    public function getOrderModels($type) {
        return OrderFactory::search()
                ->filterByTypeRef($type)
                ->filter('contact_id', $this->getProperty('id'))
                ->select();
    }
    
    public function getInitials(){
        $initials = substr($this->getName(), 0, 1);
        return $initials;
    }
    
    public function getAvatarClass(){
        $colour = $this->getColour();
        $avatarClass = 'light_font';
        if(!GI_Colour::useLightFont($colour)){
            $avatarClass = 'dark_font';
        }
        $avatarClass .= ' contact_avatar';
        return $avatarClass;
    }
    
    public function getAvatarHTML($width = NULL, $height = NULL){
        $avatarView = $this->getAvatarView();
        $colour = $this->getColour();
        $avatarClass = $this->getAvatarClass();
        if($avatarView){
            $avatarClass .= ' has_img';
        }
        $avatarHTML = '<span class="avatar_wrap inline_block ' . $avatarClass . '" style="background: #' . $colour . ';">';
        $initials = $this->getInitials();
        $avatarHTML .= '<span class="avatar_initials">' . $initials . '</span>';
        if($avatarView){
            if(!is_null($width) && !is_null($height)){
                $avatarView->setSize($width, $height);
            }
            $avatarHTML .= $avatarView->getHTMLView();
        }
        $avatarHTML .= '</span>';
        return $avatarHTML;
    }
    
    /**
     * @todo implement searchRestricted
     * @return GI_DataSearch
     */
    public static function searchRestricted(){
        return parent::search();
    }
    
    /**
     * Get link buttons on the contact detail page
     * @param type $id
     * @param type $relation
     * @return string
     */
     
    public function getLinkButtons($id, $relation = NULL) {
        $manageRelationshipURL = GI_URLUtils::buildURL(array(
            'controller' => 'contact',
            'action' => 'manageRelationship',
            'id' => $id,
            'type' => $this->getTypeRef(),
            'relation' => $relation,
        ));
        $html = '<a href="' . $manageRelationshipURL . '" title="Manage '.$this->getTypeTitle().'" class="custom_btn ajax_link">'.GI_StringUtils::getIcon('pencil').'<span class="btn_text">Manage</span></a>';
        return $html;
    }
    
    public function getManageRelationshipFormView(GI_Form $form, $linkedTypeRef, $relation, $buildForm = true) {
        $formView = new ContactManageRelationshipFormView($form, $this, $linkedTypeRef, $relation);
        if($buildForm){
            $formView->buildForm();
        }
        return $formView;
    }


    public function getContactRelationshipsWithIdKey($typeRef = NULL, $relation = 'child') {
        $contactRelationshipModels = $this->getContactRelationships($typeRef, $relation);
        $modelsWithIdKey = array();
        foreach ($contactRelationshipModels as $contactRelationshipModel) {
            $modelsWithIdKey[$contactRelationshipModel->getId()] = $contactRelationshipModel;
        }
        return $modelsWithIdKey;
    }
    
    /**
     * @param GI_Form $form
     * @param ContactRelationship[] $dbModelsToRemove
     * @return ContactRelationship[]
     */
    public function getMergeContactRelationships(GI_Form $form = NULL, $relation = 'child', &$dbModelsToRemove = array()){
        $relationships = array();
        if(!empty($form) && $form->wasSubmitted()){
            $seqNums = filter_input(INPUT_POST, 'contact_relationships', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY);
            foreach($seqNums as $seqNum){
                $submittedRelationshipId = filter_input(INPUT_POST,'contact_relationship_id_' . $seqNum);
                $typeRef = filter_input(INPUT_POST, 'contact_relationship_type_' . $seqNum);
                if(!empty($submittedRelationshipId)){
                    if(isset($dbModelsToRemove[$submittedRelationshipId])){
                        $contactRelationship = $dbModelsToRemove[$submittedRelationshipId];
                    } else {
                        $contactRelationship = ContactRelationshipFactory::getModelById($submittedRelationshipId);
                    }
                    if ($typeRef !== $contactRelationship->getTypeRef()) {
                        $contactRelationship = ContactRelationshipFactory::changeModelType($contactRelationship, $typeRef);
                    }
                    $contactRelationship->setSeqNumber($seqNum);
                } else {
                    //Build a new model
                    if (empty($typeRef)) {
                        $typeRef = 'relationship';
                    }
                    $contactRelationship = ContactRelationshipFactory::buildNewModel($typeRef);
                    $contactRelationship->setSeqNumber($seqNum);
                        
                    //Check if there is a record with the same ids in db records
                    $submittedPContactId = filter_input(INPUT_POST, $contactRelationship->getFieldName('p_contact_id'));
                    $submittedCContactId = filter_input(INPUT_POST, $contactRelationship->getFieldName('c_contact_id'));
            
                    $duplicatedRelationships = ContactRelationshipFactory::search()
                        ->setAutoStatus(false)
                        ->filter('p_contact_id', $submittedPContactId)
                        ->filter('c_contact_id', $submittedCContactId)
                        ->select();
                    if (!empty($duplicatedRelationships)) {
                        //If there is a record with the same ids, replace it
                        $contactRelationship = $duplicatedRelationships[0];
                        $contactRelationship->setProperty('status', 1);
                        $contactRelationship->setSeqNumber($seqNum);
                    } else {
                        $contactRelationship->setProperty('p_contact_id', $submittedPContactId);
                        $contactRelationship->setProperty('c_contact_id', $submittedCContactId);
                    }
                }
                //Remove duplicated relationships on the input form
                if ($relation == 'parent') {
                    $key = $contactRelationship->getProperty('p_contact_id');
                } else if ($relation == 'child') {
                    $key = $contactRelationship->getProperty('c_contact_id');
                }
                if (isset($relationships[$key])) {
                    //One that has relation ID is prior
                    if (empty($relationships[$key]->getProperty('id'))) {
                        //Relpace the latest one
                        $relationships[$key] = $contactRelationship;
                    }
                } else {
                    $relationships[$key] = $contactRelationship;
                }
            }
            
            //Remove submitted model from removable models
            foreach ($relationships as $relationship) {
                $newId = $relationship->getProperty('id');
                if (!empty($newId) && isset($dbModelsToRemove[$newId])) {
                    unset($dbModelsToRemove[$newId]);
                }
            }
        } else {
            $seqNum = 1;
            foreach($dbModelsToRemove as $dbModel){
                $dbModel->setSeqNumber($seqNum++);
                $relationships[] = $dbModel;
            }
        }
        
        return $relationships;
    }
    
    
    /**
     * Submit handler for Relationship management page
     * @param type $form
     * @return boolean
     */
    public function handleManageRelationshipFormSubmission($form) {
        if ($form->wasSubmitted()) {
            $linkedTypeRef = filter_input(INPUT_POST, 'linked_type');
            $relation = filter_input(INPUT_POST, 'relation');
            $dbRelationshipsToRemove = $this->getContactRelationshipsWithIdKey($linkedTypeRef, $relation);
            //Merge DB records and form records
            $mergedContactRelationships = $this->getMergeContactRelationships($form, $relation, $dbRelationshipsToRemove);
            foreach($mergedContactRelationships as $mergedContactRelationship){
                if(!$mergedContactRelationship->handleRowsFormSubmission($form)){
                    return false;
                }
            }
            foreach($dbRelationshipsToRemove as $dbRelationshipToRemove){
                $dbRelationshipToRemove->softDelete();
            }
            return true;
        }
    }
    
    /**
     * @return AbstractInvDiscount[]
     */
    public function getInvDiscounts() {
        if (is_null($this->invDiscounts)) {
            $this->invDiscounts = InvDiscountFactory::getByContact($this);
        }
        return $this->invDiscounts;
    }

    public function getViewURLAttributes() {
        return array(
            'controller' => 'contact',
            'action' => 'view',
            'id' => $this->getProperty('id')
        );
    }

    public function addCustomFiltersToDataSearch(GI_DataSearch $dataSearch) {
        if (!Permission::verifyByRef('view_contacts')) {
            if (!Permission::verifyByRef('view_contact_clients')) {
                //Exclude clients except for ones assigned to login id or created by login id
                $this->addClientRestrictionFiltersToDataSearch($dataSearch);
            }

            if (!Permission::verifyByRef('view_contact_vendors')) {
                //Exclude vendors except for ones created by login id
                $this->addVendorRestrictionFiltersToDataSearch($dataSearch);
            }

            if (!Permission::verifyByRef('view_contact_internals')) {
                //Exclude internal contacts except for ones created by login id
                $this->addInternalRestrictionFiltersToDataSearch($dataSearch);
            }
        }
        
        $internalOnly = $dataSearch->getSearchValue('internal_only');
        if($internalOnly){
            // internal column is deprecated 
            //$dataSearch->filter('internal', 1);
            $this->addInternalOnlyFiltersToDataSearch($dataSearch);
        }
        $dataSearch->groupBy('id');
        return $dataSearch;
    }
    
    public function addSortingToDataSearch(GI_DataSearch $dataSearch){
        return $dataSearch;
    }

    protected function addContactCatJoinsToClientDataSearch(GI_DataSearch $dataSearch) {
        $clientType = TypeModelFactory::getTypeModelByRef('client', 'contact_cat_type');
        $contactTableName = $dataSearch->prefixTableName('contact');
        
        if(!$dataSearch->isJoinedWithTable('CCNOTCLIENT')){
            $dataSearch->createJoin('contact_cat', 'contact_id', $contactTableName, 'id', 'CCNOTCLIENT', 'left')
                    ->filterNotEqualTo('CCNOTCLIENT.contact_cat_type_id', $clientType->getProperty('id'));
        }
          
        if(!$dataSearch->isJoinedWithTable('CCCLIENT')){
            $dataSearch->createJoin('contact_cat', 'contact_id', $contactTableName, 'id', 'CCCLIENT', 'left')
                    ->filter('CCCLIENT.contact_cat_type_id', $clientType->getProperty('id'));
        }
        
        if(!$dataSearch->isJoinedWithTable('CREL')){
            $dataSearch->join('contact_relationship', 'c_contact_id', $contactTableName, 'id', 'CREL', 'left');
        }
    }
    
    public function addClientRestrictionFiltersToDataSearch(GI_DataSearch $dataSearch) {
        $userId = Login::getUserId();
        $sourceContact = ContactFactory::getBySourceUserId($userId);
        if (empty($sourceContact)) {
            GI_URLUtils::redirectToAccessDenied();
        }
        $clientType = TypeModelFactory::getTypeModelByRef('client', 'contact_cat_type');
        if(!$dataSearch->isJoinedWithTable('CCNOTCLIENT') || !$dataSearch->isJoinedWithTable('CCCLIENT') || !$dataSearch->isJoinedWithTable('CREL')){
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

    protected function addContactCatJoinsToVendorDataSearch(GI_DataSearch $dataSearch) {
        $contactTableName = $dataSearch->prefixTableName('contact');
        if(!$dataSearch->isJoinedWithTable('VCONCAT')){
            $dataSearch->join('contact_cat', 'contact_id', $contactTableName, 'id', 'VCONCAT', 'left');
        }
    }
    
    public function addVendorRestrictionFiltersToDataSearch(GI_DataSearch $dataSearch) {
        $userId = Login::getUserId();
        $vendorType = TypeModelFactory::getTypeModelByRef('vendor', 'contact_cat_type');
        if(!$dataSearch->isJoinedWithTable('VCONCAT')){
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
    
    protected function addContactCatJoinsToInternalDataSearch(GI_DataSearch $dataSearch) {
        $contactTableName = $dataSearch->prefixTableName('contact');
        if(!$dataSearch->isJoinedWithTable('ICONCAT')){
            $dataSearch->join('contact_cat', 'contact_id', $contactTableName, 'id', 'ICONCAT', 'left');
        }
    }
    
    public function addInternalRestrictionFiltersToDataSearch(GI_DataSearch $dataSearch) {
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
    
    protected function addInternalOnlyFiltersToDataSearch(GI_DataSearch $dataSearch) {
        $internalType = TypeModelFactory::getTypeModelByRef('internal', 'contact_cat_type');
        $contactTableName = ContactFactory::getDbPrefix() . 'contact';
        $dataSearch->join('contact_cat', 'contact_id', $contactTableName, 'id', 'ICONCAT')
                ->filter('ICONCAT.contact_cat_type_id', $internalType->getProperty('id'));
        return $dataSearch;
    }
    
    public function getOutstandingFinalizedInvoiceBalance() {
        $contactQB = $this->getContactQB();
        if (!empty($contactQB)) {
            return $contactQB->getOutstandingInvoiceBalance();
        }
        return NULL;
    }
    
    /**
     * @Deprecated - these values come from QB now - use getOutstandingFinalizedInvoiceBalance() to retrieve value
     * - The value is updated through other mechanisms
     * @return Boolean
     */
    public function updateOutstandingFinalizedInvoiceBalance() {
        $newBalance = $this->getOutstandingFinalizedInvoiceBalance();
        $this->setProperty('cad_out_inv_bal', $newBalance);
        return $this->save();
    }
    
    public function isOrganization() {
        return false;
    }
    
    public function isIndividual() {
        return false;
    }
    
    public function isLocation() {
        return false;
    }
    
    /**
     * @return boolean
     */
    public function markAsInternal() {
        if (empty($this->getProperty('internal'))) {
            $internalContactCat = ContactCatFactory::getModelByContactAndTypeRef($this, 'internal');
            if (empty($internalContactCat)) {
                $buildNewModel = true;
                $softDeletedSearch = ContactCatFactory::search();
                $softDeletedSearch->filterByTypeRef('internal')
                        ->filter('contact_id', $this->getProperty('id'))
                        ->filter('status', 0);
                $softDeletedArray = $softDeletedSearch->select();
                if (!empty($softDeletedArray)) {
                    $internalContactCat = $softDeletedArray[0];
                    if ($internalContactCat->unSoftDelete()) {
                        $buildNewModel = false;
                    }
                }
                if ($buildNewModel) {
                    $internalContactCat = ContactCatFactory::buildNewModel('internal');
                }
                $internalContactCat->setProperty('contact_id', $this->getProperty('id'));
                if (!$internalContactCat->save()) {
                    return false;
                }
            }
            $this->setProperty('internal', 1);
            if (!$this->save()) {
                return false;
            }
        }
        $childContacts = $this->getChildContacts();
        if (!empty($childContacts)) {
            foreach ($childContacts as $childContact) {
                if (!$childContact->markAsInternal()) {
                    return false;
                }
            }
        }
        return true;
    }
    
    /**
     * @return boolean
     */
    public function markAsNotInternal() {
        if (!empty($this->getProperty('internal'))) {
            $internalContactCats = ContactCatFactory::getModelsByContact($this, array('internal'));
            if (!empty($internalContactCats)) {
                foreach ($internalContactCats as $internalContactCat) {
                    $internalContactCat->setProperty('contact_id', NULL);
                    if (!$internalContactCat->softDelete()) {
                        return false;
                    }
                }
            }
            $this->setProperty('internal', 0);
            if (!$this->save()) {
                return false;
            }
        }
        $childContacts = $this->getChildContacts();
        if (!empty($childContacts)) {
            foreach ($childContacts as $childContact) {
                if (!$childContact->markAsNotInternal()) {
                    return false;
                }
            }
        }
        return true;
    }
    
    public function exportToQuickbooks() {
        $contactQB = $this->getContactQB();
        if (empty($contactQB) || !$contactQB->exportToQB()) {
            return false;
        }
        return true;
    }

    public function isQuickbooksExportable() {
        if (!ProjectConfig::getIsQuickbooksIntegrated()) {
            return false;
        }
        if ($this->isClient() || $this->isVendor() || $this->isShipper()) {
            return true;
        }
        return false;
    }
    
    
    public function isQuickbooksImportable() {
        if (!ProjectConfig::getIsQuickbooksIntegrated()) {
            return false;
        }
        if (!empty($this->getContactQB())) {
            return true;
        }
        return false;
    }

    public function isClient() {
        if (is_null($this->isClient)) {
            $contactCatClient = $this->getContactCatModelByType('client');
            if (!empty($contactCatClient)) {
                $this->isClient = true;
            } else {
                $this->isClient = false;
            }
        }
        return $this->isClient;
    }

    public function isVendor() {
        if (is_null($this->isVendor)) {
            $contactCatVendor = $this->getContactCatModelByType('vendor');
            if (!empty($contactCatVendor)) {
                $this->isVendor = true;
            } else {
                $this->isVendor = false;
            }
        }
        return $this->isVendor;
    }
    
    public function isShipper() {
        if (is_null($this->isShipper)) {
            $contactCatShipper = $this->getContactCatModelByType('shipper');
            if (!empty($contactCatShipper)) {
                $this->isShipper = true;
            } else {
                $tags = $this->getTags();
                if (!empty($tags)) {
                    foreach ($tags as $tag) {
                        if ($tag->getProperty('ref') == 'shipper') {
                            $this->isShipper = true;
                            return $this->isShipper;
                        }
                    }
                }
                $this->isShipper = false;
            }
        }
        return $this->isShipper;
    }
    
    public function isInternal() {
        if (is_null($this->isInternal)) {
            $contactCatInternal=  $this->getContactCatModelByType('internal');
            if (!empty($contactCatInternal)) {
                $this->isInternal = true;
            } else {
                $this->isInternal = false;
            }
        }
        return $this->isInternal;
    }
    
    public function getContactQB() {
        if (empty($this->contactQB)) {
            $this->contactQB = ContactQBFactory::getModelById($this->getProperty('contact_qb_id'));
        }
        return $this->contactQB;
    }
    
    public function getQuickbooksId() {
        $contactQB = $this->getContactQB();
        if (!empty($contactQB)) {
            return $contactQB->getQuickbooksId();
        }
        return NULL;
    }
    
    public function getQuickbooksExportDate() {
        $contactQB = $this->getContactQB();
        if (!empty($contactQB)) {
            $timestamp = $contactQB->getProperty('qb_export_date');
            if (!empty($timestamp)) {
                $dateTime = new DateTime($timestamp);
                return $dateTime->format('Y-m-d H:i:s');
            }
        }
        return NULL;
    }

    public function getQuickbooksImportDate() {
        $contactQB = $this->getContactQB();
        if (!empty($contactQB)) {
            $timestamp = $contactQB->getProperty('qb_import_date');
            if (!empty($timestamp)) {
                $dateTime = new DateTime($timestamp);
                return $dateTime->format('Y-m-d H:i:s');
            }
        }
        return NULL;
    }

    public function hasBeenExportedToQuickbooks() {
        $contactQB = $this->getContactQB();
        if (!empty($contactQB)) {
            return true;
        }
        return false;
    }
    
    /** @return String - the QB id of the tax code */
    public function getQuickbooksDefaultTaxCodeRef() {
        $contactQB = $this->getContactQB();
        if (!empty($contactQB)) {
            return $contactQB->getQuickbooksDefaultTaxCodeRef();
        }
        return NULL;
    }

    public function getQuickbooksDisplayName() {
        $contactQB = $this->getContactQB();
        if (!empty($contactQB)) {
            return $contactQB->getProperty('display_name');
        }
        return '';
    }

    public function getQBExportTitle(){
        return $this->getQuickbooksDisplayName();
    }
    
    
    public function addressMatchesQuickbooksBillAddress() {
        $contactQB = $this->getContactQB();
        if (!empty($contactQB)) {
            $qbBillingAddress = $this->getQBBillingAddress();
            if (!empty($qbBillingAddress)) {
                return true;
            }
        }
        return false;
    }

    public function quickbooksBillingAddressIsMailAcceptable() {
        $quickbooksAddress = $this->getQBBillingAddress();
        if (!empty($quickbooksAddress)) {
            if (empty($quickbooksAddress->getProperty('contact_info_address.addr_street'))) {
                return false;
            }
            if (empty($quickbooksAddress->getProperty('contact_info_address.addr_city'))) {
                return false;
            }
            if (empty($quickbooksAddress->getProperty('contact_info_address.addr_region'))) {
                return false;
            }
            if (empty($quickbooksAddress->getProperty('contact_info_address.addr_country'))) {
                return false;
            }
            if (empty($quickbooksAddress->getProperty('contact_info_address.addr_code'))) {
                return false;
            }
            return true;
        }
        return false;
    }

    /**
     * @return AbstractContactInfoPhoneNum
     */
    public function getQBPhoneNumber() {
        $contactInfos = ContactInfoFactory::getContactInfosByContact($this, 'phone_num', false, false, true);
        if (!empty($contactInfos)) {
            $contactInfo = $contactInfos[0];
            return $contactInfo;
        }
        return NULL;
    }

    /**
     * @return AbstractContactInfoPhoneNum
     */
    public function getQBMobileNumber() {
        return $this->getContactInfo('mobile_phone_num', true);
    }

    /**
     * @return AbstractContactInfoPhoneNum
     */
    public function getQBFaxNumber() {
        return $this->getContactInfo('fax_num', true);
    }

    /**
     * @return AbstractContactInfoPhoneNum
     */
    public function getQBOtherNumber() {
        return $this->getContactInfo('other_phone_num', true);
    }

    /**
     * @return AbstractContactInfoAddress
     */
    public function getQBBillingAddress() {
        return $this->getContactInfo('billing_address', true);
    }

    /**
     * @return AbstractContactInfoAddress
     */
    public function getQBShippingAddress() {
        return $this->getContactInfo('shipping_address', true);
    }
    
    /**
     * @return AbstractContactInfoEmailAddr
     */
    public function getQBEmailAddress() {
        return $this->getContactInfo('email_address', true);
    }
    
    public function handleImportFromQBFormSubmission(GI_Form $form) {
        if ($form->wasSubmitted() && $form->validate()) {
            if (!$this->handleImportFromQBFormFields($form)) {
                return false;
            }
            if (!$this->save()) {
                return NULL;
            }
            if (!$this->handleImportFromQBFormContactInfoUpdates($form)) {
                return NULL;
            }
            $contactQB = $this->getContactQB();
            if (empty($contactQB)) {
                return NULL;
            }
            $contactQB->setProperty('import_required', 0);
            $contactQB->setProperty('export_required', 0);
            $contactQB->setProperty('qb_import_date', GI_Time::getDateTime());
            if (!$contactQB->save()) {
                return NULL;
            }
            return $this;
        }
        return NULL;
    }

    protected function handleImportFromQBFormFields(GI_Form $form) {
        if ($form->wasSubmitted() && $form->validate()) {
            $contactQB = $this->getContactQB();
            if (!empty($contactQB)) {
                $fullyQualifiedName = $contactQB->getProperty('fully_qualified_name');
                $this->setProperty('fully_qualified_name', $fullyQualifiedName);
            }
            return true;
        }
        return false;
    }
    
    protected function handleImportFromQBFormContactInfoUpdates(GI_Form $form) {
        if ($form->wasSubmitted() && $form->validate()) {
            //Billing Address
            if (!$this->updateQBBillingAddressFromImportForm($form)) {
                return false;
            }
            //Shipping Address
            if (!$this->updateQBShippingAddressFromImportForm($form)) {
                return false;
            }
            //Email
            if (!$this->updateQBEmailAddressFromImportForm($form)) {
                return false;
            }
            //Primary Phone
            if (!$this->updateQBPhoneNumberFromImportForm($form)) {
                return false;
            }
            //Mobile
            if (!$this->updateQBMobileNumberFromImportForm($form)) {
                return false;
            }
            //Fax
            if (!$this->updateQBFaxNumberFromImportForm($form)) {
                return false;
            }
            //Other Phone
            if (!$this->updateQBOtherNumberFromImportForm($form)) {
                return false;
            }
            return true;
        }
        return false;
    }

    protected function updateQBBillingAddressFromImportForm(GI_Form $form) {
        if ($form->wasSubmitted() && $form->validate()) {
            $billAddressId = filter_input(INPUT_POST, 'bill_address_id');
            if (empty($billAddressId)) {
                return true;
            }
            $existingBillingAddress = $this->getQBBillingAddress();
            if ($billAddressId == 'new') {
                $billAddress = ContactInfoFactory::buildNewModel('billing_address');
            } else {
                if (!empty($existingBillingAddress) && ($billAddressId == $existingBillingAddress->getId())) {
                    $billAddress = $existingBillingAddress;
                } else {
                    $billAddress = ContactInfoFactory::getModelById($billAddressId);
                }
            }
            $contactQB = $this->getContactQB();
            if (empty($contactQB)) {
               return false;
           }
           $billAddress = $contactQB->createBillingAddressContactInfoFromData('billing_address', $billAddress);
           if (empty($billAddress)) {
               return false;
           }
           $billAddress->setProperty('qb_linked', 1);
           $billAddress->setProperty('contact_id', $this->getProperty('id'));
           if (!$billAddress->save()) {
               return false;
           }
           if (!empty($existingBillingAddress) && ($billAddress->getId() != $existingBillingAddress->getId())) {
               $existingBillingAddress->setProperty('qb_linked', 0);
               if (!$existingBillingAddress->save()) {
                   return false;
               }
           }
            return true;
        }
        return false;
    }

    protected function updateQBShippingAddressFromImportForm(GI_Form $form) {
        if ($form->wasSubmitted() && $form->validate()) {
            $shipAddressId = filter_input(INPUT_POST, 'ship_address_id');
            if (empty($shipAddressId)) {
                return true;
            }
            $existingShippingAddress = $this->getQBShippingAddress();
            if ($shipAddressId == 'new') {
                $shipAddress = ContactInfoFactory::buildNewModel('shipping_address');
            } else {
                if (!empty($existingShippingAddress) && ($shipAddressId == $existingShippingAddress->getId())) {
                    $shipAddress = $existingShippingAddress;
                } else {
                    $shipAddress = ContactInfoFactory::getModelById($shipAddressId);
                }
            }
            $contactQB = $this->getContactQB();
            if (empty($contactQB)) {
               return false;
           }
           $shipAddress = $contactQB->createShippingAddressContactInfoFromData('shipping_address', $shipAddress);
           if (empty($shipAddress)) {
               return false;
           }
           $shipAddress->setProperty('qb_linked', 1);
           $shipAddress->setProperty('contact_id', $this->getProperty('id'));
           if (!$shipAddress->save()) {
               return false;
           }
           if (!empty($existingShippingAddress) && ($shipAddress->getId() != $existingShippingAddress->getId())) {
               $existingShippingAddress->setProperty('qb_linked', 0);
               if (!$existingShippingAddress->save()) {
                   return false;
               }
           }
            return true;
        }
        return false;
    }

    protected function updateQBEmailAddressFromImportForm(GI_Form $form) {
        if ($form->wasSubmitted() && $form->validate()) {
            $emailId = filter_input(INPUT_POST, 'email_id');
            if (empty($emailId)) {
                return true;
            }
            $existingEmailModel = $this->getQBEmailAddress();
            if ($emailId == 'new') {
                $emailModel = ContactInfoFactory::buildNewModel('email_address');
            } else {
                if (!empty($existingEmailModel) && ($emailId == $existingEmailModel->getId())) {
                    $emailModel = $existingEmailModel;
                } else {
                    $emailModel = ContactInfoFactory::getModelById($emailId);
                }
                $emailModel = ContactInfoFactory::changeModelType($emailModel, 'email_address');
            }
            $contactQB = $this->getContactQB();
            if (empty($contactQB)) {
                return false;
            }
            $emailAddress = $contactQB->getProperty('email');
            if (empty($emailAddress)) {
                return true;
            }
            $emailModel->setProperty('contact_info_email_addr.email_address', $emailAddress);
            $emailModel->setProperty('qb_linked', 1);
            $emailModel->setProperty('contact_id', $this->getProperty('id'));
            if (!$emailModel->save()) {
                return false;
            }
            if (!empty($existingEmailModel) && ($emailModel->getId() != $existingEmailModel->getId())) {
                $existingEmailModel->setProperty('qb_linked', 0);
                if (!$existingEmailModel->save()) {
                    return false;
                }
            }
            return true;
        }
        return false;
    }

    protected function updateQBPhoneNumberFromImportForm(GI_Form $form) {
        if ($form->wasSubmitted() && $form->validate()) {
            $phoneId = filter_input(INPUT_POST, 'phone_id');
            if (empty($phoneId)) {
                return true;
            }
            $existingPhoneModel = $this->getQBPhoneNumber();
            if ($phoneId == 'new') {
                $phoneModel = ContactInfoFactory::buildNewModel('phone_num');
            } else {
                if (!empty($existingPhoneModel) && ($phoneId == $existingPhoneModel->getId())) {
                    $phoneModel = $existingPhoneModel;
                } else {
                    $phoneModel = ContactInfoFactory::getModelById($phoneId);
                }
                $phoneModel = ContactInfoFactory::changeModelType($phoneModel, 'phone_num');
            }
            $contactQB = $this->getContactQB();
            if (empty($contactQB)) {
                return false;
            }
            $phoneNum = $contactQB->getProperty('primary_phone');
            if (empty($phoneNum)) {
                return true;
            }
            $phoneModel->setProperty('contact_info_phone_num.phone', $phoneNum);
            $phoneModel->setProperty('qb_linked', 1);
            $phoneModel->setProperty('contact_id', $this->getProperty('id'));
            if (!$phoneModel->save()) {
                return false;
            }
            if (!empty($existingPhoneModel) && ($existingPhoneModel->getId() != $phoneId)) {
                $existingPhoneModel->setProperty('qb_linked', 0);
                if (!$existingPhoneModel->save()) {
                    return false;
                }
            }
            return true;
        }
        return false;
    }

    protected function updateQBMobileNumberFromImportForm(GI_Form $form) {
        if ($form->wasSubmitted() && $form->validate()) {
            $phoneId = filter_input(INPUT_POST, 'mobile_phone_id');
            if (empty($phoneId)) {
                return true;
            }
            $existingPhoneModel = $this->getQBMobileNumber();
            if ($phoneId == 'new') {
                $phoneModel = ContactInfoFactory::buildNewModel('mobile_phone_num');
            } else {
                if (!empty($existingPhoneModel) && ($phoneId == $existingPhoneModel->getId())) {
                    $phoneModel = $existingPhoneModel;
                } else {
                    $phoneModel = ContactInfoFactory::getModelById($phoneId);
                }
                $phoneModel = ContactInfoFactory::changeModelType($phoneModel, 'mobile_phone_num');
            }
            $contactQB = $this->getContactQB();
            if (empty($contactQB)) {
                return false;
            }
            $mobileNum = $contactQB->getProperty('mobile');
            if (empty($mobileNum)) {
                return true;
            }
            $phoneModel->setProperty('contact_info_phone_num.phone', $mobileNum);
            $phoneModel->setProperty('qb_linked', 1);
            $phoneModel->setProperty('contact_id', $this->getProperty('id'));
            if (!$phoneModel->save()) {
                return false;
            }
            if (!empty($existingPhoneModel) && ($existingPhoneModel->getId() != $phoneId)) {
                $existingPhoneModel->setProperty('qb_linked', 0);
                if (!$existingPhoneModel->save()) {
                    return false;
                }
            }
            return true;
        }
        return false;
    }

    protected function updateQBFaxNumberFromImportForm(GI_Form $form) {
        if ($form->wasSubmitted() && $form->validate()) {
            $phoneId = filter_input(INPUT_POST, 'fax_phone_id');
            if (empty($phoneId)) {
                return true;
            }
            $existingPhoneModel = $this->getQBFaxNumber();
            if ($phoneId == 'new') {
                $phoneModel = ContactInfoFactory::buildNewModel('fax_num');
            } else {
                if (!empty($existingPhoneModel) && ($phoneId == $existingPhoneModel->getId())) {
                    $phoneModel = $existingPhoneModel;
                } else {
                    $phoneModel = ContactInfoFactory::getModelById($phoneId);
                    $phoneModel = ContactInfoFactory::changeModelType($phoneModel, 'fax_num');
                }
            }
            $contactQB = $this->getContactQB();
            if (empty($contactQB)) {
                return false;
            }
            $faxNum = $contactQB->getProperty('fax');
            if (empty($faxNum)) {
                return true;
            }
            $phoneModel->setProperty('contact_info_phone_num.phone', $faxNum);
            $phoneModel->setProperty('qb_linked', 1);
            $phoneModel->setProperty('contact_id', $this->getProperty('id'));
            if (!$phoneModel->save()) {
                return false;
            }
            if (!empty($existingPhoneModel) && ($existingPhoneModel->getId() != $phoneId)) {
                $existingPhoneModel->setProperty('qb_linked', 0);
                if (!$existingPhoneModel->save()) {
                    return false;
                }
            }
            return true;
        }
        return false;
    }

    protected function updateQBOtherNumberFromImportForm(GI_Form $form) {
        if ($form->wasSubmitted() && $form->validate()) {
            $phoneId = filter_input(INPUT_POST, 'other_phone_id');
            if (empty($phoneId)) {
                return true;
            }
            $existingPhoneModel = $this->getQBOtherNumber();
            if ($phoneId == 'new') {
                $phoneModel = ContactInfoFactory::buildNewModel('other_phone_num');
            } else {
                if (!empty($existingPhoneModel) && ($phoneId == $existingPhoneModel->getId())) {
                    $phoneModel = $existingPhoneModel;
                } else {
                    $phoneModel = ContactInfoFactory::getModelById($phoneId);
                }
                $phoneModel = ContactInfoFactory::changeModelType($phoneModel, 'other_phone_num');
            }
            $contactQB = $this->getContactQB();
            if (empty($contactQB)) {
                return false;
            }
            $otherNum = $contactQB->getProperty('alternate_phone');
            if (empty($otherNum)) {
                return true;
            }
            $phoneModel->setProperty('contact_info_phone_num.phone', $otherNum);
            $phoneModel->setProperty('qb_linked', 1);
            $phoneModel->setProperty('contact_id', $this->getProperty('id'));
            if (!$phoneModel->save()) {
                return false;
            }
            if (!empty($existingPhoneModel) && ($existingPhoneModel->getId() != $phoneId)) {
                $existingPhoneModel->setProperty('qb_linked', 0);
                if (!$existingPhoneModel->save()) {
                    return false;
                }
            }
            return true;
        }
        return false;
    }

    public function handleExportToQBFormSubmission(GI_Form $form, AbstractContactQB $contactQB) {
        if ($form->wasSubmitted() && $form->validate()) {
            if (!$this->handleExportToQBFormFields($form, $contactQB)) {
                return false;
            }
            if (!$this->updateQBBillingAddressFromExportForm($form, $contactQB)) {
                return false;
            }
            if (!$this->updateQBShippingAddressFromExportForm($form, $contactQB)) {
                return false;
            }
            if (!$this->updateQBEmailAddressFromExportForm($form, $contactQB)) {
                return false;
            }
            if (!$this->updateQBPhoneNumberFromExportForm($form, $contactQB)) {
                return false;
            }
            if (!$this->updateQBMobileNumberFromExportForm($form, $contactQB)) {
                return false;
            }
            if (!$this->updateQBFaxNumberFromExportForm($form, $contactQB)) {
                return false;
            }
            if (!$this->updateQBOtherNumberFromExportForm($form, $contactQB)) {
                return false;
            }
            $defaultCurrency = $this->getDefaultCurrency();
            if (!empty($defaultCurrency)) {
                $currencyRef = $defaultCurrency->getProperty('name');
            } else {
                $currencyRef = strtoupper(ProjectConfig::getDefaultCurrencyRef());
            }
            $contactQB->setProperty('currency_ref', $currencyRef);
            $contactQB->setProperty('import_required', 0);
            $contactQB->setProperty('export_required', 0);
            $contactQB->setProperty('qb_export_date', GI_Time::getDateTime());
            if (!$contactQB->save()) {
                return false;
            }
            $this->setProperty('fully_qualified_name', $contactQB->getProperty('fully_qualified_name'));
            $this->setProperty('contact_qb_id', $contactQB->getId());
            if (!$this->save()) {
                return false;
            }
            return true;
        }
        return false;
    }

    protected function handleExportToQBFormFields(GI_Form $form, AbstractContactQB $contactQB) {
        if ($form->wasSubmitted() && $form->validate()) {
            //TODO - display name field?
            return true;
        }
        return false;
    }

    protected function updateQBBillingAddressFromExportForm(GI_Form $form, AbstractContactQB $contactQB) {
        if ($form->wasSubmitted() && $form->validate()) {
            $billAddressId = filter_input(INPUT_POST, 'bill_address_id');
            if (empty($billAddressId)) {
                return true;
            }
            $existingQBBillingAddress = $this->getQBBillingAddress();
            if ($billAddressId == 'dne') {
                if (!empty($existingQBBillingAddress)) {
                    $existingQBBillingAddress->setProperty('qb_linked', 0);
                    if (!$existingQBBillingAddress->save()) {
                        return false;
                    }
                }
            } else {
                $billAddress = ContactInfoFactory::changeModelType(ContactInfoFactory::getModelById($billAddressId), 'billing_address');
                if (empty($billAddress)) {
                    return false;
                }
                $contactQB->setProperty('bill_addr_line_1', $billAddress->getProperty('contact_info_address.addr_street'));
                $contactQB->setProperty('bill_addr_line_2', $billAddress->getProperty('contact_info_address.addr_street_two'));
                $contactQB->setProperty('bill_addr_city', $billAddress->getProperty('contact_info_address.addr_city'));
                $contactQB->setProperty('bill_addr_region', $billAddress->getProperty('contact_info_address.addr_region'));
                $countryCode = $billAddress->getProperty('contact_info_address.addr_country');
                $contactQB->setProperty('bill_addr_country', GeoDefinitions::getCountryNameFromCode($countryCode));
                $contactQB->setProperty('bill_addr_postal_code', $billAddress->getProperty('contact_info_address.addr_code'));

                $billAddress->setProperty('qb_linked', 1);
                if (!empty($existingQBBillingAddress) && ($billAddress->getId() != $existingQBBillingAddress->getId())) {
                    $existingQBBillingAddress->setProperty('qb_linked', 0);
                    if (!$existingQBBillingAddress->save()) {
                        return false;
                    }
                }
                if (!$billAddress->save()) {
                    return false;
                }
            }
            return true;
        }
        return false;
    }

    protected function updateQBShippingAddressFromExportForm(GI_Form $form, AbstractContactQB $contactQB) {
        if ($form->wasSubmitted() && $form->validate()) {
            $shipAddressId = filter_input(INPUT_POST, 'ship_address_id');
            if (empty($shipAddressId)) {
                return true;
            }
            $existingQBShippingAddress = $this->getQBShippingAddress();
            if ($shipAddressId == 'dne') {
                if (!empty($existingQBShippingAddress)) {
                    $existingQBShippingAddress->setProperty('qb_linked', 0);
                    if (!$existingQBShippingAddress->save()) {
                        return false;
                    }
                }
            } else if ($shipAddressId == 'sab') {
                $billAddressId = filter_input(INPUT_POST, 'bill_address_id');
                if (!empty($billAddressId) && $billAddressId !== 'dne') {
                    $billAddressModel = ContactInfoFactory::getModelById($billAddressId);
                    if (!empty($billAddressModel)) {
                        $shipAddress = ContactInfoFactory::buildNewModel('shipping_address');
                        $shipAddress->setProperty('contact_id', $this->getProperty('id'));
                        $shipAddress->setProperty('qb_linked', 1);
                        $shipAddress->setPropertiesFromOtherAddress($billAddressModel);
                        if (!$shipAddress->save()) {
                            return false;
                        }
                        $contactQB->setProperty('contact_qb_customer.ship_addr_line_1', $shipAddress->getProperty('contact_info_address.addr_street'));
                        $contactQB->setProperty('contact_qb_customer.ship_addr_line_2', $shipAddress->getProperty('contact_info_address.addr_street_two'));
                        $contactQB->setProperty('contact_qb_customer.ship_addr_city', $shipAddress->getProperty('contact_info_address.addr_city'));
                        $contactQB->setProperty('contact_qb_customer.ship_addr_region', $shipAddress->getProperty('contact_info_address.addr_region'));
                        $countryCode = $shipAddress->getProperty('contact_info_address.addr_country');
                        $contactQB->setProperty('contact_qb_customer.ship_addr_country', GeoDefinitions::getCountryNameFromCode($countryCode));
                        $contactQB->setProperty('contact_qb_customer.ship_addr_postal_code', $shipAddress->getProperty('contact_info_address.addr_code'));
                        if (!empty($existingQBShippingAddress)) {
                            $existingQBShippingAddress->setProperty('qb_linked', 0);
                            if (!$existingQBShippingAddress->save()) {
                                return false;
                            }
                        }
                    }
                }
            } else {
                $shipAddress = ContactInfoFactory::changeModelType(ContactInfoFactory::getModelById($shipAddressId), 'shipping_address');
                if (empty($shipAddress)) {
                    return false;
                }
                $contactQB->setProperty('contact_qb_customer.ship_addr_line_1', $shipAddress->getProperty('contact_info_address.addr_street'));
                $contactQB->setProperty('contact_qb_customer.ship_addr_line_2', $shipAddress->getProperty('contact_info_address.addr_street_two'));
                $contactQB->setProperty('contact_qb_customer.ship_addr_city', $shipAddress->getProperty('contact_info_address.addr_city'));
                $contactQB->setProperty('contact_qb_customer.ship_addr_region', $shipAddress->getProperty('contact_info_address.addr_region'));
                $countryCode = $shipAddress->getProperty('contact_info_address.addr_country');
                $contactQB->setProperty('contact_qb_customer.ship_addr_country', GeoDefinitions::getCountryNameFromCode($countryCode));
                $contactQB->setProperty('contact_qb_customer.ship_addr_postal_code', $shipAddress->getProperty('contact_info_address.addr_code'));
                $shipAddress->setProperty('qb_linked', 1);
                if (!empty($existingQBShippingAddress) && ($shipAddress->getId() != $existingQBShippingAddress->getId())) {
                    $existingQBShippingAddress->setProperty('qb_linked', 0);
                    if (!$existingQBShippingAddress->save()) {
                        return false;
                    }
                }
                if (!$shipAddress->save()) {
                    return false;
                }
            }
            return true;
        }
        return false;
    }

    protected function updateQBEmailAddressFromExportForm(GI_Form $form, AbstractContactQB $contactQB) {
        if ($form->wasSubmitted() && $form->validate()) {
            $emailId = filter_input(INPUT_POST, 'email_id');
            if (empty($emailId)) {
                return true;
            }
            $existingEmailAddress = $this->getQBEmailAddress();
            if ($emailId == 'dne') {
                if (!empty($existingEmailAddress)) {
                    $existingEmailAddress->setProperty('qb_linked', 0);
                    if (!$existingEmailAddress->save()) {
                        return false;
                    }
                }
            } else {
                $emailAddress = ContactInfoFactory::getModelById($emailId);
                if (empty($emailAddress)) {
                    return false;
                }
                $contactQB->setProperty('email', $emailAddress->getProperty('contact_info_email_addr.email_address'));
                $emailAddress->setProperty('qb_linked', 1);
                if (!empty($existingEmailAddress) && ($emailAddress->getId() != $existingEmailAddress->getId())) {
                    $existingEmailAddress->setProperty('qb_linked', 0);
                    if (!$existingEmailAddress->save()) {
                        return false;
                    }
                }
                if (!$emailAddress->save()) {
                    return false;
                }
            }

            return true;
        }
        return false;
    }

    protected function updateQBPhoneNumberFromExportForm(GI_Form $form, AbstractContactQB $contactQB) {
        if ($form->wasSubmitted() && $form->validate()) {
            $phoneId = filter_input(INPUT_POST, 'phone_id');
            if (empty($phoneId)) {
                return true;
            }
            $existingPhoneNumber = $this->getQBPhoneNumber();
            if ($phoneId == 'dne') {
                if (!empty($existingPhoneNumber)) {
                    $existingPhoneNumber->setProperty('qb_linked', 0);
                    if (!$existingPhoneNumber->save()) {
                        return false;
                    }
                }
            } else {
                $phoneNumber = ContactInfoFactory::getModelById($phoneId);
                if (empty($phoneNumber)) {
                    return false;
                }
                $contactQB->setProperty('primary_phone', $phoneNumber->getProperty('contact_info_phone_num.phone'));
                $phoneNumber->setProperty('qb_linked', 1);
                if (!empty($existingPhoneNumber) && ($phoneNumber->getId() != $existingPhoneNumber->getId())) {
                    $existingPhoneNumber->setProperty('qb_linked', 0);
                    if (!$existingPhoneNumber->save()) {
                        return false;
                    }
                }
                $phoneNumber = ContactInfoFactory::changeModelType($phoneNumber, 'phone_num');
                if (!$phoneNumber->save()) {
                    return false;
                }
            }

            return true;
        }
        return false;
    }

    protected function updateQBMobileNumberFromExportForm(GI_Form $form, AbstractContactQB $contactQB) {
        if ($form->wasSubmitted() && $form->validate()) {
            $phoneId = filter_input(INPUT_POST, 'mobile_phone_id');
            if (empty($phoneId)) {
                return true;
            }
            $existingPhoneNumber = $this->getQBMobileNumber();
            if ($phoneId == 'dne') {
                if (!empty($existingPhoneNumber)) {
                    $existingPhoneNumber->setProperty('qb_linked', 0);
                    if (!$existingPhoneNumber->save()) {
                        return false;
                    }
                }
            } else {
                $phoneNumber = ContactInfoFactory::getModelById($phoneId);
                if (empty($phoneNumber)) {
                    return false;
                }
                $contactQB->setProperty('mobile', $phoneNumber->getProperty('contact_info_phone_num.phone'));
                $phoneNumber->setProperty('qb_linked', 1);
                if (!empty($existingPhoneNumber) && ($phoneNumber->getId() != $existingPhoneNumber->getId())) {
                    $existingPhoneNumber->setProperty('qb_linked', 0);
                    if (!$existingPhoneNumber->save()) {
                        return false;
                    }
                }
                $phoneNumber = ContactInfoFactory::changeModelType($phoneNumber, 'mobile_phone_num');
                if (!$phoneNumber->save()) {
                    return false;
                }
            }
            return true;
        }
        return false;
    }

    protected function updateQBOtherNumberFromExportForm(GI_Form $form, AbstractContactQB $contactQB) {
        if ($form->wasSubmitted() && $form->validate()) {
            $phoneId = filter_input(INPUT_POST, 'other_phone_id');
            if (empty($phoneId)) {
                return true;
            }
            $existingPhoneNumber = $this->getQBOtherNumber();
            if ($phoneId == 'dne') {
                if (!empty($existingPhoneNumber)) {
                    $existingPhoneNumber->setProperty('qb_linked', 0);
                    if (!$existingPhoneNumber->save()) {
                        return false;
                    }
                }
            } else {
                $phoneNumber = ContactInfoFactory::getModelById($phoneId);
                if (empty($phoneNumber)) {
                    return false;
                }
                $contactQB->setProperty('alternate_phone', $phoneNumber->getProperty('contact_info_phone_num.phone'));
                $phoneNumber->setProperty('qb_linked', 1);
                if (!empty($existingPhoneNumber) && ($phoneNumber->getId() != $existingPhoneNumber->getId())) {
                    $existingPhoneNumber->setProperty('qb_linked', 0);
                    if (!$existingPhoneNumber->save()) {
                        return false;
                    }
                }
                $phoneNumber = ContactInfoFactory::changeModelType($phoneNumber, 'other_phone_num');
                if (!$phoneNumber->save()) {
                    return false;
                }
            }

            return true;
        }
        return false;
    }

    protected function updateQBFaxNumberFromExportForm(GI_Form $form, AbstractContactQB $contactQB) {
        if ($form->wasSubmitted() && $form->validate()) {
            $phoneId = filter_input(INPUT_POST, 'fax_phone_id');
            if (empty($phoneId)) {
                return true;
            }
            $existingPhoneNumber = $this->getQBFaxNumber();
            if ($phoneId == 'dne') {
                if (!empty($existingPhoneNumber)) {
                    $existingPhoneNumber->setProperty('qb_linked', 0);
                    if (!$existingPhoneNumber->save()) {
                        return false;
                    }
                }
            } else {
                $phoneNumber = ContactInfoFactory::getModelById($phoneId);
                if (empty($phoneNumber)) {
                    return false;
                }
                $contactQB->setProperty('fax', $phoneNumber->getProperty('contact_info_phone_num.phone'));
                $phoneNumber->setProperty('qb_linked', 1);
                if (!empty($existingPhoneNumber) && ($phoneNumber->getId() != $existingPhoneNumber->getId())) {
                    $existingPhoneNumber->setProperty('qb_linked', 0);
                    if (!$existingPhoneNumber->save()) {
                        return false;
                    }
                }
                $phoneNumber = ContactInfoFactory::changeModelType($phoneNumber, 'fax_num');
                if (!$phoneNumber->save()) {
                    return false;
                }
            }

            return true;
        }
        return false;
    }

    public function getQuickbooksExportedStatusHTML() {
        $icon = 'red remove';
        $formattedDate = '';
        if (!empty($this->getQuickbooksId()) || !empty($this->getQuickbooksLastUpdatedDate())) {
            if ($this->getRequiresQuickbooksReExport()) {
                $icon = 'red check';
            } else {
                $icon = 'green check';
            }
            $date = $this->getQuickbooksLastUpdatedDate();
            $formattedDate = '<span class="qb_export_date">' . GI_Time::formatDateForDisplay($date) . '</span>';
        }
        return GI_StringUtils::getIcon($icon) . $formattedDate;
    }
    
    public function getQuickbooksLastUpdatedDate() {
        $contactQB = $this->getContactQB();
        if (!empty($contactQB)) {
            $importDate = $contactQB->getProperty('qb_import_date');
            $exportDate = $contactQB->getProperty('qb_export_date');
            $importDateTime = new DateTime($importDate);
            $exportDateTime = new DateTime($exportDate);
            if (empty($importDate) && !empty($exportDate)) {
                return $exportDateTime->format('Y-m-d');
            } else if (!empty($importDate) && empty($exportDate)) {
                return $importDateTime->format('Y-m-d');
            } else if (!empty($importDate) && !empty($exportDate)) {
               if ($importDateTime >= $exportDateTime) {
                   return $importDateTime->format('Y-m-d');
               } else {
                   return $exportDateTime->format('Y-m-d');
               }
            }
        }
        return NULL;
    }
    
    public function getIconClass(){
        return $this->getTypeRef();
    }
    
    public function getIcon(){
        $iconClass = $this->getIconClass();
        return GI_StringUtils::getIcon($iconClass, true, 'gray');
    }
    
    /**
     * @param string $term
     * @return array
     */
    public function getAutocompResult($term = NULL, $useAddrBtn = false, $addressInfo = array()){
        $realName = $this->getRealName();
        $name = $this->getName();
        $fullyQualifiedName = $this->getFullyQualifiedName();
        $autoResultName = GI_StringUtils::markTerm($term, $name);
        $typeTitle = $this->getTypeTitle();
        $contactCat = $this->getContactCat();
        if($contactCat){
            $typeTitle = $contactCat->getTypeTitle();
        }

        $autoResult = '<span class="result_text">';
        $autoResult .= GI_StringUtils::getIcon($this->getIconClass());
        $autoResult .= '<span class="inline_block">';
        $autoResult .= $autoResultName;
        if ($realName != $name) {
            $autoResult .= '<span class="sub">';
            $autoResult .= '(' . GI_StringUtils::markTerm($term, $realName) . ')';
            $autoResult .= '</span>';
        }
        if (ProjectConfig::getContactUseFullyQualifiedName() && !empty($fullyQualifiedName) && $fullyQualifiedName != $name) {
            $autoResult .= '<span class="sub">';
            $autoResult .= '(' . GI_StringUtils::markTerm($term, $fullyQualifiedName) . ')';
            $autoResult .= '</span>';
        }
        $autoResult .= '<span class="sub">';
        $autoResult .= $typeTitle;
        $autoResult .= '</span>';
        $autoResult .= '</span>';

        $id = $this->getId();
        $result = array(
            'label' => $name,
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
                if($addrSelector){
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
        $result = $this->addTaxValuesToAutocompResult($result);
        return $this->addContactCatDataToAutocompResult($result);
    }

    protected function addTaxValuesToAutocompResult($result) {
        if (dbConnection::isModuleInstalled('accounting')) {
            if (QBTaxCodeFactory::getTaxingUsesQBAst()) {
                $result = $this->addQBAstTaxCodeValuesToAutocompResult($result);
            } else {
                $result = $this->addDefaultQBAstTaxCodeValuesToAutocompResult($result);
            }
        } else {
            $result = $this->addDefaultQBAstTaxCodeValuesToAutocompResult($result);
        }
        return $result;
    }

    protected function addQBAstTaxCodeValuesToAutocompResult($autocompResult) {
        $autocompResult['qb_ast_tax'] = 1;
        $autocompResult['qb_ast_default_tax_qb_id'] = $this->getQBAstTaxFieldDefaultVal();
        return $autocompResult;
    }

    protected function addDefaultQBAstTaxCodeValuesToAutocompResult($autocompResult) {
        $autocompResult['qb_ast_tax'] = 0;
        $autocompResult['qb_ast_default_tax_qb_id'] = NULL;
        return $autocompResult;
    }

    protected function addContactCatDataToAutocompResult($autocompResult) {
        $contactCat = $this->getContactCat();
        if (!empty($contactCat)) {
            $autocompResult = $contactCat->addDataToContactAutoCompResult($autocompResult);
        }
        return $autocompResult;
    }
    
    protected function getUseAddrBtn(ContactInfoAddress $address, $useViewAsBtn = false){
        $addrStreet = $address->getProperty('contact_info_address.addr_street');
        $addrStreetTwo = $address->getProperty('contact_info_address.addr_street_two');
        $addrCity = $address->getProperty('contact_info_address.addr_city');
        $addrRegion = $address->getProperty('contact_info_address.addr_region');
        $addrCode = $address->getProperty('contact_info_address.addr_code');
        $addrCountry = $address->getProperty('contact_info_address.addr_country');
        $prefix = $address->getFieldPrefix();
        $suffix = $address->getFieldSuffix();
        $tag = 'span';
        $class = '';
        if($useViewAsBtn){
            $tag = 'div';
            $class = 'close_gi_modal';
        }
        $addrBtn = '<' . $tag . ' class="use_this_address ' . $class . '" data-addr-street="' . $addrStreet . '" data-addr-street-two="' . $addrStreetTwo . '" data-addr-city="' . $addrCity . '" data-addr-region="' . $addrRegion . '" data-addr-code="' . $addrCode . '" data-addr-country="' . $addrCountry . '" data-field-prefix="' . $prefix . '" data-field-suffix="' . $suffix . '">';
        if($useViewAsBtn){
            $addressView = $address->getDetailView();
            $addrBtn .= $addressView->getHTMLView();
        } else {
            $addrBtn .= 'Use Address';
        }
        
        $addrBtn .= '</' . $tag . '>';
        return $addrBtn;
    }

    public function hasBills() {
        if (empty($this->hasBills)) {
            if (!dbConnection::isModuleInstalled('billing')) {
                $this->hasBills = false;
            } else {
                $search = BillFactory::search();
                $search->filter('contact_id', $this->getProperty('id'))
                        ->setPageNumber(1)
                        ->setItemsPerPage(1);
                $models = $search->select();
                if (!empty($models)) {
                    $this->hasBills = true;
                } else {
                    $this->hasBills = false;
                }
            }
        }
        return $this->hasBills;
    }

    public function hasInvoices() {
        if (empty($this->hasInvoices)) {
            if (!dbConnection::isModuleInstalled('invoice')) {
                $this->hasInvoices = false;
            } else {
                $search = InvoiceFactory::search();
                $search->filter('contact_id', $this->getProperty('id'))
                        ->setPageNumber(1)
                        ->setItemsPerPage(1);
                $models = $search->select();
                if (!empty($models)) {
                    $this->hasInvoices = true;
                } else {
                    $this->hasInvoices = false;
                }
            }
        }
        return $this->hasInvoices;
    }
    
    /**
     * @param GI_Form $form
     * @param GI_DataSearch $dataSearch
     * @return \ScheduleAvailabilitySearchFormView
     */
    protected static function getAvailabilitySearchFormView(GI_Form $form, GI_DataSearch $dataSearch = NULL){
        $searchValues = array();
        if($dataSearch){
            $searchValues = $dataSearch->getSearchValues();
        }
        $searchView = new ScheduleAvailabilitySearchFormView($form, $searchValues);
        return $searchView;
    }
    
    /**
     * @param GI_DataSearch $dataSearch
     * @param GI_Form $form
     * @return boolean
     */
    protected static function filterAvailabilitySearchForm(GI_DataSearch $dataSearch, GI_Form $form = NULL){
        $contactIds = $dataSearch->getSearchValue('contact_ids');
        if(!empty($contactIds)){
            static::addAvailabilityContactFilterToDataSearch($contactIds, $dataSearch);
        }
        
        if(!is_null($form) && $form->wasSubmitted() && $form->validate()){
            $contactIds = filter_input(INPUT_POST, 'search_contact_ids');
            $dataSearch->setSearchValue('contact_ids', $contactIds);
            
            $goToDate = filter_input(INPUT_POST, 'search_go_to_date');
            $dataSearch->setSearchValue('go_to_date', $goToDate);
        }
        
        return true;
    }
    
    public static function addAvailabilityContactFilterToDataSearch($contactIds, GI_DataSearch $dataSearch){
        $dataSearch->filterIn('contact_id', explode(',', $contactIds));
    }
    
    /**
     * 
     * @param GI_DataSearch $dataSearch
     * @param string $type
     * @param array $redirectArray
     * @return \ScheduleAvailabilitySearchFormView
     */
    public static function getAvailabilitySearchForm(GI_DataSearch $dataSearch, $type = NULL, $redirectArray = array()){
        $form = new GI_Form('contact_availability_search');
        $searchView = static::getAvailabilitySearchFormView($form, $dataSearch);
        
        static::filterAvailabilitySearchForm($dataSearch, $form);
        
        if($form->wasSubmitted() && $form->validate()){
            $queryId = $dataSearch->getQueryId();
            
            if(empty($redirectArray)){
                $redirectArray = array(
                    'controller' => 'schedule',
                    'action' => 'indexAvailability'
                );
                
                if(!empty($type)){
                    $redirectArray['type'] = $type;
                }
            }
            
            $redirectArray['queryId'] = $queryId;
            
            GI_URLUtils::redirect($redirectArray);
        }
        return $searchView;
    }
    
    /**
     * @param GI_Form $form
     * @return array
     */
    public function getContactTagIdArray(GI_Form $form, $idsAsKey = false){
        $contactTagIdArray = array();
        if($form->wasSubmitted()){
            //Get ids from the form
            $contactTagIds = filter_input(INPUT_POST, 'contact_tag_ids');
            if (!empty($contactTagIds)) {
                $contactTagIdArray = explode(',', $contactTagIds);
            }
        } else {
            //Get ids from DB
            $tagModels = $this->getTags();
            if (!empty($tagModels)) {
                foreach ($tagModels as $tagModel) {
                    if ($idsAsKey) {
                        $contactTagIdArray[$tagModel->getId()] = $tagModel->getId();
                    } else {
                        $contactTagIdArray[] = $tagModel->getId();
                    }
                }
            }
        }
        return $contactTagIdArray;
    }
    
    public function isTaxExempt() {
        $contactQB = $this->getContactQB();
        if (!empty($contactQB)) {
            return $contactQB->getIsTaxExempt();
        }
        return false;
    }

    public function getQBAstTaxFieldDefaultVal() {
        if ($this->isTaxExempt()) {
            return 0;
        }
        return 1;
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
    
    public function getNameWithPhoneNumber() {
        $html = '<span class="title">'.$this->getName().'</span>';
        $phoneNumber = $this->getPhoneNumber();
        if (!empty($phoneNumber)) {
            $html .= '<span class="subtitle">'.$phoneNumber.'</span>';
        }
        return $html;
    }
    
    public function getIndexURLAttrs($withPageNumber = false){
        $indexURLAttributes = array(
            'controller' => 'contact',
            'action' => 'index',
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
//    
////    public function getListBarURL($otherAttributes = NULL) {
////        if (!$this->isIndexViewable()) {
////            return NULL;
////        }
////        
////        $listURLAttributes = $this->getIndexURLAttrs();
////        $listURLAttributes['targetId'] = 'list_bar';
////        $listURLAttributes['curId'] = $this->getId();
////        if (isset($otherAttributes['type'])) {
////            //overrite type
////            $listURLAttributes['type'] = $otherAttributes['type'];
////        }
////        if (isset($otherAttributes['fullView'])) {
////            $listURLAttributes['fullView'] = $otherAttributes['fullView'];
////        } else {
////            $listURLAttributes['fullView'] = 1;
////        }
////        
////        return GI_URLUtils::buildURL($listURLAttributes);
////    }
    

    public function getRequiresQuickbooksReExport() {
        $contactQB = $this->getContactQB();
        if (!empty($contactQB) && !empty($contactQB->getProperty('export_required'))) {
            return true;
        }
        return false;
    }
    
    public function save() {
        $contactQB = $contactQB = $this->getContactQB();
        if (empty($contactQB)) {
            $this->setProperty('fully_qualified_name', NULL);
        } else {
            $this->setProperty('fully_qualified_name', $contactQB->getProperty('fully_qualified_name'));
        }
        return parent::save();
    }
    
    /**
     * In order to access handleContactInfoFormSubmission and not affect already overrode function, create public call function
     * @param type $form
     */
    public function callHandleContactInfoFormSubmission($form) {
        return $this->handleContactInfoFormSubmission($form);
    }

    /**
     * In order to access handleContactInfoFormSubmission and not affect already overrode function, create public call function
     * @param GI_Form $form
     * @return boolean
     */
    public function callHandleTagFormSubmission(GI_Form $form) {
        return $this->handleTagFormSubmission($form);
    }

    public function handleContactSubCatTagFieldSubmission($form) {
        $subcatTagId = filter_input(INPUT_POST, 'sub_cat_tag_id');
        $existingSubCatTag = $this->getSubCategoryTag();
        if (!empty($subcatTagId)) {
            if ($existingSubCatTag->getId() !== $subcatTagId) {
                $newSubCatTag = TagFactory::getModelById($subcatTagId);
                if (empty($newSubCatTag)) {
                    return false;
                }
                if (!(ContactFactory::unlinkContactAndTag($this, $existingSubCatTag) && ContactFactory::linkContactAndTag($this, $newSubCatTag))) {
                    return false;
                }
            }
        } else {
            if (!empty($existingSubCatTag)) {
                if (!ContactFactory::unlinkContactAndTag($this, $existingSubCatTag)) {
                    return false;
                }
            }
        }
        return true;
    }

    public function getPublicProfileAccentColour() {
        $colour = $this->getColour();
        return $colour;
    }
    
    /**
     * @return Folder
     */
    public function getPublicLogoFolder() {
        $publicFolder = $this->getSubFolderByRef('public', array('title' => 'Public'));
        return $publicFolder;
    }
    
    /** @return AbstractFile */
    public function getPublicLogoFile(){
        $publicLogoFolder = $this->getPublicLogoFolder();
        if (empty($publicLogoFolder)) {
            return NULL;
        }
        $files = $publicLogoFolder->getFiles();
        if (empty($files)) {
            return;
        }
        $logoFile = $files[0];
        return $logoFile;
    }
    
    public function getPublicLogoHTML($width = NULL, $height = NULL){
        $logoFile = $this->getPublicLogoFile();
        if(!$logoFile){
            return $this->getPublicLogoPlaceHolderHTML($width, $height);
        }
        $fileView = $logoFile->getSizedView();
        if(!empty($width) && !empty($height)){
            $fileView->setDimensions($width, $height);
        }
        return $fileView->getHTMLView();
    }
    
    public function getPublicLogoPlaceHolderHTML($width = NULL, $height = NULL){
        $avatar = '<span class="avatar_wrap avatar_placeholder inline_block">' . GI_StringUtils::getSVGIcon('avatar') . '</span>';
        return $avatar;
    }
    
    /** @return \ContactPublicDetailView */
    public function getPublicDetailView() {
        $detailView = new QnAContactDetailView($this);
        return $detailView;
    }
    
    public function setContactCat(AbstractContactCat $contactCat) {
        $contactCat->setProperty('contact_id', $this->getId());
        $this->contactCat = $contactCat;
    }
    
    /** Profile */
    
        public function getProfileListBarURL($otherAttributes = NULL) {
        if (!$this->isIndexViewable()) {
            return NULL;
        }
        $contactCat = $this->getContactCat();
        if (!empty($contactCat)) {
            $type = $contactCat->getTypeRef();
        } else {
            $type = 'client';
        }
        $listURLAttributes = array(
            'controller'=>'contactprofile',
            'action'=>'index',
            'type'=>$type,
        );
        $listURLAttributes['targetId'] = 'list_bar';
        $listURLAttributes['curId'] = $this->getId();
        if (isset($otherAttributes['type'])) {
            //overrite type
            $listURLAttributes['type'] = $otherAttributes['type'];
        }
        if (isset($otherAttributes['fullView'])) {
            $listURLAttributes['fullView'] = $otherAttributes['fullView'];
        } else {
            $listURLAttributes['fullView'] = 1;
        }
        
        return GI_URLUtils::buildURL($listURLAttributes);
    }
    
    public function getProfileIndexURLAttrs() {
        $attrs = array(
            'controller'=>'contactprofile',
            'action'=>'index',
        );
        $contactCat = $this->getContactCat();
        if (!empty($contactCat)) {
            $attrs['type'] = $contactCat->getTypeRef(); //TODO - might need to only be top level type ref. i.e 'client', not 'qna_client'
        }
        return $attrs;
    }

    public function getEditProfileURLAttrs() {
        return array(
            'controller' => 'contactprofile',
            'action' => 'edit',
            'id' => $this->getId(),
        );
    }

    public function getEditProfileURL() {
        return GI_URLUtils::buildURL($this->getEditProfileURLAttrs());
    }
    
    public function getViewProfileURLAttrs() {
        return array(
            'controller'=>'contactprofile',
            'action'=>'view',
            'id'=>$this->getId(),
        );
    }
    
    public function getViewProfileURL() {
        return GI_URLUtils::buildURL($this->getViewProfileURLAttrs());
    }

    public function getProfileStepNavURLAttrs($step = 1, $ajax = true) {
        $attrs = $this->getEditProfileURLAttrs();
        $attrs['step'] = $step;
        if ($ajax) {
            $attrs['ajax'] = 1;
        }

        return $attrs;
    }
    
    public function getProfileViewURLAttrs() {
        return array(
            'controller'=>'contactprofile',
            'action'=>'view',
            'id'=>$this->getId(),
        );
    }
    
    public function getAddPersonURLAttrs() {
        return NULL;
    }
    
    public function getAddPersonURL() {
        $attrs = $this->getAddPersonURLAttrs();
        if (!empty($attrs)) {
            return GI_URLUtils::buildURL($attrs);
        }
        return NULL;
    }
    
    public function getProfileDetailView() {
        return NULL;
    }

    public function getProfileFormView(\GI_Form $form, $buildForm = true, $curStep = 1, $curTab = 0) {
        $formView = $this->getProfileFormViewObject($form, $curStep);
        if (empty($formView)) {
            return NULL;
        }
        if ($buildForm) {
            $formView->buildForm();
        }
        return $formView;
    }

    protected function getProfileFormViewObject(GI_Form $form, $curStep = 1) {
        return NULL;
    }
    
        /**
     * @param GI_Form $form
     * @param type $step
     * @return boolean
     */
    public function handleProfileFormSubmission(GI_Form $form, $step = 1) {
        if ($form->wasSubmitted() && $this->validateForm($form, $step)) {
            switch ($step) {
                case 1:

                    break;
                case 2:

                    break;
                case 3:

                    break;

                default:
                    return false;
            }
            return true;
        }
        return false;
    }

    public function validateProfileForm(GI_Form $form, $step = 1) {
        switch ($step) {
            case 1:

                break;
            case 2:

                break;
            case 3:

                break;
            default:
                //Do Nothing
                break;
        }
        return true;
    }
    
    public function getProfileUICardView() {
        return new ContactProfileUICardView($this);
    }
    
    public function getPublicProfileUICardView() {
        return $this->getProfileUICardView();
    }
    
    /** @return AbstractWindowView */
    public function getPublicProfileDetailView() {
        return NULL;
    }

    public function getPublicProfileFormView(GI_Form $form) {
        return NULL;
    }
    
    protected function setDefaultAdvancedSettings() {
        if (empty($this->getProperty('default_currency_id'))) {
            $this->setProperty('default_currency_id', ProjectConfig::getDefaultCurrencyId());
        }
        return true;
    }
    
    public function getProfileMySettingsDetailView() {
        return NULL;
    }
    
    public function getIsProfileComplete() {
        return true;
    }
    
    /**
     * @param GI_DataSearch $dataSearch
     * @param GI_Form $form
     * @return boolean
     */
    public static function filterProfileSearchForm(GI_DataSearch $dataSearch, GI_Form $form = NULL) {
        $name = $dataSearch->getSearchValue('name');
        if (!empty($name)) {
            static::addNameFilterToProfileDataSearch($name, $dataSearch);
        }

        $address = $dataSearch->getSearchValue('address');
        if (!empty($address)) {
            static::addAddressFilterToDataSearch($address, $dataSearch);
        }

        $email = $dataSearch->getSearchValue('email');
        if (!empty($email)) {
            static::addEmailFilterToDataSearch($email, $dataSearch);
        }

        $phone = $dataSearch->getSearchValue('phone');
        if (!empty($phone)) {
            static::addPhoneFilterToDataSearch($phone, $dataSearch);
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

            $email = filter_input(INPUT_POST, 'search_email');
            $dataSearch->setSearchValue('email', $email);

            $phone = filter_input(INPUT_POST, 'search_phone');
            $dataSearch->setSearchValue('phone', $phone);

            $tags = filter_input(INPUT_POST, 'search_tags', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY);
            $dataSearch->setSearchValue('tags', $tags);
        }

        return true;
    }

    public static function addNameFilterToProfileDataSearch($name, GI_DataSearch $dataSearch, $contactTableAlias = NULL){
        if (get_called_class() != 'Contact') {
            return;
        }
    }

    public static function addAddressFilterToProfileDataSearch($address, GI_DataSearch $dataSearch){
        $contactTable = $dataSearch->prefixTableName('contact');
        $columns = array(
            'ADDR.addr_street',
            'ADDR.addr_city',
            'ADDR.addr_code'
        );
        $dataSearch->leftJoin('contact_info', 'contact_id', $contactTable, 'id', 'INFOADDR')
                ->leftJoin('contact_info_address', 'parent_id', 'INFOADDR', 'id', 'ADDR')
                ->filterTermsLike($columns, $address)
                ->orderByLikeScore($columns, $address);
    }
    
    public static function addEmailFilterToProfileDataSearch($email, GI_DataSearch $dataSearch){
        $contactTable = $dataSearch->prefixTableName('contact');
        $columns = array(
            'EMAIL.email_address'
        );
        $dataSearch->leftJoin('contact_info', 'contact_id', $contactTable, 'id', 'INFOEMAIL')
                ->leftJoin('contact_info_email_addr', 'parent_id', 'INFOEMAIL', 'id', 'EMAIL')
                ->filterTermsLike($columns, $email)
                ->orderByLikeScore($columns, $email);
    }
    
    public static function addPhoneFilterToProfileDataSearch($phone, GI_DataSearch $dataSearch){
        $contactTable = $dataSearch->prefixTableName('contact');
        $columns = array(
            'PHONE.phone'
        );
        $dataSearch->leftJoin('contact_info', 'contact_id', $contactTable, 'id', 'INFOPHONE')
                ->leftJoin('contact_info_phone_num', 'parent_id', 'INFOPHONE', 'id', 'PHONE')
                ->filterTermsLike($columns, $phone)
                ->orderByLikeScore($columns, $phone);
    }
    
    public static function addTagsFilterToProfileDataSearch($tags, GI_DataSearch $dataSearch){
        $contactTableName = ContactFactory::getDbPrefix() . 'contact';
        $tagLinkJoin = $dataSearch->createInnerJoin('item_link_to_tag', 'item_id', $contactTableName, 'id', 'cltt');
        $tagLinkJoin->filter('cltt.table_name', 'contact');
        $dataSearch->join('tag', 'id', 'cltt', 'tag_id', 'tag')
                ->filterIn('tag.id', $tags)
                ->groupBy('id');
    }
    
    public function addCustomFiltersToProfileDataSearch(GI_DataSearch $dataSearch) {
        return $this->addCustomFiltersToDataSearch($dataSearch);
    }

    public function addSortingToProfileDataSearch(GI_DataSearch $dataSearch) {
        return $dataSearch;
    }
    
        /**
     * @param string $term
     * @return array
     */
    public function getProfileAutocompResult($term = NULL, $useAddrBtn = false, $addressInfo = array()){
        return $this->getAutocompResult($term, $useAddrBtn, $addressInfo);
    }
    
        /**
     * @param GI_DataSearch $dataSearch
     * @param GI_Form $form
     * @return boolean
     */
    public static function filterPublicSearchForm(GI_DataSearch $dataSearch, GI_Form $form = NULL) {
        return false;
    }
    
    /**
     * 
     * @return AbstractPaymentProcessor
     */
    public function getPaymentProcessor() {
        if (empty($this->paymentProcessor)) {
            $paymentProcessor = new StripePaymentProcessor();
            if (!empty($paymentProcessor)) {
                $paymentProcessor->setContact($this);
            }
            $this->paymentProcessor = $paymentProcessor;
        }
        return $this->paymentProcessor;
    }
    
    public function getPaymentsDetailView() {
        $view = new ContactPaymentsDetailView($this);
        $paymentMethods = $this->getPaymentMethods();
        $payments = $this->getCharges();
        $view->setPaymentMethods($paymentMethods);
        $view->setPayments($payments);
        return $view;
    }
    
    public function getPaymentMethods() {
       $paymentProcessor = $this->getPaymentProcessor();
        if (!empty($paymentProcessor)) {
            return $paymentProcessor->getPaymentMethods();
        }
        return array();
    }

    public function getDefaultPaymentMethod() {
        $paymentProcessor = $this->getPaymentProcessor();
        if (!empty($paymentProcessor)) {
            return $paymentProcessor->getDefaultPaymentMethod();
        }
        return NULL;
    }

    public function canChangeDefaultPaymentMethod() {
        if (Permission::verifyByRef('change_default_payment_method')) {
            return true;
        }
        $user = Login::getUser();
        if (!empty($user) && $this->getIsUserLinkedToThis($user)) {
            return true;
        }
        return false;
    }

    public function canRemovePaymentMethod() {
        if (Permission::verifyByRef('remove_payment_method')) {
            return true;
        }
        $user = Login::getUser();
        if (!empty($user) && $this->getIsUserLinkedToThis($user)) {
            return true;
        }
        return false;
    }

    public function canAddPaymentMethod() {
        if (Permission::verifyByRef('add_payment_method')) {
            return true;
        }
        $user = Login::getUser();
        if (!empty($user) && $this->getIsUserLinkedToThis($user)) {
            return true;
        }
        return false;
    }

    public function canSelectSubscription() {
        if (Permission::verifyByRef('select_subscription')) {
            return true;
        }
        $user = Login::getUser();
        if (!empty($user) && $this->getIsUserLinkedToThis($user)) {
            return true;
        }
        return false;
    }

    public function getCharges() {
        $paymentProcessor = $this->getPaymentProcessor();
        if (!empty($paymentProcessor)) {
            return $paymentProcessor->getCharges();
        }
        return array();
    }

    public function getChargeHistoryView() {
        $paymentProcessor = $this->getPaymentProcessor();
        if (!empty($paymentProcessor)) {
            return $paymentProcessor->getChargeHistoryView();
        }
        return NULL;
    }

    public function isSuspendable() {
        return false;
    }
    
    public function isSuspended() {
        if (is_null($this->isSuspended)) {
            $suspensionCount = SuspensionFactory::getSuspensionCountByContact($this, '', GI_Time::getDateTime(), true);
            if (!empty($suspensionCount)) {
                $this->isSuspended = true;
            } else {
                $this->isSuspended = false;
            }
        }
        return $this->isSuspended;
    }
    
    public function getSuspendedStatus() {
        if ($this->isSuspended()) {
            return 'Suspended';
        }
        return 'Active';
    }
    
    /**
     * 
     * @return AbstractSuspensionTableView
     */
    public function getSuspensionTableView() {
       $suspensions = SuspensionFactory::getSuspensionsByContact($this, '', NULL, false);
       $view = new SuspensionTableView($suspensions);
       return $view;
    }
    
    public function getSuspensionSummaryView() {
        $view = new SuspensionSummaryView($this);
        return $view;
    }
    
    public function getPaymentSettings($typeRef = 'payment') {
        if (!isset($this->paymentSettings[$typeRef])) {
            $search = SettingsFactory::search();
            $search->filterByTypeRef($typeRef, false);
            $search->filter('payment.contact_id', $this->getId());
            $results = $search->select();
            if (!empty($results)) {
                $this->paymentSettings[$typeRef] = $results[0];
            }
        }
        if (isset($this->paymentSettings[$typeRef])) {
            return $this->paymentSettings[$typeRef];
        }
        return NULL;
    }
    
    
    public function getSubscriptions() {
        if (is_null($this->subscriptions)) {
            $this->subscriptions = SubscriptionFactory::getModelsByContact($this);
        }
        return $this->subscriptions;
    }

    /**
     * 
     * @return AbstractContactApplication
     */
    public function getApplication() {
        return NULL;
    }
    
    public function getChangeSubscriptionURL() {
        $attrs = $this->getChangeSubscriptionURLAttrs();
        if (!empty($attrs)) {
            return GI_URLUtils::buildURL($attrs);
        }
        return NULL;
    }
    
    public function getChangeSubscriptionURLAttrs() {
        return array(
            'controller' => 'contactprofile',
            'action' => 'changeSubscription',
            'id' => $this->getId(),
        );
    }

    public function doesContactHaveSubscription(AbstractSubscription $subscription) {
        $search = new GI_DataSearch('contact_has_subscription');
        $search->filter('contact_id', $this->getId())
                ->filter('subscription_id', $subscription->getId());
        $results = $search->select();
        if (!empty($results)) {
            return true;
        }
        return false;
    }
    
    public function unsubscribeFromAllSubscriptions($subIdsToKeep = array()) {
        $search = SubscriptionFactory::search();
        $tableName = $search->prefixTableName('subscription');
        $search->join('contact_has_subscription', 'subscription_id', $tableName, 'id', 'CHS');
        $search->filter('CHS.contact_id', $this->getId());
        if (!empty($subIdsToKeep)) {
            foreach ($subIdsToKeep as $subId) {
                $search->filterNotEqualTo('CHS.subscription_id', $subId);
            }
        }
        
        $search->groupBy('id');
        /* @var $results AbstractSubscription[] */
        $results = $search->select();
        if (!empty($results)) {
            foreach ($results as $result) {
                if (!$result->unsubscribeContact($this)) {
                    return false;
                }
            }
        }
        return true;
    }

}
