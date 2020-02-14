<?php
/**
 * Description of AbstractUserFormView
 *
 * @author General Internet
 * @copyright  2019 General Internet
 * @version    4.0.1
 */
abstract class AbstractUserFormView extends MainWindowView {
    
    /** @var GI_Form */
    protected $form;
    /** @var AbstractUser */
    protected $user;
    protected $roleOptions;
    protected $languages;
    protected $roleSelectionTitle;
    protected $formBuilt = false;
    protected $showLanguages = false;
    protected $registerForm = false;
    protected $newUser = true;
    /** @var AbstractGI_Uploader */
    protected $avatarUploader = NULL;
    /** @var AbstractGI_Uploader */
    protected $fileUploader = NULL;
    protected $showPasswordFields = true;
    protected $showContactCatField = true;
    protected $defaultContactCatRef = NULL;
    
    public function __construct(GI_Form $form, AbstractUser $user) {
        parent::__construct();
        $this->form = $form;
        $this->roleOptions = Role::buildRoleOptions();
        $this->languages = Lang::getLanguages();
        $this->setUser($user);
        $this->roleSelectionTitle = Lang::getString('role');
        
        $listBarURL = NULL;
        $profileView = false;
        $profile = GI_URLUtils::getAttribute('profile');
        if($profile && $profile == 1){
            $profileView = true;
        }
        if($profileView){
            $notification = NotificationFactory::buildNewModel();
            if($notification){
                $listBarURL = $notification->getListBarURL();
            }
        } else {
            $listBarURL = $user->getListBarURL();
        }
        if(!empty($listBarURL)){
            $this->setListBarURL($listBarURL);
        }
        //Set window area title
        $title = Lang::getString('add_user');
        if(!$this->newUser){
            $title = Lang::getString('edit_user');
        }
        $this->setWindowTitle('<span class="inline_block">' . $title . '</span>');
    }
    
    public function setAvatarUploader(AbstractGI_Uploader $avatarUploader){
        $this->avatarUploader = $avatarUploader;
        return $this;
    }
    
    public function setFileUploader(AbstractGI_Uploader $fileUploader){
        $this->fileUploader = $fileUploader;
        return $this;
    }
    
    public function setUser(AbstractUser $user){
        $this->user = $user;
        if($this->user->getId()){
            $this->newUser = false;
        }
        return $this;
    }
    
    public function setShowLanguages($showLanguages){
        $this->showLanguages = $showLanguages;
        return $this;
    }
    
    public function setRegisterForm($registerForm){
        $this->registerForm = $registerForm;
    }
    
    public function setShowPasswordFields($showPasswordFields){
        $this->showPasswordFields = $showPasswordFields;
        return $this;
    }
    
    public function setShowContactCatField($showContactCatField) {
        $this->showContactCatField = $showContactCatField;
        return $this;
    }
    
    public function setDefaultContactCatRef($defaultContactCatRef){
        $this->defaultContactCatRef = $defaultContactCatRef;
        return $this;
    }
    
    public function buildForm() {
        if (!$this->formBuilt) {
            $this->openFormBody();
                $this->buildFormBody();
            $this->closeFormBody();
            $this->formBuilt = true;
        }
    }   
        
    protected function addSaveBtn(){
        if(!$this->registerForm){
            $btnTitle = Lang::getString('save');
            if($this->newUser){
                $btnTitle = Lang::getString('add_user');
            }
            $this->openHideDuringOTP();
            $this->form->addHTML('<span class="submit_btn" title="' . $btnTitle . '" tabindex="0">' . $btnTitle . '</span>');
            $this->closeHideDuringOTP();
            if(ProjectConfig::registerRequiresCodeConfirmation() && !Login::isLoggedIn()){
                $this->form->openShowDuringOTP();
                $this->form->addHTML('<span class="submit_btn" title="' . $btnTitle . '" tabindex="0">' . Lang::getString('submit') . '</span>');
                $this->form->closeShowDuringOTP();
            }
        }
    }

    protected function addAdditionalFields(){
        
    }
    
    protected function addNameFields(){
        $this->form->addHTML('<div class="columns halves">');
            $this->addFirstNameField(array(
                'formElementClass' => 'column'
            ));
            $this->addLastNameField(array(
                'formElementClass' => 'column'
            ));
        $this->form->addHTML('</div>');
    }
    
    protected function addFirstNameField($overWriteSettings = array()) {
        $fieldSettings = GI_Form::overWriteSettings(array(
            'value' => $this->user->getProperty('first_name'),
            'displayName' => Lang::getString('first_name'),
            'placeHolder' => Lang::getString('first_name'),
            'required' => true
        ), $overWriteSettings);
        $this->form->addField('first_name', 'text', $fieldSettings);
    }
    
    protected function addLastNameField($overWriteSettings = array()) {
        $fieldSettings = GI_Form::overWriteSettings(array(
            'value' => $this->user->getProperty('last_name'),
            'displayName' => Lang::getString('last_name'),
            'placeHolder' => Lang::getString('last_name'),
        ), $overWriteSettings);
        $this->form->addField('last_name', 'text', $fieldSettings);
    }
    
