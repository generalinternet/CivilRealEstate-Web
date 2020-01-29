<?php
/**
 * Description of AbstractContactApplicationClient
 *
 * @author General Internet
 * @copyright  2019 General Internet
 * @version    4.0.0
 */
abstract class AbstractContactApplicationClient extends AbstractContactApplication {

    public function getFormView(GI_Form $form) {
        return new ContactApplicationClientFormView($form, $this);
    }

    public function getFormId() {
        return 'payment_form';
    }

    protected function handleFormSubmissionByStatus(GI_Form $form, ContactApplicationStatus $status) {
        if ($form->wasSubmitted() && $this->validateFormByStatus($form, $status)) {
            $this->setProperty('contact_app_status_id', $status->getId());
            $ref = $status->getProperty('ref');
            switch($ref) {
                case 'select_package':
                    if (!$this->handleSelectPackageFormSubmission($form)) {
                        return false;
                    }
                    break;
                case 'payment_method':
                    if (!$this->handlePaymentMethodFormSubmission($form)) {
                        return false;
                    }
                    break;
                case 'payment';
                    if (!$this->handlePaymentFormSubmission($form)) {
                        return false;
                    }
                    break;
                case 'payment_results':
                    if (!$this->handlePaymentResultsFormSubmission($form)) {
                        return false;
                    }
                    break;
                default:
                    break;
            }
            return true;
        }
        return false;
    }
    
    protected function handleSelectPackageFormSubmission(GI_Form $form) {
        if (!$this->setPropertiesFromPackageForm($form)) {
            return false;
        }
        $subscription = $this->getSubscription();
        if (!empty($subscription) && $subscription->isFree()) {
            $newStatus = ContactApplicationStatusFactory::getStatusModelByRefAndTypeRef('payment_results', 'client');
            if (empty($newStatus)) {
                return false;
            }
            $this->setProperty('contact_app_status_id', $newStatus->getId());
            if (!$this->save()) {
                return false;
            }
            if (!$this->verifyContactModels()) {
                return false;
            }
            $contact = $this->getContactOrg();
            if (!$subscription->subscribeContact($contact)) {
                return false;
            }
            GI_URLUtils::redirect(array(
                'controller'=>'contactprofile',
                'action'=>'application',
                'id'=>$this->getId(),
                'sId'=>$newStatus->getId(),
            ));
        } else {
            $this->updateStatus();
            if (!$this->save()) {
                return false;
            }
        }

        return true;
    }

    protected function setPropertiesFromPackageForm(GI_Form $form) {
        $subscriptionId = filter_input(INPUT_POST, 'subscription_id');
        $this->setProperty('subscription_id', $subscriptionId);
        $userId = filter_input(INPUT_POST, 'user_id'); //TODO - for future admin functions
        if (empty($userId)) {
            $userId = Login::getUserId();
        }
        $this->setProperty('user_id', $userId);
        return true;
    }

