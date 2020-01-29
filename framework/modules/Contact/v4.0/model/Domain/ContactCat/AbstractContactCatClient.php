<?php
/**
 * Description of AbstractContactCatClient
 *
 * @author General Internet
 * @copyright  2019 General Internet
 * @version    4.1.0
 */
abstract class AbstractContactCatClient extends AbstractContactCat {
    
    protected $terms = NULL;
    protected static $newUserDefaultRoleSystemTitle = 'client';
    protected static $applicationTypeRef = 'client';
    
    public function getTerms() {
        if (empty($this->terms)) {
            $this->terms = ContactTermsFactory::getModelById($this->getProperty('contact_cat_client.terms_id'));
        }
        return $this->terms;
    }
    
    /**
     * @param GI_Form $form
     * @return \ContactCatFormView
     */
    public function getFormView($form, $otherData = array()) {
        $formView = new ContactCatClientFormView($form, $this, $otherData);
        return $formView;
    }
    
    /**
     * @param boolean $plural
     * @return string
     */
    public function getViewTitle($plural = true) {
        $title = 'Client';
        if ($plural) {
            $title .= 's';
        }
        return $title;
    }

    public function setPropertiesFromForm(GI_Form $form) {
        $termsId = filter_input(INPUT_POST, 'terms_id');
        $this->setProperty('contact_cat_client.terms_id', $termsId);
        if(!ProjectConfig::getIsQuickbooksIntegrated()){
            $use_default_rate = filter_input(INPUT_POST, 'use_default_rate');
            if(!$use_default_rate){
                $interestRate = filter_input(INPUT_POST, 'interest_rate');
                $cmpdXDays = filter_input(INPUT_POST, 'cmpd_x_days');
                $this->setProperty('contact_cat_client.interest_rate', $interestRate / 100);
                $this->setProperty('contact_cat_client.cmpd_x_days', $cmpdXDays);
            } else {
                $this->setProperty('contact_cat_client.interest_rate', NULL);
                $this->setProperty('contact_cat_client.cmpd_x_days', NULL);
            }
        }
        $defaultPricingRegionId = filter_input(INPUT_POST, 'default_pricing_region_id');
        $this->setProperty('contact_cat_client.default_pricing_region_id', $defaultPricingRegionId);
        return true;
    }

    /**
     * 
     * @return \ContactCatClientDetailView
     */
    public function getDetailView() {
        $detailView = new ContactCatClientDetailView($this);
        return $detailView;
    }

    /**
     * Get Default Interest Rate from settings
     * @return AbstractSettings
     */
    public function getSettingsDefIntRate() {
        $settingsDefIntRates = SettingsFactory::search()
                ->filterByTypeRef('def_int_rate')
                ->select();
        if (!empty($settingsDefIntRates)) {
            //@todo : get the first default interest rates for now
            return $settingsDefIntRates[0];
        }

        return NULL;
    }

    public function validateForm(\GI_Form $form) {

        if (!$this->formValidated) {
            if ($form->wasSubmitted() && $form->validate()) {
                $formValid = true;
                if ($formValid) {
                    $this->formValidated = true;
                } else {
                    $this->formValidated = false;
                }
            }
        }
        return $this->formValidated;
    }

    /**
     * Check if input fields are related to interest rates when 'Use default..' is unchecked
     * @param GI_Form $form
     * @return boolean
     */
    protected function validateInterestRateFields(GI_Form $form) {
        if (!$form->wasSubmitted()) {
            return false;
        }
        $use_default_rate = filter_input(INPUT_POST, 'use_default_rate');

        if (!$use_default_rate) {
            $interestRate = filter_input(INPUT_POST, 'interest_rate');
            $cmpdXDays = filter_input(INPUT_POST, 'cmpd_x_days');
            if (empty($interestRate)) {
                $form->addFieldError('interest_rate', 'required', 'Required field.');
                return false;
            }
            if (empty($cmpdXDays)) {
                $form->addFieldError('cmpd_x_days', 'required', 'Required field.');
                return false;
            }
        }
        return true;
    }

