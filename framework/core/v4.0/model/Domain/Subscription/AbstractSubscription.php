<?php
/**
 * Description of AbstractSubscription
 * 
 * @author General Internet
 * @copyright  2020 General Internet
 * @version    4.0.0
 */
abstract class AbstractSubscription extends GI_Model {
    
    protected $priceCurrency;
    /** @var AbstractPaymentProcessor */
    protected $paymentProcessor = NULL;
    
    public function getTitle(){
        return $this->getProperty('title');
    }
    
    public function getDescription(){
        return $this->getProperty('description');
    }
    
    public function getPrice($formatForDisplay = false, $includeCurrencyName = false) {
        $price = $this->getProperty('price');
        if ($formatForDisplay) {
            $currency = $this->getPriceCurrency();
            if (!empty($currency)) {
                $symbol = $currency->getProperty('symbol');
            } else {
                $symbol = '$';
            }
            $price = $symbol . GI_StringUtils::formatMoney($price);
            if ($includeCurrencyName && !empty($currency)) {
                $price .= ' (' . $currency->getProperty('name') . ')';
            }
        }
        return $price;
    }
    
    public function getPriceCurrency() {
        if (empty($this->priceCurrency)) {
            $this->priceCurrency = CurrencyFactory::getModelById($this->getProperty('price_currency_id'));
        }
        return $this->priceCurrency;
    }
    
    public function getDetailView() {
        return new SubscriptionDetailView($this);
    }

    public function getFormView(GI_Form $form) {
        return new SubscriptionFormView($form);
    }
    
    public function getPaymentProcessor() {
        return $this->paymentProcessor;
    }
    
    public function setPaymentProcessor(AbstractPaymentProcessor $paymentProcessor) {
        $this->paymentProcessor = $paymentProcessor;
    }

    public function handleFormSubmission(GI_Form $form) {
        if ($form->wasSubmitted() && $this->validateForm($form)) {
            if (!$this->setPropertiesFromForm($form)) {
                return false;
            }
            if (!$this->save()) {
                return false;
            }
            return true;
        }
        return false;
    }
    
    protected function setPropertiesFromForm(GI_Form $form) {
        return false;
    }
    
    public function validateForm(\GI_Form $form) {
        if ($this->formValidated) {
            return true;
        }
        if ($form->wasSubmitted() && $form->validate()) {
            if ($this->validateFormFields($form)) {
                $this->formValidated = true;
                return $this->formValidated;
            }
        }
        return false;
    }

    protected function validateFormFields(GI_Form $form) {
        return true;
    }

    public function subscribeContact(AbstractContact $contact) {
        $result = false;
        $currentSub = $contact->getCurrentSubscription();
        $upcomingSub = $contact->getUpcomingSubscription();
        if (empty($currentSub)) {
            //sub to this immediately
            if (!empty($this->createSubscriptionLinkToContact($contact, $this->determineStartDateForContactSubscription($contact)))) {
                $result = true;
            }
        } else {
            if ($currentSub->getId() === $this->getId()) {
                //already subbed to this, return true
                if (!empty($upcomingSub)) {
                    $contactHasSubArray = ContactHasSubscriptionFactory::getModelsByContact($contact);
                    if (!empty($contactHasSubArray)) {
                        foreach ($contactHasSubArray as $contactHasSub) {
                            $contactHasSub->setProperty('end_date_time', NULL);
                            $contactHasSub->save();
                        }
                    }
                    $upcomingSub->unsubscribeContact($contact, '', true);
                }
                //return true;
                $result = true;
            } else {

                if (!empty($upcomingSub)) {
                    if ($upcomingSub->getId() === $this->getId()) {
                        //already subbed to this in the future, return true
                        return true;
                    } else {
                        //unsubscribe from upcoming and soft-delete connection
                        $upcomingSub->unsubscribeContact($contact, '', true);
                        //sub to this one (either immediately or when current (paid) one ends)
                        $this->createSubscriptionLinkToContact($contact, $this->determineStartDateForContactSubscription($contact));
                        $result = true;
                    }
                } else {
                    //subbed to another sub
                    //determine when to start this sub, and sub to it

                    $this->createSubscriptionLinkToContact($contact, $this->determineStartDateForContactSubscription($contact));
                    $result = true;
                    //determine end date for current sub, and unsubscribe
                    $currentSub->unsubscribeContact($contact);

                }
            }
        }

        if (!$this->isFree() && $result && !$this->subscribeContactToOnlineService($contact)) {
            return false;
        }
        return true;
    }
    
    protected function createSubscriptionLinkToContact(AbstractContact $contact, $startDateTime) {
        $defaultDAOClass = ApplicationConfig::getProperty('defaultDAOClass');
        $newLink = new $defaultDAOClass('contact_has_subscription');
        $newLink->setProperty('subscription_id', $this->getId());
        $newLink->setProperty('contact_id', $contact->getId());
        $newLink->setProperty('start_date_time', $startDateTime);
        if (!$newLink->save()) {
            return NULL;
        }
        return $newLink;
    }

    protected function determineStartDateForContactSubscription(AbstractContact $contact) {
        return GI_Time::getDateTime();
    }
    
    protected function determineEndDateForContactSubscription(AbstractContact $contact) {
        return GI_Time::getDateTime();
    }

    protected function subscribeContactToOnlineService(AbstractContact $contact) {
        return true;
    }

    public function unsubscribeContact(AbstractContact $contact, $endDateTime = '', $softDeleteLink = false) {
        if (empty($endDateTime)) {
            $endDateTime = $this->determineEndDateForContactSubscription($contact);
        }
        $search = new GI_DataSearch('contact_has_subscription');
        $search->filter('contact_id', $contact->getId())
                ->filterNull('end_date_time')
                ->filter('subscription_id', $this->getId());
        $results = $search->select();
        if (empty($results)) {
            return true;
        }
        if (!$this->unsubscribeContactFromOnlineService($contact)) {
            return false;
        }
        foreach ($results as $linkToDAO) {
            if ($softDeleteLink) {
                $linkToDAO->setProperty('status', 0);
            } else {
                $linkToDAO->setProperty('end_date_time', $endDateTime);
            }
            
            if (!$linkToDAO->save()) {
                return false;
            }
        }
        return true;
    }

    protected function unsubscribeContactFromOnlineService(AbstractContact $contact) {
        return true;
    }
    
    public function getPriceSummaryString() {
        $string = '';
        $currency = $this->getPriceCurrency();
        $currencyName = $currency->getProperty('name');
        $string .= $currencyName . ' ';
        $string .= $currency->getProperty('symbol') . GI_StringUtils::formatMoney($this->getProperty('price'));
        $string .= ' per month'; //TODO  - setting on subscription
        return $string;
    }
    
    public function isFree() {
        if (!empty($this->getProperty('is_free'))) {
            return true;
        }
        return false;
    }
    
    public function getTrialPeriodDays() {
        $trialPeriodDays = $this->getProperty('trial_period_days');
        if (empty($trialPeriodDays)) {
            return 0;
        }
        return $trialPeriodDays;
    }

    public function getExternalBillingPeriodDates(AbstractContact $contact) {
        return array();
    }

}
