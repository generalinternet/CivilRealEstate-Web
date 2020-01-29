<?php
/**
 * Description of AbstractContactFormView
 *
 * @author General Internet
 * @copyright  2017 General Internet
 * @version    4.0.0
 */
abstract class AbstractContactFormView extends MainWindowView {
    
    /** @var GI_Form */
    protected $form;
    /** @var AbstractContact */
    protected $contact;
    protected $formBuilt = false;
    protected $formAction = 'Add';
    protected $addTags = false;
    protected $forceContactInfo = true;
    protected $ajax = false;
    protected $pId = NULL;
    /** @var AbstractGI_Uploader */
    protected $uploader = NULL;
    /** @var AbstractGI_Uploader */
    protected $imageUploader = NULL;
    protected $pInternal = NULL;
    protected $startTitle = '';
    protected $catTypeRefArray = array();
    
    //Options to hide fields
    protected $hideLoginEmail = false;
    protected $hideRole = false;
    protected $hideInternal = false;
    protected $hideDefaultCurrency = false;
    protected $hideContactCategories = false;
    
    public function __construct(GI_Form $form, AbstractContact $contact) {
        parent::__construct();
        $this->form = $form;
        $this->contact = $contact;
        $this->addSiteTitle('Contacts');
        $typeTitle = $this->contact->getViewTitle();
        if(!empty($this->contact->getTypeRef()) && $typeTitle != 'Contact'){
            $this->addSiteTitle($typeTitle);
        }
        if(!is_null($contact->getId())){
            $this->formAction = 'Edit';
            $this->addSiteTitle($this->contact->getName());
        }
        $this->addSiteTitle($this->formAction);
        
        //Set list URL
        $contactCat = $contact->getContactCat();
        if (empty($contactCat)) {
            $contactCat = $contact->getDefaultContactCat();
        }
        $this->setListBarURL($contactCat->getListBarURL());
        //Set window area title
        $title = $this->formAction. ' ' .$this->contact->getViewTitle(false);
        $this->setWindowTitle('<span class="inline_block">' . $title . '</span>');
    }
    
    public function setStartTitle($startTitle){
        $this->startTitle = $startTitle;
        return $this;
    }
    
    public function setAjax($ajax){
        $this->ajax = $ajax;
        return $this;
    }
    
    public function setForceContactInfo($forceContactInfo){
        $this->forceContactInfo = $forceContactInfo;
        return $this;
    }
    
    public function setAddTags($addTags){
        $this->addTags = $addTags;
    }
    
    public function setPid($pId) {
        $this->pId = $pId;
    }
    
    public function setPInternal($pInternal) {
        $this->pInternal = $pInternal;
    }
    
    public function setCatTypeRefArray($catTypeRefArray) {
        $this->catTypeRefArray = $catTypeRefArray;
    }
    
    /**
     * @param boolean $hideLoginEmail
     */
    public function setHideLoginEmail($hideLoginEmail) {
        $this->hideLoginEmail = $hideLoginEmail;
    }
    
    /**
     * @param boolean $hideInternal
     */
    public function setHideRole($hideInternal) {
        $this->hideInternal = $hideInternal;
    }
    
    /**
     * @param boolean $hideDefaultCurrency
     */
    public function setHideDefaultCurrency($hideDefaultCurrency) {
        $this->hideDefaultCurrency = $hideDefaultCurrency;
    }
    
    /**
     * 
     * @param boolean $hideInternal
     */
    public function setHideInternal($hideInternal) {
        $this->hideInternal = $hideInternal;
    }
    
    /**
     * @param boolean $hideContactCategories
     */
    public function setHideContactCategories($hideContactCategories) {
        $this->hideContactCategories = $hideContactCategories;
    }
    
    protected function buildFormFooter(){
        $this->addFormFields();
    }
    
    /**
     * @param GI_Uploader $uploader
     * @return \AbstractContactFormView
     */
    public function setUploader(AbstractGI_Uploader $uploader){
        $this->uploader = $uploader;
        return $this;
    }
    
    /**
     * @param GI_Uploader $uploader
     * @return \AbstractContactFormView
     */
    public function setImageUploader(AbstractGI_Uploader $uploader){
        $this->imageUploader = $uploader;
        return $this;
    }
    