    protected function addEmailField($overWriteSettings = array()){
        if($this->registerForm){
            $this->form->setBotValidation(true);
        }
        $fieldSettings = GI_Form::overWriteSettings(array(
            'required' => true,
            'value' => $this->user->getProperty('email'),
            'displayName' => Lang::getString('login_email'),
            'placeHolder' => Lang::getString('email_address'),
        ), $overWriteSettings);
        $this->form->addField('r_email', 'email', $fieldSettings);
    }
    
    protected function addPhoneField($overWriteSettings = array()){
        $fieldSettings = GI_Form::overWriteSettings(array(
            'value' => $this->user->getProperty('mobile'),
            'displayName' => Lang::getString('phone'),
            'placeHolder' => Lang::getString('phone_number'),
        ), $overWriteSettings);
        $this->form->addField('mobile', 'phone', $fieldSettings);
    }
    
    public function setRoleTitle($roleSelectionTitle){
        $this->roleSelectionTitle = $roleSelectionTitle;
        return $this;
    }
    
    protected function addRoleAndLanguageFields(){
        $this->form->addHTML('<div class="auto_columns halves">');
            $this->addRoleField();
            $this->addLanguageField();
            $this->addPermissionsField();
        $this->form->addHTML('</div>');
    }
    
    protected function addRoleField($overWriteSettings = array()){
        if (count($this->roleOptions) > 1 && (is_null($this->user->getProperty('id')) || !$this->user || $this->user->getProperty('id') != Login::getUserId())) {
            $roles = RoleFactory::getRolesByUser($this->user);
            $roleId = NULL;
            if (!empty($roles)) {
                $currentRole = $roles[0];
                $roleId = $currentRole->getProperty('id');
            }
            $fieldSettings = GI_Form::overWriteSettings(array(
                'options' => $this->roleOptions,
                'value' => $roleId,
                'displayName' => $this->roleSelectionTitle,
                'required' => true
            ), $overWriteSettings);
            $this->form->addField('role_id', 'dropdown', $fieldSettings);
        }
    }
    
    protected function addLanguageField($overWriteSettings = array()){
        if($this->showLanguages){
            $fieldSettings = GI_Form::overWriteSettings(array(
                'options' => $this->languages,
                'value' => $this->user->getProperty('language'),
                'hideNull' => true,
                'displayName' => Lang::getString('language')
            ), $overWriteSettings);
            $this->form->addField('language', 'dropdown', $fieldSettings);
        }
    }
    