    protected function handlePaymentMethodFormSubmission(GI_Form $form) {
        $subscription = $this->getSubscription();
        if (empty($subscription)) {
            return false;
        }

        if (!$this->verifyContactModels()) {
            return false;
        }
        $contactOrg = $this->getContactOrg();
        if (empty($contactOrg)) {
            return false;
        }
      
        $processor = new StripePaymentProcessor();
        $processor->setContact($contactOrg);
        $selectedCardId = filter_input(INPUT_POST, 'selected_card');

        if ($selectedCardId === 'new') {
            $email = $contactOrg->getEmailAddress();
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
            $settings = $contactOrg->getPaymentSettings($processor->getSettingsPaymentTypeRef());
            if (empty($settings)) {
                return false;
            }
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

        $this->updateStatus();

        if (!$this->save()) {
            return false;
        }
        
        return true;
    }
    
    protected function handlePaymentFormSubmission(GI_Form $form) {
        $subscription = $this->getSubscription();
        if (empty($subscription)) {
            return false;
        }
        $contactOrg = $this->getContactOrg();
        if (empty($contactOrg)) {
            return false;
        }
        if (!$subscription->subscribeContact($contactOrg)) {
            return false;
        }

        return true;
    }

    protected function verifyContactModels() {
        $contactOrg = $this->getContactOrg();
        if (empty($contactOrg)) {
            $contactOrg = ContactFactory::buildNewModel('org');
            if (!$contactOrg->save()) {
                return false;
            }
        }
        $this->setProperty('contact_org_id', $contactOrg->getId());
        if (!$this->save()) {
            return false;
        }

        $contactCat = $contactOrg->getContactCat();
        if (empty($contactCat)) {
            $contactCat = ContactCatFactory::buildNewModel($this->getDefaultContactCatTypeRef());
            $contactCat->setProperty('contact_id', $contactOrg->getId());
            if (!$contactCat->save()) {
               return false;
            }
        }

        $user = $this->getUser();
        if (!empty($user)) {
            $contact = $user->getContact();
            if (empty($contact) || !ContactRelationshipFactory::establishRelationship($contactOrg, $contact)) {
                return false;
            }
            if ($contactCat->getProfileRequired()) {
                $profileURLAttrs = $contactOrg->getEditProfileURLAttrs();
                if (isset($profileURLAttrs['controller']) && $profileURLAttrs['controller'] !== 'login') {
                    $profileActionRequired = UserActionRequiredFactory::buildNewModel('redirect');
                    $profileActionRequired->setProperty('user_id', $user->getId());
                    $profileActionRequired->setProperty('rank', 50);
                    $profileActionRequired->setProperty('user_act_req_redirect.url', GI_URLUtils::buildURL($profileURLAttrs, false, true));
                    $profileActionRequired->setProperty('user_act_req_redirect.controller', $profileURLAttrs['controller']);
                    $profileActionRequired->setProperty('user_act_req_redirect.action', $profileURLAttrs['action']);

                    if (!$profileActionRequired->save()) {
                        return false;
                    }
                }
            }
        }
        return true;
    }

    protected function handlePaymentResultsFormSubmission(GI_Form $form) {
        if ($form->wasSubmitted() && $form->validate()) {
            $contactOrg = $this->getContactOrg();
            if (!empty($contactOrg)) {
                $contactOrg->setProperty('pending', 0);
                if (!$contactOrg->save()) {
                    return false;
                }
                $contactInds = $contactOrg->getChildContactInds();
                if (!empty($contactInds)) {
                    foreach ($contactInds as $contactInd) {
                        if (!empty($contactInd->getProperty('pending'))) {
                            $contactInd->setProperty('pending', 0);
                            if (!$contactInd->save()) {
                                return false;
                            }
                        }
                    }
                }
            }
            

            $user = $this->getUser();
            if (!empty($user)) {
                $search = UserActionRequiredFactory::search();
                $search->filterByTypeRef('redirect')
                        ->filter('user_id', $user->getId())
                        ->filter('redirect.controller', 'contactprofile')
                        ->filter('redirect.action', 'application');
                $redirectResults = $search->select();
                if (!empty($redirectResults)) {
                    foreach ($redirectResults as $redirectResult) {
                        if (!$redirectResult->softDelete()) {
                            GI_URLUtils::redirectToError(1000);
                        }
                    }
                }
            }


            return true;
        }
        return false;
    }

    protected function setPropertiesFromPaymentResultsForm(GI_Form $form) {
        //TODO
        return true;
    }


    protected function validateFormByStatus(GI_Form $form, ContactApplicationStatus $status) {
        //TODO
        return true;
    }

    public function getCompletedRedirectURLAttrs() {
        $contactOrg = $this->getContactOrg();
        if (!empty($contactOrg)) {
            $contactCat = $contactOrg->getContactCat();
            if (!empty($contactCat) && $contactCat->getProfileRequired()) {
                return array(
                    'controller' => 'contactprofile',
                    'action' => 'edit',
                    'id' => $this->getProperty('contact_org_id'),
                );
            }
        }
        return parent::getCompletedRedirectURLAttrs();
    }
    
    public function getSelectPackageStepURL() {
        return GI_URLUtils::buildURL(array(
            'controller'=>'contactprofile',
            'action'=>'application',
            'id'=>$this->getId(),
            'sId'=>1,
        ));
    }
    
    public function getCreditCardFormView(GI_Form $form) {
        $paymentProcessor = new StripePaymentProcessor();
        $paymentProcessor->setContact($this->getContactOrg());
        $view = $paymentProcessor->getCreditCardFormView($form);
        return $view;
    }

}