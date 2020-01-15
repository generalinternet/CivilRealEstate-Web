<?php

class UserDetailWe extends UserDetail {
    
    protected $homeAddrContactInfo;
    protected $phoneNumContactInfo;
    
//    public function handleFormSubmission(GI_Form $form) {
//        if ($form->wasSubmitted() && $form->validate()) {
//            $this->setPropertiesFromForm($form);
//            
//            if (!parent::handleFormSubmission($form)) {
//                return false;
//            }
//            
//            return true;
//        }
//        return false;
//    }
    
    public function handleGeneralProfileFormSubmission(GI_Form $form) {
        if ($form->wasSubmitted() && $form->validate()) {
            $this->setPropertiesFromGeneralProfileForm($form);
            
            if (!parent::handleFormSubmission($form)) {
                return false;
            }
            return true;
        }
        return false;
    }
    
    public function handleInvestmentProfileFormSubmission(GI_Form $form) {
        if ($form->wasSubmitted() && $form->validate()) {
            $this->setPropertiesFromInvestmentProfileForm($form);
            
            if (!parent::handleFormSubmission($form)) {
                return false;
            }
            return true;
        }
        return false;
    }
    
    
    
    public function setPropertiesFromForm($form){
        if ($form->wasSubmitted()) {
            $this->setPropertiesFromGeneralProfileForm();
            $this->setPropertiesFromInvestmentProfileForm();
            return true;
        }
        return false;
    }
    
    public function setPropertiesFromGeneralProfileForm(GI_Form $form) {
        if ($form->wasSubmitted()) {
            $investorType = filter_input(INPUT_POST, 'investor_type');
            $positionTitle = filter_input(INPUT_POST, 'position_title');
            $dateOfBirth = filter_input(INPUT_POST, 'date_of_birth');
            $gender = filter_input(INPUT_POST, 'gender');
            $maritalStatus = filter_input(INPUT_POST, 'marital_status');
            $citizenship = filter_input(INPUT_POST, 'citizenship');
            $educationLevel = filter_input(INPUT_POST, 'education_level');
            $mobile = trim(filter_input(INPUT_POST, 'mobile'));

            $this->setInvestorType($investorType);
            $this->setPositionTitle($positionTitle);
            $this->setDOB($dateOfBirth);
            $this->setGender($gender);
            $this->setMaritalStatus($maritalStatus);
            $this->setCitizenship($citizenship);
            $this->setEducationLevel($educationLevel);

            $homeAddressContactInfo = $this->getHomeAddrContactInfo();
            if (!$homeAddressContactInfo->handleFormSubmission($form)) {
                return false;
            }
            if (!empty($homeAddressContactInfo)) {
                $homeAddressContactInfoId = $homeAddressContactInfo->getProperty('id');
                $this->setProperty('user_detail_we.home_addr_id', $homeAddressContactInfoId);
            }
            if (!empty($mobile)) {
                $user = $this->getUser();
                $user->setProperty('mobile', $mobile);
                $user->save();
            }
            return true;
        }
        return false;
        
    }
    
    public function setPropertiesFromInvestmentProfileForm(GI_Form $form) {
        if ($form->wasSubmitted()) {
            $investExperience = filter_input(INPUT_POST, 'invest_experience');
            $objectiveLiquidity = filter_input(INPUT_POST, 'objective_liquidity');
            $objectiveSafety = filter_input(INPUT_POST, 'objective_safety');
            $objectiveIncome = filter_input(INPUT_POST, 'objective_income');
            $objectiveLTGrowth = filter_input(INPUT_POST, 'objective_long_term_growth');
            $objectiveSTGrowth = filter_input(INPUT_POST, 'objective_short_term_growth');
            $objectiveSpeculative = filter_input(INPUT_POST, 'objective_speculative');
            $objectiveInflationHedging = filter_input(INPUT_POST, 'objective_inflation_hedging');
            
            $this->setInvestmentExperience($investExperience);
            $this->setObjectiveLiquidity($objectiveLiquidity);
            $this->setObjectiveSafety($objectiveSafety);
            $this->setObjectiveIncome($objectiveIncome);
            $this->setObjectiveLTGrowth($objectiveLTGrowth);
            $this->setObjectiveSTGrowth($objectiveSTGrowth);
            $this->setObjectiveSpeculative($objectiveSpeculative);
            $this->setObjectiveInflationHedging($objectiveInflationHedging);
            
            return true;
        }
        return false;
    }
    
    public function getHomeAddrContactInfo() {
        if (empty($this->homeAddrContactInfo)) {
            $homeAddrId = $this->getProperty('user_detail_we.home_addr_id');
            if (!empty($homeAddrId)) {
                $this->homeAddrContactInfo = ContactInfoFactory::getModelById($homeAddrId);
            } else {
                $this->homeAddrContactInfo = ContactInfoFactory::buildNewModel('address');
            }
        }
        
        return $this->homeAddrContactInfo;
    }
    