    protected function addPermissionsField($overWriteSettings = array(), $overWriteAutocompProps = array()){
        if (Permission::verifyByRef('set_permissions') && (is_null($this->user->getProperty('id')) || !$this->user || $this->user->getProperty('id') != Login::getUserId())) {
            $userPermissions = PermissionFactory::getPermissionsLinkedToUser($this->user);
            $userPermissionIds = array();
            foreach($userPermissions as $userPermission){
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
            $this->form->addField('permission_ids', 'autocomplete', $fieldSettings);
        }
    }
    
    protected function addPasswordFields(){
        if(!$this->showPasswordFields){
            return;
        }
        $this->form->addHTML('<hr/>');
        
        //the below forces firefox to respect [autocomplete="off"]
        $this->form->addHTML('<input type="text" style="display:none" />');
        $this->addPasswordField();
        $this->addRepeatPasswordField();
        
        $showCannotBeSame = true;
        if(!$this->user->getId() || empty($this->user->getProperty('pass'))){
            $showCannotBeSame = false;
        }
        $this->form->addHTML(GI_StringUtils::getPasswordRules('new_password', 'repeat_password', $showCannotBeSame));
    }
    
    protected function addPasswordField($overWriteSettings = array()){
        $passwordText = Lang::getString('new_password');
        $passwordRequired = false;
        if($this->registerForm){
            $passwordText = Lang::getString('password');
            $passwordRequired = true;
        }
        
        $fieldSettings = GI_Form::overWriteSettings(array(
            'displayName' => $passwordText,
            'placeHolder' => $passwordText,
            'required' => $passwordRequired,
            'autoComplete' => false,
            'inputAutoCompleteVal' => 'new-password'
        ), $overWriteSettings);
        $this->form->addField('new_password', 'password', $fieldSettings);
    }
    
    protected function addRepeatPasswordField($overWriteSettings = array()){
        $rePasswordText = Lang::getString('re_enter_new_password');
        $passwordRequired = false;
        if($this->registerForm){
            $rePasswordText = Lang::getString('re_enter_password');
            $passwordRequired = true;
        }
        
        $fieldSettings = GI_Form::overWriteSettings(array(
            'displayName' => $rePasswordText,
            'placeHolder' => $rePasswordText,
            'required' => $passwordRequired,
            'autoComplete' => false,
            'description' => 'Re-enter your password to confirm.',
            'inputAutoCompleteVal' => 'new-password'
        ), $overWriteSettings);
        $this->form->addField('repeat_password', 'password', $fieldSettings);
    }
    
    protected function addContactCatField($overWriteSettings = array()) {
        $categoryArray = ContactCatFactory::getTypesArray('category');
        if (isset($categoryArray['category'])) {
            unset($categoryArray['category']);
        }
        if ($this->registerForm) {
            if (isset($categoryArray['internal'])) {
                unset($categoryArray['internal']);
            }
            $categoryValue = 'client';
        } else {
            $categoryValue = 'internal';
        }
        if(!empty($this->defaultContactCatRef)){
            $categoryValue = $this->defaultContactCatRef;
        }
        if ($this->showContactCatField) {
            $fieldSettings = GI_Form::overWriteSettings(array(
                        'displayName' => 'Category',
                        'options' => $categoryArray,
                        'value' => $categoryValue,
                        'required' => true,
                        'hideNull' => true,
                            ), $overWriteSettings);
            $this->form->addField('contact_cat_type_ref', 'dropdown', $fieldSettings);
        } else {
            $this->form->addField('contact_cat_type_ref', 'hidden', array(
                'value' => $categoryValue,
            ));
        }
    }

//    protected function openViewWrap(){
//        $this->addHTML('<div class="content_padding">');
//        return $this;
//    }
//    
//    protected function closeViewWrap(){
//        $this->addHTML('</div>');
//        return $this;
//    }
//    
//    public function buildView() {
//        $this->openViewWrap();
//            $this->buildViewHeader();
//            $this->openViewBody();
//        $this->addHTML('<div class="columns halves">');
//        $this->addHTML('<div class="column">');
//        $this->addHTML($this->form->getForm());
//        $this->addHTML('</div>');
//        $this->addHTML('<div class="column">');
//        $this->addUploaders();
//        $this->addHTML('</div>');
//        $this->addHTML('</div>');
//            $this->closeViewBody();
//        $this->closeViewWrap();
//    }
    
    protected function addViewBodyContent(){
        $this->addHTML('<div class="columns halves">');
        $this->addHTML('<div class="column">');
        $this->addHTML($this->form->getForm());
        $this->addHTML('</div>');
        $this->addHTML('<div class="column">');
        $this->addUploaders();
        $this->addHTML('</div>');
        $this->addHTML('</div>');
    }
    
//    protected function buildViewHeader(){
//        $this->addSiteTitle(Lang::getString('users'));
//        $formTitle = Lang::getString('add_user');
//        if(!$this->newUser){
//            $formTitle = Lang::getString('edit_user');
//        }
//        $this->addSiteTitle($formTitle);
//        
//        $this->openViewHeader();
//        $this->addHTML('<h1 class="main_head">' . $formTitle . '</h1>');
//        $this->closeViewHeader();
//    }
//    
//    protected function openViewHeader($class = ''){
//        $this->addHTML('<div class="view_header'.$class.'">');
//        return $this;
//    }
//    
//    protected function closeViewHeader(){
//        $this->addHTML('</div>');
//        return $this;
//    }
//    
//    protected function openViewBody($class = ''){
//        $this->addHTML('<div class="main_body'.$class.'">');
//        return $this;
//    }
//    
//    protected function closeViewBody(){
//        $this->addHTML('</div>');
//        return $this;
//    }
    
    protected function openFormBody($class ='') {
        $this->form->addHTML('<div class="form_body'.$class.'">');
    }
    
    protected function closeFormBody() {
        $this->form->addHTML('</div><!--.form_body-->');
    }
    
    protected function addContactInfoFields(){
        $this->addEmailField();
        $this->addPhoneField();
    }
    
    protected function buildFormBody() {
        $this->openHideDuringOTP();

        if (dbConnection::isModuleInstalled('contact') && empty($this->user->getProperty('id'))) {
            $this->addContactCatField();
        }

        $this->addNameFields();

        $this->addContactInfoFields();

        $this->addAdditionalFields();

        $this->addRoleAndLanguageFields();

        $this->addPasswordFields();
        
        $this->closeHideDuringOTP();
        
        $this->addOTPField();
        
        $this->addSaveBtn();
    }
    
    protected function openHideDuringOTP(){
        if(ProjectConfig::registerRequiresCodeConfirmation() && !Login::isLoggedIn()){
            $this->form->openHideDuringOTP();
        }
    }
    
    protected function closeHideDuringOTP(){
        if(ProjectConfig::registerRequiresCodeConfirmation() && !Login::isLoggedIn()){
            $this->form->closeHideDuringOTP();
        }
    }
    
    protected function addOTPField(){
        if(ProjectConfig::registerRequiresCodeConfirmation() && !Login::isLoggedIn()){
            $readyToSend = false;
            if($this->form->wasSubmitted() && $this->user->validateForm($this->form)){
                $readyToSend = true;
            }
            $this->form->addOTP('r_email','mobile', $readyToSend);
        }
    }
    
    public function addUploaders(){
        if($this->avatarUploader){
            $this->addHTML($this->avatarUploader->getHTMLView());
        }
        
        if($this->fileUploader){
            $this->addHTML($this->fileUploader->getHTMLView());
        }
    }
    
    public function beforeReturningView() {
        $this->buildView();
    }
    
}
