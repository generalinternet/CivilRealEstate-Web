<?php

class UserSignupFormView extends AbstractUserFormView {
    
    protected $step = 1;
    /**
     * @var UserDetailWe 
     */
    protected $userDetail;


    public function __construct(GI_Form $form, AbstractUser $user) {
        parent::__construct($form, $user);
        $userId = $user->getId();
        if(!empty($userId)){
            $this->userDetail = $user->getPrimeUserDetail();
        }else{
            $this->userDetail = UserDetailFactory::buildNewModel('we');
        }
    }
    
    public function setStep($step) {
        $this->step = $step;
        return $this;
    }
    
    public function buildForm() {
        if (!$this->formBuilt) {
                $this->buildFormBody();
            $this->formBuilt = true;
        }
    }

    protected function buildFormBody() {
        // $this->buildStepFormHeader();
        $this->buildStepFormBody();
    }
    
    protected function buildStepFormHeader() {
        $this->form->addHTML($this->userDetail->buildStepNavHTML($this->step));
    }
    protected function buildStepFormBody() {
        $halfFormSteps = [1, 2];

        $classes = "";
        if(in_array($this->step, $halfFormSteps)){
            $classes .= ' form__wrapper_type_half-form';
        }

        $this->form->addHTML('<div class="form__wrapper '.$classes.'">');

        switch ($this->step) {
            case 1:
                $isSigningUp = false;
                if(isset($_SESSION['is_signing_up']) && $_SESSION['is_signing_up']){
                    $this->retrieveSessionUserInfo();
                    $isSigningUp = true;
                }
                $this->form->addHTML('<h2 class="form__title form__title_has-number"><span class="form__title-number">'.$this->step.'</span> Let\'s get started</h2>');
                $this->addFirstNameField(array('class' => 'form__input'));
                $this->addLastNameField(array('required' => true, 'class' => 'form__input'));
                $this->addEmailField(array('class' => 'form__input'));
                $this->addPhoneField(array('class' => 'form__input sign_up_phone_input'));
                $this->addInvestorTypeField($isSigningUp);
                // $this->addRoleAndLanguageFields();
                $this->addOTPField();
                if(dbConnection::isModuleInstalled('contact') && empty($this->user->getProperty('id'))){
                    if(Permission::verifyByRef('add_contacts') && Permission::verifyByRef('edit_contacts')){
                        $this->addInternalField();
                    }
                }
                break;
            case 2:
                $this->form->addHTML('<h2 class="form__title form__title_has-number"><span class="form__title-number">'.$this->step.'</span> Choose a password</h2>');
                $this->addPasswordFields();
                $this->form->addField('contact_cat_type_ref', 'hidden', array(
                    'value' => 'client'
                ));
                break;
            
            case 3:
                $this->form->addHTML('<h2 class="form__title form__title_has-number"><span class="form__title-number">'.$this->step.'</span> Investor Profile</h2>');
                $this->addIdField();
                $this->addGeneralProfileFields();
                break;
                
            case 4:
                $this->form->addHTML('<h2 class="form__title form__title_has-number"><span class="form__title-number">'.$this->step.'</span> Investment Profile</h2>');
                $this->addIdField();
                $this->addInvestmentProfileFields();
                break;
            
            case 5:
                $this->addIdField();
                $this->addAgreementFormView();
            default:
        }

    }
    