    public function getPhoneNumContactInfo() {
        if (empty($this->phoneNumContactInfo)) {
            $phoneNumId = $this->getProperty('user_detail_we.phone_num_id');
            if (!empty($phoneNumId)) {
                $this->phoneNumContactInfo = ContactInfoFactory::getModelById($phoneNumId);
            } else {
                $this->phoneNumContactInfo = ContactInfoFactory::buildNewModel('phone_num');
            }
        }
        
        return $this->phoneNumContactInfo;
    }
    
    public function isAccreditedInvestor() {
        $investorType = $this->getInvestorType();

        $agreementForm = AgreementFormFactory::getAgreementFormByTypeRef();
        $agreement = $agreementForm->getAgreementByUser($this->getUser());
        $agreementItems = $agreement->getItems();

        $isAccredited = true;

        if($investorType == UserDetailFactory::$INVESTOR_TYPE_NONACCREDITED){
            $isAccredited = false;
        }

        if (
            $investorType == UserDetailFactory::$INVESTOR_TYPE_ACCREDITED &&
            empty($agreementItems)
        ) {
            $isAccredited = false;
             // if this is accredited but not signed agreement, then correct it to non-accredited
            $this->toggleAccreditedInvestorType(false);
        }

        return $isAccredited;
    }
    
    public function getInvestorType() {
        return $this->getProperty('user_detail_we.investor_type');
    }
    
    public function setInvestorType($value) {
        $this->setProperty('user_detail_we.investor_type', $value);
    }
    
    public function getPositionTitle() {
        return $this->getProperty('user_detail_we.position_title');
    }
    
    public function setPositionTitle($value) {
        $this->setProperty('user_detail_we.position_title', $value);
    }
    
    public function getDOB() {
        $dateOfBirth = $this->getProperty('user_detail_we.date_of_birth');
        if (empty($dateOfBirth)) {
            $dateOfBirthObj = new DateTime();
            $dateOfBirthObj->sub(new DateInterval('P50Y'));
            $dateOfBirth = $dateOfBirthObj->format('Y-m-d');
        }
        return $dateOfBirth;
    }
    
    public function setDOB($value) {
        $this->setProperty('user_detail_we.date_of_birth', $value);
    }
    
    public function getGender() {
        return $this->getProperty('user_detail_we.gender');
    }
    
    public function setGender($value) {
        $this->setProperty('user_detail_we.gender', $value);
    }
    
    public function getMaritalStatus() {
        return $this->getProperty('user_detail_we.marital_status');
    }
    
    public function setMaritalStatus($value) {
        $this->setProperty('user_detail_we.marital_status', $value);
    }
    
    public function getCitizenship() {
        return $this->getProperty('user_detail_we.citizenship');
    }
    
    public function setCitizenship($value) {
        $this->setProperty('user_detail_we.citizenship', $value);
    }
    
    public function getEducationLevel() {
        return $this->getProperty('user_detail_we.education_level');
    }
    
    public function setEducationLevel($value) {
        $this->setProperty('user_detail_we.education_level', $value);
    }
    
    public function getInvestmentExperience() {
        return $this->getProperty('user_detail_we.invest_experience');
    }
    
    public function setInvestmentExperience($value) {
        $this->setProperty('user_detail_we.invest_experience', $value);
    }
    
    public function getObjectiveLiquidity() {
        return $this->getProperty('user_detail_we.objective_liquidity');
    }
    
    public function setObjectiveLiquidity($value) {
        $this->setProperty('user_detail_we.objective_liquidity', $value);
    }
    
    public function getObjectiveSafety() {
        return $this->getProperty('user_detail_we.objective_safety');
    }
    
    public function setObjectiveSafety($value) {
        $this->setProperty('user_detail_we.objective_safety', $value);
    }
    
    public function getObjectiveIncome() {
        return $this->getProperty('user_detail_we.objective_income');
    }
    
    public function setObjectiveIncome($value) {
        $this->setProperty('user_detail_we.objective_income', $value);
    }
    
    public function getObjectiveLTGrowth() {
        return $this->getProperty('user_detail_we.objective_long_term_growth');
    }
    
    public function setObjectiveLTGrowth($value) {
        $this->setProperty('user_detail_we.objective_long_term_growth', $value);
    }
    
    public function getObjectiveSTGrowth() {
        return $this->getProperty('user_detail_we.objective_short_term_growth');
    }
    
    public function setObjectiveSTGrowth($value) {
        $this->setProperty('user_detail_we.objective_short_term_growth', $value);
    }
    
    public function getObjectiveSpeculative() {
        return $this->getProperty('user_detail_we.objective_speculative');
    }
    