    public static function getUITableCols() {
        $tableColArrays = array(
            array(
                
                'header_title'=>'<span class="qb_sml_logo dark"></span>',
                'css_header_class' => 'qb_logo_col',
                'method_name' => 'getQuickbooksExportedStatusHTML',
            ),
        );
        $UITableCols = parent::getUITableCols();
        foreach ($tableColArrays as $tableColArray) {
            $UITableCols[] = UITableCol::buildUITableColFromArray($tableColArray);
        }
        return $UITableCols;
    }

    /**
     * @param Mixed[] $contactAutoCompResult
     * @return Mixed[]
     */
    public function addDataToContactAutoCompResult($contactAutoCompResult) {
        $defaultPricingRegionId = $this->getProperty('contact_cat_client.default_pricing_region_id');
        $contactAutoCompResult['pricing_region_id'] = $defaultPricingRegionId;
        return $contactAutoCompResult;
    }

    public function getProfileDetailView() {
        $contact = $this->getContact();
        return new ContactOrgClientProfileDetailView($contact);
    }

    protected function getProfileFormViewObject(GI_Form $form, $curStep = 1) {
        $contact = $this->getContact();
        if (empty($contact)) {
            return NULL;
        }
        $view = new ContactOrgClientProfileFormView($form, $contact);
        $view->setCurStep($curStep);
        return $view;
    }

    public function isClient() {
        return true;
    }
    
            /**
     * @param GI_Form $form
     * @param type $step
     * @return boolean
     */
    public function handleProfileFormSubmission(GI_Form $form, $step = 1) {
        if ($form->wasSubmitted() && $this->validateForm($form, $step)) {
            switch ($step) {
                case 30:
                    if (!$this->handleProfilePublicProfileFormSubmission($form)) {
                        return false;
                    }
                    //TODO - check if this is the last step first
                    $contactOrg = $this->getContact();
                    if (empty($contactOrg->getProperty('profile_complete'))) {
                        $contactOrg->setProfileIsComplete();
                        if (!$contactOrg->save()) {
                            return false;
                        }
                    }

                default:
                    return parent::handleProfileFormSubmission($form, $step);
            }
            return true;
        }
        return false;
    }

    protected function handleProfilePublicProfileFormSubmission(GI_Form $form) {
        if ($form->wasSubmitted()) {
            $contactOrg = $this->getContact();
            if (empty($contactOrg) || !$contactOrg->isOrganization()) {
                return false;
            }
            if (!$this->setPropertiesFromProfilePublicProfileForm($form, $contactOrg)) {
                return false;
            }
            if (!$contactOrg->save()) {
                return false;
            }
            $publicLogoUploader = $contactOrg->getPublicLogoUploader($form);
            if ($publicLogoUploader) {
                $publicLogoUploader->setTargetFolder($contactOrg->getPublicLogoFolder());
                FolderFactory::putUploadedFilesInTargetFolder($publicLogoUploader);
            }
            $primaryInd = $contactOrg->getPrimaryIndividual();
            if (!empty($primaryInd)) {
                $user = $primaryInd->getUser();
                if (!empty($user)) {
                    $search = UserActionRequiredFactory::search();
                    $search->filterByTypeRef('redirect')
                            ->filter('user_id', $user->getId())
                            ->filter('redirect.controller', 'contactprofile')
                            ->filter('redirect.action', 'edit');
                    $redirectResults = $search->select();
                    if (!empty($redirectResults)) {
                        foreach ($redirectResults as $redirectResult) {
                            if (!$redirectResult->softDelete()) {
                                GI_URLUtils::redirectToError(1000);
                            }
                        }
                    }
                }
            }


            return true;
        }
        return false;
    }