    /**
     * @return GI_Form
     */
    public function getForm(){
        return $this->form;
    }
    
    public function buildForm() {
        if (!$this->formBuilt) {
            $this->buildFormBody();
            $this->buildFormFooter();
            $this->formBuilt = true;
        }
    }
    protected function buildFormBody() {
    }
    
    public function addTagForm() {
        if($this->addTags){
            $this->form->addHTML('<div class="columns halves top_align">');
                $this->form->addHTML('<div class="column">');
                    $tagListFormView = $this->contact->getTagListFormView($this->form);
                    $this->form->addHTML($tagListFormView->getHTMLView());
                $this->form->addHTML('</div>');
            $this->form->addHTML('</div>');
        }
        
        if (dbConnection::isModuleInstalled('qna')) {
            $this->addQnATagForm();
        }
    }

    protected function addSubCategoryTagField($overWriteSettings = array()) {
        $tagFieldName = 'sub_cat_tag_id';
        $acURL = GI_URLUtils::buildURL(array(
                    'controller' => 'tag',
                    'action' => 'autocompTag',
                    'ajax' => 1,
                    'type' => 'contact_sub_cat',
                    'valueColumn' => 'id',
                    'autocompField' => $tagFieldName
                        ), false, true);
        $tagIdsString = '';
        $tag = $this->contact->getSubCategoryTag();
        if (!empty($tag)) {
            $tagIdsString = $tag->getId();
        }
        $fieldSettings = GI_Form::overWriteSettings(array(
                    'displayName' => Lang::getString('contact_sub_category'),
                    'placeHolder' => 'SELECT',
                    'autocompURL' => $acURL,
                    'autocompMinLength' => 0,
                    'autocompMultiple' => false,
                    'autocompLimit' => 1,
                    'value' => $tagIdsString
                        ), $overWriteSettings);
        $this->form->addField($tagFieldName, 'autocomplete', $fieldSettings);
    }

    protected function addViewBodyContent(){
        $this->addHTML($this->form->getForm());
    }
    