    public function setObjectiveSpeculative($value) {
        $this->setProperty('user_detail_we.objective_speculative', $value);
    }
    
    public function getObjectiveInflationHedging() {
        return $this->getProperty('user_detail_we.objective_inflation_hedging');
    }
    
    public function setObjectiveInflationHedging($value) {
        $this->setProperty('user_detail_we.objective_inflation_hedging', $value);
    }
    
    public function getSignupNextStep($curStep = 1){
        $nextStep = (int)$curStep + 1;
//        if ($this->isAccreditedInvestor()) {
//            if ($curStep == 4) {
//                $nextStep = -1;
//            }
//        } else {
//            if ($curStep == 3) {
//                $nextStep = -1;
//            }
//        }
        $totalSignupStep = $this->getTotalSignupStep();
        if ($nextStep > $totalSignupStep) {
            $nextStep = -1;
        }
        return $nextStep;
    }
    
    public function getNextStepAttrs($curStep = 1, $userId = NULL, $ajax = 0){
        $nextStep = $this->getSignupNextStep($curStep);
        if ($nextStep == -1) {
            $attr = parent::getNextStepAttrs($curStep, $userId, $ajax);
        } else {
            $attr = array(
                'controller' => 'user',
                'action' => 'signup',
                'step' => $this->getSignupNextStep($curStep),
                'ajax' => $ajax,
            );
            if (!empty($userId)) {
                $attr['id'] = $userId;
            }
        }
        return $attr;
    }
    
    public function getPrevStepAttrs($curStep = 1, $userId = NULL, $ajax = 0){
        if ($curStep == 1) {
            $prevStep = 0;
        } else {
            $prevStep = (int)$curStep - 1;
        }
        $attr = array(
            'controller' => 'user',
            'action' => 'signup',
            'step' => $prevStep,
            'ajax' => $ajax,
        );
        if (!empty($userId)) {
            $attr['id'] = $userId;
        }
        return $attr;
    }
    
    public function getSignupStepDataArray() {
        if ($this->getInvestorType() == UserDetailFactory::$INVESTOR_TYPE_ACCREDITED) {
            parent::setSignupStepDataArray (array(
                array(
                    'step' => 1,
                    'title' => 'User',
                    'icon' => 'person',
                ),
                array(
                    'step' => 2,
                    'title' => 'Password',
                    'icon' => 'unlocked',
                ),
                array(
                    'step' => 3,
                    'title' => 'Profile',
                    'icon' => 'account',
                ),
                array(
                    'step' => 4,
                    'title' => 'Investment',
                    'icon' => 'dollars',
                ),
                array(
                    'step' => 5,
                    'title' => 'Accreditation',
                    'icon' => 'clipboard_stock',
                    'auth' => 'accredited',
                ),
            ));
        } else {
            parent::setSignupStepDataArray (array(
                array(
                    'step' => 1,
                    'title' => 'User',
                    'icon' => 'person',
                ),
                array(
                    'step' => 2,
                    'title' => 'Password',
                    'icon' => 'unlocked',
                ),
                array(
                    'step' => 3,
                    'title' => 'Profile',
                    'icon' => 'account',
                ),
                array(
                    'step' => 4,
                    'title' => 'Investment',
                    'icon' => 'dollars',
                ),
            ));
        }
        
        return parent::getSignupStepDataArray();
    }
    
    public function getUserInfo($prop, $defaultValue = 'Empty'){
        $value = $this->getProperty($prop);
        if(!empty($value)){
            return $value;
        }
        return $defaultValue;
    }

    public function getBasicInfos(){
        $user = $this->getUser();
        $firstName = 'Missing Data';
        $lastName = 'Missing Data';
        $email = 'Missing Data';
        if($user){
            $firstName = $user->getProperty('first_name');
            $lastName = $user->getProperty('last_name');
            $email = $user->getProperty('email');
        }

        $colItems = array(
            'First name' => $firstName,
            'Last name' => $lastName,
            'Email' => $email,
        );

        // Is accredited investor
        if(!empty($this)){
            $isAccreditedInvestor = 'No';
            if($this->isAccreditedInvestor()){
                $isAccreditedInvestor = 'Yes';
            }
            $colItems['Is accredited investor'] = $isAccreditedInvestor;
        }
        return $colItems;
    }