    protected function addRoleField($overWriteSettings = array()){
        if (count($this->roleOptions) == 1) {
            foreach ($this->roleOptions as $key => $value) {
                $roleOptionId = $key;
                break;
            }
            $this->form->addField('role_id', 'hidden', array(
                'value' => $roleOptionId,
            ));
        } else {
            //Muliple roles
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
    
    protected function addInvestorTypeField($isSigningUp = false){

        // when user has investor type "accredited", so they can't not update it again
        $investorType = $this->userDetail->getInvestorType();
        if(!$isSigningUp && $investorType == UserDetailFactory::$INVESTOR_TYPE_ACCREDITED){
            $this->form->addField('investor_type', 'hidden', array(
                'value' => $this->userDetail->getInvestorType(),
            ));
            return;
        }

        $this->form->addField('investor_type', 'radio', array(
            'class' => 'form__input form__input_type_radio',
            'required' => true,
            'options'=> UserDetailFactory::$OPITIONS_INVESTOR_TYPE_RADIO,
            'value' => $this->userDetail->getInvestorType(),
            'formElementClass' => 'strong-label',
            'displayName' => 'Are you an accredited investor?',
        ));
    }
    
    protected function addInvestorTypeDropdownField(){
        $this->form->addField('investor_type', 'dropdown', array(
            'class' => 'form__input form__input_type_dropdown',
            'required' => true,
            'options'=> UserDetailFactory::$OPITIONS_INVESTOR_TYPE,
            'value' => $this->userDetail->getInvestorType(),
            'formElementClass' => 'change_signup_nav autofocus_off',
            'showLabel' => false,
        ));
    }
    
    protected function addIdField(){
        $this->form->addField('id', 'hidden', array(
            'value' => $this->user->getId(),
        ));
    }
    
    protected function addGeneralProfileFields(){
        $investorType = $this->userDetail->getInvestorType();

        if($investorType != UserDetailFactory::$INVESTOR_TYPE_ACCREDITED){
            $this->form->addHTML('<div class="content_group">');
                $this->form->addHTML('<h2 class="content_group_title">Investor Type</h2>');
                $this->form->addHTML('<div class="row">');
                    $this->form->addHTML('<div class="col-md-6">'); 
                        $this->addInvestorTypeDropdownField();
                    $this->form->addHTML('</div>');
                $this->form->addHTML('</div>');
            $this->form->addHTML('</div>');
        }else{
            $this->form->addField('investor_type', 'hidden', array(
                'value' => $this->userDetail->getInvestorType(),
            ));
        }
        
        $this->form->addHTML('<div class="content_group">');
            $this->form->addHTML('<h2 class="content_group_title">General Personal Data</h2>');
            $this->form->addHTML('<div class="row half_margin_bottom">');
                $this->form->addHTML('<div class="col-md-6">'); 
                    $this->addHomeAddressField();
                $this->form->addHTML('</div>');
                $this->form->addHTML('<div class="col-md-6">'); 
                    $this->addPhoneNumberField();
                $this->form->addHTML('</div>');
            $this->form->addHTML('</div>');    
            $this->form->addHTML('<div class="row">');
                $this->form->addHTML('<div class="col-md-6">'); 
                    $this->addPositionTitleField();
                    $this->addDOBField();
                    $this->addGenderField();
                $this->form->addHTML('</div>');
                $this->form->addHTML('<div class="col-md-6">'); 
                    $this->addMaritalStatusField();
                    $this->addCitizenshipField();
                    $this->addEducationLevelField();
                $this->form->addHTML('</div>');
            $this->form->addHTML('</div>');
        $this->form->addHTML('</div>');
    }
    
    function addHomeAddressField() {
        $homeAddrContactInfo = $this->userDetail->getHomeAddrContactInfo();
        $homeAddrContactInfoFormView = $homeAddrContactInfo->getFormView($this->form);
        $homeAddrContactInfoFormView->hideTypeField(true);
        $homeAddrContactInfoFormView->setFieldRequired('addr_country', true);
        $homeAddrContactInfoFormView->setFieldRequired('addr_region', true);
        $this->form->addHTML('<fieldset class="form__input form__input_type_address-set label_legend address_fieldset">'); 
            $this->form->addHTML('<legend class="main">Address</legend>');
            $homeAddrContactInfoFormView->buildForm();
        $this->form->addHTML('</fieldset>');
        $this->form->addHTML('<br>');
    }
    
    function addPhoneNumberField() {
        $this->form->addField('mobile', 'phone', array(
            'class' => 'form__input',
            'displayName' => 'Phone Number',
            'placeHolder' => 'ex. 604-123-1234',
            'value' => $this->user->getMobileNumber()
        ));
    }
    
    function addPositionTitleField() {
        $this->form->addField('position_title', 'text', array(
            'class' => 'form__input',
            'displayName'=>'Title',
            'value' => $this->userDetail->getPositionTitle(),
        ));
    }
    
    function addDOBField() {
        $this->form->addField('date_of_birth', 'date', array(
            'class' => 'form__input',
            'displayName'=>'Date of Birth',
            'value' => $this->userDetail->getDOB(),
        ));
    }
    
    function addGenderField() {
        $this->form->addField('gender', 'dropdown', array(
            'class' => 'form__input form__input_type_dropdown',
            'displayName'=>'Gender',
            'options'=>UserDetailFactory::$OPITIONS_GENDER,
            'value' => $this->userDetail->getGender(),
        ));
    }
    
    function addMaritalStatusField() {
        $this->form->addField('marital_status', 'dropdown', array(
            'class' => 'form__input form__input_type_dropdown',
            'displayName'=>'Marital Status',
            'options'=>UserDetailFactory::$OPITIONS_MARITAL_STATUS,
            'value' => $this->userDetail->getMaritalStatus(),
        ));
    }
    
    function addCitizenshipField() {
        $this->form->addField('citizenship', 'dropdown', array(
            'class' => 'form__input form__input_type_dropdown',
            'displayName'=>'Citizenship',
            'options'=>UserDetailFactory::$OPITIONS_CITIZENSHIP,
            'value' => $this->userDetail->getCitizenship(),
        ));
    }
    
    function addEducationLevelField() {
        $this->form->addField('education_level', 'dropdown', array(
            'class' => 'form__input form__input_type_dropdown',
            'displayName'=>'Education Level',
            'options'=>UserDetailFactory::$OPITIONS_EDUCATION_LEVEL,
            'value' => $this->userDetail->getEducationLevel(),
        ));
    }
    
    protected function addInvestmentProfileFields(){
        $this->form->addHTML('<div class="content_group">');
            $this->form->addHTML('<h2 class="content_group_title">Investment Experience/Knowledge</h2>');
            $this->form->addHTML('<div class="row">');
                $this->form->addHTML('<div class="col-md-8">'); 
                    $this->addInvestmentExperienceField();
                $this->form->addHTML('</div>');
            $this->form->addHTML('</div>');
        $this->form->addHTML('</div>');
        
        $this->form->addHTML('<div class="content_group">');
            $this->form->addHTML('<h2 class="content_group_title">Investment Objectives</h2>');
            $this->form->addHTML('<div class="row">');
                $this->form->addHTML('<div class="col-md-6">'); 
                    $this->addObjectiveLiquidityField();
                    $this->addObjectiveSafetyField();
                    $this->addObjectiveIncomeField();
                    $this->addObjectiveSpeculativeField();
                $this->form->addHTML('</div>');
                $this->form->addHTML('<div class="col-md-6">'); 
                    $this->addObjectiveLTGrowthField();
                    $this->addObjectiveSTGrowthField();
                    $this->addObjectiveInflationHedgingField();
                $this->form->addHTML('</div>');
            $this->form->addHTML('</div>');
        $this->form->addHTML('</div>');
    }
    
    function addInvestmentExperienceField() {
        $this->form->addField('invest_experience', 'dropdown', array(
            'class' => 'form__input form__input_type_dropdown',
            'displayName'=>'Investment Experience/Knowledge',
            'options'=>UserDetailFactory::$OPITIONS_EXPERIENCE,
            'formElementClass' => 'autofocus_off',
            'value' => $this->userDetail->getInvestmentExperience(),
        ));
    }
    
    function addObjectiveLiquidityField() {
        $this->form->addField('objective_liquidity', 'dropdown', array(
            'class' => 'form__input form__input_type_dropdown',
            'displayName'=>'Liquidity(%)',
            'options'=>UserDetailFactory::$OPITIONS_OBJECTIVE,
            'value' => $this->userDetail->getObjectiveLiquidity(),
        ));
    }
    function addObjectiveSafetyField() {
        $this->form->addField('objective_safety', 'dropdown', array(
            'class' => 'form__input form__input_type_dropdown',
            'displayName'=>'Safety(%)',
            'options'=>UserDetailFactory::$OPITIONS_OBJECTIVE,
            'value' => $this->userDetail->getObjectiveSafety(),
        ));
    }
    function addObjectiveIncomeField() {
        $this->form->addField('objective_income', 'dropdown', array(
            'class' => 'form__input form__input_type_dropdown',
            'displayName'=>'Income(%)',
            'options'=>UserDetailFactory::$OPITIONS_OBJECTIVE,
            'value' => $this->userDetail->getObjectiveIncome(),
        ));
    }
    function addObjectiveLTGrowthField() {
        $this->form->addField('objective_long_term_growth', 'dropdown', array(
            'class' => 'form__input form__input_type_dropdown',
            'displayName'=>'Long Term Growth(%)',
            'options'=>UserDetailFactory::$OPITIONS_OBJECTIVE,
            'value' => $this->userDetail->getObjectiveLTGrowth(),
        ));
    }
    
    function addObjectiveSTGrowthField() {
        $this->form->addField('objective_short_term_growth', 'dropdown', array(
            'class' => 'form__input form__input_type_dropdown',
            'displayName'=>'Short Term Growth(%)',
            'options'=>UserDetailFactory::$OPITIONS_OBJECTIVE,
            'value' => $this->userDetail->getObjectiveSTGrowth(),
        ));
    }
    
    function addObjectiveSpeculativeField() {
        $this->form->addField('objective_speculative', 'dropdown', array(
            'class' => 'form__input form__input_type_dropdown',
            'displayName'=>'Speculative(%)',
            'options'=>UserDetailFactory::$OPITIONS_OBJECTIVE,
            'value' => $this->userDetail->getObjectiveSpeculative(),
        ));
    }
    
    function addObjectiveInflationHedgingField() {
        $this->form->addField('objective_inflation_hedging', 'dropdown', array(
            'class' => 'form__input form__input_type_dropdown',
            'displayName'=>'Inflation Hedging(%)',
            'options'=>UserDetailFactory::$OPITIONS_OBJECTIVE,
            'value' => $this->userDetail->getObjectiveInflationHedging(),
        ));
    }
    
    function addAgreementFormView() {
        $agreementForm = AgreementFormFactory::getAgreementFormByTypeRef();
        if (!empty($agreementForm)) {
            $agreement = $agreementForm->getAgreementByUser($this->user);
            $agreementFormView = $agreement->getFormView($this->form);
            $agreementFormView->buildForm();
        }
    }

    protected function retrieveSessionUserInfo(){
        $email = $_SESSION['signup_r_email'];
        $firstName = $_SESSION['signup_first_name'];
        $lastName = $_SESSION['signup_last_name'];
        $investorType = $_SESSION['signup_investor_type'];

        $this->user->setProperty('first_name', $firstName);
        $this->user->setProperty('last_name', $lastName);
        $this->user->setProperty('email', $email);
        $this->userDetail->setProperty('user_detail_we.investor_type', $investorType);
        
        if(!$this->form->wasSubmitted()){
            unset($_SESSION['is_signing_up']);
            unset($_SESSION['signup_r_email']);
            unset($_SESSION['signup_mobile']);
            unset($_SESSION['signup_first_name']);
            unset($_SESSION['signup_last_name']);
            unset($_SESSION['signup_language']);
            unset($_SESSION['signup_role_id']);
            unset($_SESSION['signup_investor_type']);    
        }
    }

    protected function addPasswordFields(){
        if(!$this->showPasswordFields){
            return;
        }
        
        //the below forces firefox to respect [autocomplete="off"]
        $this->form->addHTML('<input type="text" style="display:none" />');

        $overWriteSettings = array('class' => 'form__input');
        $this->addPasswordField($overWriteSettings);
        $this->addRepeatPasswordField($overWriteSettings);
        
        $showCannotBeSame = true;
        if(!$this->user->getId() || empty($this->user->getProperty('pass'))){
            $showCannotBeSame = false;
        }
        $this->form->addHTML(GI_StringUtils::getPasswordRules('new_password', 'repeat_password', $showCannotBeSame));
    }
    
    protected function addOTPField(){
        if(ProjectConfig::registerRequiresCodeConfirmation() && !Login::isLoggedIn()){
            $readyToSend = false;
            if($this->form->wasSubmitted() && $this->user->validateForm($this->form)){
                $readyToSend = true;
            }
            $fieldName = 'otp';
            $overWriteSettings = array('class' => 'form__input form__input_type_otp');
            $otpTypeValue = filter_input(INPUT_POST, 'otp_msg_type');
            $overMsgWriteSettings = array(
                'class' => 'form__input form__input_type_radio form__input_type_otp-message sign_up_form_otp_msg_input',
                'value' => $otpTypeValue
            );

            $this->form->addOTP('r_email','mobile', $readyToSend, $fieldName, $overWriteSettings, $overMsgWriteSettings);
        }
    }
}