    protected function addContactInfoForms(){
        $pTypeRefs = array();
        $contactInfos = $this->contact->getContactInfoArrayFromForm($this->form);
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
            if($this->forceContactInfo){
                $contactInfoWrapClass .= ' force_one_contact_info';
            }
            
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
            
            if($this->contact->multiInfoEnabled($pTypeRef)){
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
    
    protected function addFormFields(){
        if(!$this->ajax){
            $this->addContactInfoForms();

            $this->addUploaders();
            
            $this->addNotesField();
            
            $this->addTagForm();
        }
        $this->addSubmitBtn();
    }
    
    protected function addColourField(){
        $this->form->addField('colour', 'colour', array(
            'displayName' => '&nbsp;',
            'value' => $this->contact->getColour()
        ));
    }

    protected function addUserEmailField() {
        if (!$this->hideLoginEmail) {
            $this->form->addField('login_email', 'email', array(
                'displayName' => 'Login Email',
                'placeHolder' => 'ex. email@domain.com',
                'value' => $this->contact->getLoginEmail()
            ));
        } else {
            $this->form->addField('login_email', 'hidden', array(
                'value' => $this->contact->getLoginEmail()
            ));
        }
    }

    protected function addUserRoleField(){
        $roleOptions = Role::buildRoleOptions();
        $this->form->addField('role_id', 'dropdown', array(
            'options' => $roleOptions,
            'displayName' => Lang::getString('role'),
            'hideNull'=>true,
        ));
    }
    
    protected function addImageUploader(){
        if($this->imageUploader){
            $this->form->addHTML($this->imageUploader->getHTMLView());
        }
    }
    
    protected function addUploader(){
        if($this->uploader){
            $this->form->addHTML($this->uploader->getHTMLView());
        }
    }
    
    protected function addUploaders(){
        if($this->imageUploader || $this->uploader){
            $this->form->addHTML('<div class="columns halves">');
                if($this->imageUploader){
                    $this->form->addHTML('<div class="column">');
                        $this->addImageUploader();
                    $this->form->addHTML('</div>');
                }
                if($this->uploader){
                    $this->form->addHTML('<div class="column">');
                        $this->addUploader();
                    $this->form->addHTML('</div>');
            }
            $this->form->addHTML('</div>');
        }
    }
    
    protected function addUserFieldsSection() {
        $contactNew = true;
        $userNew = true;
        if (!empty($this->contact->getId())) {
            $contactNew = false;
            $user = $this->contact->getUser();
            if (!empty($user->getProperty('id'))) {
                $userNew = false;
            }
        }
        if (($userNew && Permission::verifyByRef('add_users')) || (!$userNew && Permission::verifyByRef('edit_users'))) {
            $this->form->addHTML('<div class="border_box_field_group border_form_wrap">');
            $addUserOptions = array(
                0 => 'No',
                1 => 'Yes'
            );
            if ($userNew) {
                $this->form->addField('add_user_to_system', 'radio', array(
                    'displayName' => 'Allow ' . $this->contact->getTypeTitle() . ' system access?',
                    'options' => $addUserOptions,
                    'value' => 0,
                    'stayOn' => true,
                    'fieldClass' => 'radio_toggler'
                ));
                $this->form->addHTML('<div class="radio_toggler_element form_element" data-group="add_user_to_system" data-element="1">');
                $this->addUserFields();
                $this->form->addHTML('</div>');
            } else {
                $this->form->addHTML('<h3>System User Details</h3>');
                $this->addUserFields();
            }
            $this->form->addHTML('</div>');
        }
    }

    protected function addUserFields() {
        $contactNew = true;
        $userNew = true;
        if (!empty($this->contact->getId())) {
            $contactNew = false;
            $user = $this->contact->getUser();
            if (!empty($user->getProperty('id'))) {
                $userNew = false;
            }
        }
        if (!$this->hideLoginEmail && !$this->hideRole) {
            $this->form->addHTML('<div class="columns halves top_align">');
            $this->form->addHTML('<div class="column">');
            $this->addUserEmailField();
            $this->form->addHTML('</div>');
            $this->form->addHTML('<div class="column">');
            if ($userNew) {
                $this->addUserRoleField();
            }
            $this->form->addHTML('</div>');
            $this->form->addHTML('</div>');
        } else if (!$this->hideLoginEmail && $this->hideRole) {
            $this->addUserEmailField();
        } else {
            if ($userNew) {
                $this->addUserRoleField();
            }
        }
        $this->addUserPasswordSection();
    }

    protected function addUserPasswordSection() {
        $user = $this->contact->getUser();
        $userId = $user->getId();
        if (Permission::verifyByRef('add_users') && empty($userId)) {
            $this->addUserPasswordFields();
        } else if (Permission::verifyByRef('edit_users') && !empty($userId)) {
            $this->form->addField('overwrite_user_password', 'radio', array(
                'displayName' => 'Overwrite existing user password?',
                'options' => array(
                    0 => 'No',
                    1 => 'Yes'
                ),
                'value' => 0,
                'stayOn' => true,
                'fieldClass' => 'radio_toggler'
            ));
            $this->form->addHTML('<div class="radio_toggler_element form_element" data-group="overwrite_user_password" data-element="1">');
            $this->addUserPasswordFields();
            $this->form->addHTML('</div>');
        }
    }

    protected function addUserPasswordFields() {
        $this->form->addHTML('<div class="columns halves">')
                ->addHTML('<div class="column">');
        $this->addUserPasswordField();
        $this->form->addHTML('</div>')
                ->addHTML('<div class="column">');
        $this->addUserConfirmPasswordField();
        $this->form->addHTML('</div>')
                ->addHTML('</div>');
    }

    protected function addUserPasswordField() {
        $this->form->addField('user_pass', 'password', array(
            'displayName' => 'Password'
        ));
    }

    protected function addUserConfirmPasswordField() {
        $this->form->addField('user_pass_confirm', 'password', array(
            'displayName' => 'Confirm Password'
        ));
    }

    protected function addSubmitBtn() {
        $this->form->addHTML('<span class="submit_btn" tabindex="0" title="Save">Submit</span>');
    }

    public function buildContactCatForm() {
        $categoryValue = $this->catTypeRefArray;
        if (!empty($this->contact->getProperty('id'))) {
            $categoryValue = $this->contact->getContactCatTypeRefArray();
        }

        $categoryArray = ContactCatFactory::getTypesArray('category');
        if (isset($categoryArray['category'])) {
            unset($categoryArray['category']);
        }
        $refsToHide = array();
        foreach ($categoryArray as $categoryTypeRef => $title) {
            $tempModel = ContactCatFactory::buildNewModel($categoryTypeRef);
            if (!$tempModel->isEditableByContact($this->contact) || $this->hideContactCategories) {
                unset($categoryArray[$categoryTypeRef]);
                if (isset($categoryValue[$categoryTypeRef])) {
                    $refsToHide[] = $categoryTypeRef;
                }
            }
        }
        if (!empty($categoryArray)) {
            $lockedLabel = '';
            $readOnly = false;
            if (!empty($this->contact->getId())) {
                $contactQB = $this->contact->getContactQB();
                if ($this->contact->isVendor()) {
                    if (!empty($contactQB)) {
                        $readOnly = true;
                        $lockedLabel = 'Locked in order to ensure data integrity with corresponding Quickbooks Supplier';
                    }
                } else if ($this->contact->isClient()) {
                    if (!empty($contactQB)) {
                        $readOnly = true;
                        $lockedLabel = 'Locked in order to ensure data integrity with corresponding Quickbooks Customer';
                    }
                }
            }
            $fieldProperties = array(
                'displayName' => 'Contact Category',
                'placeHolder' => 'Name',
                'options' => $categoryArray,
                'fieldClass' => 'change_category_form',
                'value' => $categoryValue,
                'readOnly' => $readOnly,
                'stayOn' => true,
            );
            if (empty($refsToHide)) {
                $fieldProperties['formElementClass'] = 'fake_required';
                $this->form->addField('categories', 'radio', $fieldProperties);
                if (!empty($lockedLabel)) {
                    $this->form->addHTML('<p>' . $lockedLabel . '</p>');
                }
            }
        } else {
            //Commented out because it causes an error(Array to string conversion Error) when there is no category
//            $this->form->addField('categories', 'hidden', array(
//                'value' => array(),
//            ));
        }
        $refsToHideString = implode(',', $refsToHide);
        $this->form->addField('hidden_categories', 'hidden', array(
            'value'=>$refsToHideString,
        ));

        $this->form->addField('hidden_contact_id', 'hidden', array(
            'value' => $this->contact->getProperty('id'),
        ));

        $catModels = $this->contact->getContactCatModelsFromForm($this->form);
        if (!empty($catModels)) {
            foreach ($catModels as $catModel) {
                $formView = $catModel->getFormView($this->form);
                $formView->buildForm();
                $formView->buildView(false);
            }
        }
    }

    protected function addDefaultCurrencyField() {
        if (empty($this->contact->getProperty('id'))) {
            $value = ProjectConfig::getDefaultCurrencyId();
        } else {
            $value = $this->contact->getProperty('default_currency_id');
        }
        if (ProjectConfig::getHasMultipleCurrencies() && !$this->hideDefaultCurrency) {
            $fieldSettings = array(
                    'displayName' => 'Default Currency',
                    'options' => CurrencyFactory::getOptionsArray('name'),
                    'value' => $value,
                    'required' => true,
                    'hideNull' => true,
                );
            if (!empty($this->contact->getContactQB()) || $this->contact->hasBills() || $this->contact->hasInvoices()) {
                $fieldSettings['readOnly'] = true;
            } 
            $this->form->addField('default_currency_id', 'dropdown', $fieldSettings);
        } else {
            $this->form->addDefaultCurrencyField($value, 'default_currency_id');
        }
    }
    
    protected function addNotesField($overWriteSettings = array()){
        $fieldSettings = GI_Form::overWriteSettings(array(
            'displayName' => 'Notes',
            'placeHolder' => 'enter any notes about this ' . strtolower($this->contact->getTypeTitle()),
            'value' => $this->contact->getProperty('notes')
        ), $overWriteSettings);
        
        $this->form->addField('notes', 'textarea', $fieldSettings);
    }
    
    protected function addQnATagForm() {
        $qnaTagListFormView = TagFactory::getTagListFormView($this->form, $this->contact, 'qna');
        $qnaTagListFormView->setListTitle('QnA Tags');
        $this->form->addHTML($qnaTagListFormView->getHTMLView());
    }
}