    protected function setPropertiesFromProfilePublicProfileForm(GI_Form $form, AbstractContactOrg $contactOrg) {
        $accentColour = filter_input(INPUT_POST, 'accent_colour');
        $businessName = filter_input(INPUT_POST, 'pub_biz_name');
        $ownerName = filter_input(INPUT_POST, 'pub_owner_name');
        $webURL = filter_input(INPUT_POST, 'pub_website_url');
        if (!empty($webURL)) {
            $webURL = GI_StringUtils::fixLink($webURL);
        }
        $videoURL = filter_input(INPUT_POST, 'pub_video_url');
        if (!empty($videoURL)) {
            $videoURL = GI_StringUtils::fixLink($videoURL);
        }
        $bizDescription = filter_input(INPUT_POST, 'pub_biz_description');
        
        $contactOrg->setProperty('contact_org.pub_accent_colour', $accentColour);
        $contactOrg->setProperty('contact_org.pub_biz_name',$businessName);
        $contactOrg->setProperty('contact_org.pub_owner_name',$ownerName);
        $contactOrg->setProperty('contact_org.pub_website_url',$webURL);
        $contactOrg->setProperty('contact_org.pub_video_url',$videoURL);
        $contactOrg->setProperty('contact_org.pub_biz_description',$bizDescription);
        
        $publicAddress = $contactOrg->getPublicAddressModel();
        if (!empty($publicAddress) && $publicAddress->handleFormSubmission($form)) {
            $contactOrg->setProperty('contact_org.pub_address_id', $publicAddress->getId());
        }
        
        $publicEmail = $contactOrg->getPublicEmailModel();
        if (!empty($publicEmail) && $publicEmail->handleFormSubmission($form)) {
            $contactOrg->setProperty('contact_org.pub_email_id', $publicEmail->getId());
        }
        
        $publicPhone = $contactOrg->getPublicPhoneModel();
        if (!empty($publicPhone) && $publicPhone->handleFormSubmission($form)) {
            $contactOrg->setProperty('contact_org.pub_phone_id', $publicPhone->getId());
        }
        
        return true;
    }
    
        public function validateProfileForm(GI_Form $form, $step = 1) {
        if ($form->wasSubmitted() && $form->validate()) {
            switch ($step) {
                case 30:
                    return $this->validateProfilePublicProfileForm($form);
                default:
                    return parent::validateProfileForm($form, $step);
            }

            return true;
        }
        return false;
    }

    protected function validateProfilePublicProfileForm(GI_Form $form) {
        if ($form->wasSubmitted() && $form->validate()) {
            $errors = 0;

            if (!empty($errors)) {
                return false;
            }
            return true;
        }
        return false;
    }
    
    public function getUsesPublicProfile() {
        if (empty($this->usesPublicProfile)) {
            $usesProfile = false;
            if(dbConnection::isModuleInstalled('qna')){
                $usesProfile = true;
            }
            $this->usesPublicProfile = $usesProfile;
        }
        return $this->usesPublicProfile;
    }

    public function getUsesPayment() {
        if (empty($this->usesPayment)) {
            $usesPayment = false;
            if (dbConnection::isModuleInstalled('qna')) {
                $usesPayment = true;
            }
            $this->usesPayment = $usesPayment;
        }
        return $this->usesPayment;
    }

    public function getChangeSubFormView(GI_Form $form, $buildForm = true, $curStep = 1) {
       $view = new ContactCatClientChangeSubFormView($form, $this);
       $view->setCurStep($curStep);
       if ($buildForm) {
           $view->buildForm();
       }
       return $view;
    }

    public function handleChangeSubscriptionFormSubmission(GI_Form $form, $step = 1) {
        if ($form->wasSubmitted() && $this->validateChangeSubscriptionForm($form, $step)) {

            //steps
            //10 - select sub
            //20 - enter payment 
            //30 - confirm payment
            //40 sub updated
            switch($step) {
                case 10:
                    if (!$this->handleSelectSubscriptionFormSubmission($form)) {
                        return false;
                    }
                    break;
                case 20:
                    if (!$this->handleSelectPaymentFormSubmission($form)) {
                        return false;
                    }
                    break;
                case 30:
                    if (!$this->confirmPaymentFormSubmission($form)) {
                        return false;
                    }
                    break;
                case 40:
                    //Nothing to do
                    break;
            }
            return true;
        }
        return false;
    }