    public function getInvestorProfileInfos(){
        $dob = $this->getDOB();
        if(empty($dob)){
            $dob = 'Empty';
        }

        $addressObj = $this->getHomeAddrContactInfo();
        $address = $addressObj->getAddressString();
        if(empty($address)){
            $address = 'Empty';
        }

        $user = $this->getUser();
        $phone = $user->getMobileNumber();
        if(empty($phone)){
            $phone = 'Empty';
        }

        $investorType = $this->getUserInfo('user_detail_we.investor_type');
        if($investorType !== 'Empty'){
            $investorType = UserDetailFactory::$OPITIONS_INVESTOR_TYPE[$investorType];
        }

        $maritalStatus = $this->getUserInfo('user_detail_we.marital_status');
        if($maritalStatus !== 'Empty'){
            $maritalStatus = UserDetailFactory::$OPITIONS_MARITAL_STATUS[$maritalStatus];
        }

        $citizenship = $this->getUserInfo('user_detail_we.citizenship');
        if($citizenship !== 'Empty'){
            $citizenship = UserDetailFactory::$OPITIONS_CITIZENSHIP[$citizenship];
        }

        $educationLevel = $this->getUserInfo('user_detail_we.education_level');
        if($educationLevel !== 'Empty'){
            $educationLevel = UserDetailFactory::$OPITIONS_EDUCATION_LEVEL[$educationLevel];
        }

        $gender = $this->getUserInfo('user_detail_we.gender');
        if($gender !== 'Empty'){
            $gender = UserDetailFactory::$OPITIONS_GENDER[$gender];
        }

        $colItems = array(
            'Title' => $this->getUserInfo('user_detail_we.position_title'),
            'Investor Type' => $investorType,
            'Date of Birth' => $dob,
            'Marital Status' => $maritalStatus,
            'Address' => $address,
            'Phone Number' => $phone,
            'Citizenship' => $citizenship,
            'Education Level' => $educationLevel,
            'Gender' => $gender,
        );
        return $colItems;
    }

    public function getInvestmentProfileInfos(){
        $investExp = $this->getUserInfo('user_detail_we.invest_experience');
        if($investExp !== 'Empty'){
            $investExp = UserDetailFactory::$OPITIONS_EXPERIENCE[$investExp];
        }

        $objectiveLiquidity = $this->getUserInfo('user_detail_we.objective_liquidity');
        if($objectiveLiquidity !== 'Empty'){
            $objectiveLiquidity = UserDetailFactory::$OPITIONS_OBJECTIVE[$objectiveLiquidity];
        }

        $objectiveSafety = $this->getUserInfo('user_detail_we.objective_safety');
        if($objectiveSafety !== 'Empty'){
            $objectiveSafety = UserDetailFactory::$OPITIONS_OBJECTIVE[$objectiveSafety];
        }

        $objectiveLongTerm = $this->getUserInfo('user_detail_we.objective_long_term_growth');
        if($objectiveLongTerm !== 'Empty'){
            $objectiveLongTerm = UserDetailFactory::$OPITIONS_OBJECTIVE[$objectiveLongTerm];
        }

        $objectiveIncome = $this->getUserInfo('user_detail_we.objective_income');
        if($objectiveIncome !== 'Empty'){
            $objectiveIncome = UserDetailFactory::$OPITIONS_OBJECTIVE[$objectiveIncome];
        }

        $objectiveShortTerm = $this->getUserInfo('user_detail_we.objective_short_term_growth');
        if($objectiveShortTerm !== 'Empty'){
            $objectiveShortTerm = UserDetailFactory::$OPITIONS_OBJECTIVE[$objectiveShortTerm];
        }

        $objectiveSpeculative = $this->getUserInfo('user_detail_we.objective_speculative');
        if($objectiveSpeculative !== 'Empty'){
            $objectiveSpeculative = UserDetailFactory::$OPITIONS_OBJECTIVE[$objectiveSpeculative];
        }

        $objectiveInflation = $this->getUserInfo('user_detail_we.objective_inflation_hedging');
        if($objectiveInflation !== 'Empty'){
            $objectiveInflation = UserDetailFactory::$OPITIONS_OBJECTIVE[$objectiveInflation];
        }

        return array(
            'Investment Experience/Knowledge' => $investExp,
            'Liquidity' => $objectiveLiquidity,
            'Safety' => $objectiveSafety,
            'Long Term Growth' => $objectiveLongTerm,
            'Income' => $objectiveIncome,
            'Short Term Growth' => $objectiveShortTerm,
            'Speculative' => $objectiveSpeculative,
            'Inflation Hedging' => $objectiveInflation,
        );
    }

    public function getAccreditationPdfLink(){
        $userId = $this->getProperty('user_detail.user_id');
        return GI_URLUtils::buildCleanURL(array(
            'controller' => 'contact',
            'action' => 'angelContactViewPdf',
            'userId' => $userId
        ));
    }

    public function toggleAccreditedInvestorType($isAccredited = true){
        $investorType = UserDetailFactory::$INVESTOR_TYPE_NONACCREDITED;
        if($isAccredited){
            $investorType = UserDetailFactory::$INVESTOR_TYPE_ACCREDITED;
        }

        $this->setProperty('user_detail_we.investor_type', $investorType);
        $this->save();
    }
}