    protected function handleSelectSubscriptionFormSubmission(GI_Form $form) {
        if ($form->wasSubmitted()) {
            $subscriptionId = filter_input(INPUT_POST, 'subscription_id');
            $subscription = SubscriptionFactory::getModelById($subscriptionId);
            if (empty($subscription)) {
                return false;
            }
            $contact = $this->getContact();
            if (empty($contact)) {
                return false;
            }
            $lastStepAttrs = $contact->getChangeSubscriptionURLAttrs();
            $lastStepAttrs['step'] = 40;
            if ($contact->doesContactHaveSubscription($subscription) && $contact->unsubscribeFromAllSubscriptions(array($subscriptionId))) {
                GI_URLUtils::redirect($lastStepAttrs);
            } 

            if ($subscription->isFree()) {
                if (!$subscription->subscribeContact($contact)) {
                    return false;
                }
                $contact->unsubscribeFromAllSubscriptions(array($subscriptionId));
                GI_URLUtils::redirect($lastStepAttrs);
            }
            SessionService::setValue($this->getTargetSubscriptionCacheKey(), $subscriptionId);
            return true;
        }
        return false;
    }

    protected function handleSelectPaymentFormSubmission(GI_Form $form) {
        if ($form->wasSubmitted()) {
            $subscription = $this->getTargetSubscription();
            if (empty($subscription)) {
                return false;
            }

            $contact = $this->getContact();
            if (empty($contact) || !$contact->isOrganization()) {
                return false;
            }

            $processor = new StripePaymentProcessor();
            $processor->setContact($contact);
            $selectedCardId = filter_input(INPUT_POST, 'selected_card');

            if ($selectedCardId === 'new') {
                $email = $contact->getEmailAddress();
                if (!$processor->handleCreditCardFormSubmission($form, $email)) {
                    $message = 'ERROR';
                    $errorCode = $processor->getErrorCode();
                    if (!empty($errorCode)) {
                        $message .= ' (' . $errorCode . ')';
                    }
                    $errorMessage = $processor->getErrorMessage();
                    if (!empty($errorMessage)) {
                        $message .= ' - ' . $errorMessage;
                    }
                    if (!empty($message)) {
                        $form->addFieldError('card_errors', 'processing error', $message);
                    }
                    return false;
                }
            } else {
                $settings = $contact->getPaymentSettings($processor->getSettingsPaymentTypeRef());
                $settings->setProperty('settings_payment_stripe.default_payment_method', $selectedCardId);
                if (!$processor->updateCustomerDefaultPaymentMethod($settings)) {
                    $message = 'ERROR';
                    $errorCode = $processor->getErrorCode();
                    if (!empty($errorCode)) {
                        $message .= ' (' . $errorCode . ')';
                    }
                    $errorMessage = $processor->getErrorMessage();
                    if (!empty($errorMessage)) {
                        $message .= ' - ' . $errorMessage;
                    }
                    if (!empty($message)) {
                        $form->addFieldError('card_errors', 'processing error', $message);
                    }
                    return false;
                }
                if (!$settings->save()) {
                    return false;
                }
            }
            return true;
        }
        return false;
    }

    protected function confirmPaymentFormSubmission(GI_Form $form) {
        if ($form->wasSubmitted()) {
            $subscription = $this->getTargetSubscription();
            if (empty($subscription)) {
                return false;
            }
            $contact = $this->getContact();
            if (empty($contact) || !$contact->isOrganization()) {
                return false;
            }
            if (!$subscription->subscribeContact($contact)) {
                return false;
            }
            
            if (!$contact->unsubscribeFromAllSubscriptions(array($subscription->getId()))) {
                return false;
            }
            
            $cacheKey = $this->getTargetSubscriptionCacheKey();
            if (empty($cacheKey)) {
                SessionService::unsetValue($cacheKey);
            }

            return true;
        }
        return false;
    }

    /**
     * @return AbstractSubscription
     */
    protected function getTargetSubscription() {
        $cacheKey = $this->getTargetSubscriptionCacheKey();
        $value = SessionService::getValue($cacheKey);
        if (empty($value)) {
            return NULL;
        }
        $subscription = SubscriptionFactory::getModelById($value);
        return $subscription;
    }




    protected function getTargetSubscriptionCacheKey() {
        return 'target_sub_' . $this->getId();
    }
    
    

}
