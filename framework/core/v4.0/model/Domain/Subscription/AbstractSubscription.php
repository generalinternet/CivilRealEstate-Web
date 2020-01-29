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
        $search = new GI_DataSearch('contact_has_subscription');
        $search->filter('contact_id', $contact->getId())
                ->filter('subscription_id', $this->getId());
        $results = $search->select();
        if (!empty($results)) {
            $result = true;
        }
        if (!$result) {
            $softDeletedSearch = new GI_DataSearch('contact_has_subscription');
            $softDeletedSearch->filter('contact_id', $contact->getId())
                    ->filter('subscription_id', $this->getId())
                    ->filter('status', 0)
                    ->setAutoStatus(false);
            $softDeletedResults = $softDeletedSearch->select();
            if (!empty($softDeletedResults)) {
                $softDeletedDAO = $softDeletedResults[0];
                $softDeletedDAO->setProperty('status', 1);
                if ($softDeletedDAO->save()) {
                    $result = true;
                }
            }
        }
        if (!$result) {
            $defaultDAOClass = ApplicationConfig::getProperty('defaultDAOClass');
            $newLink = new $defaultDAOClass('contact_has_subscription');
            $newLink->setProperty('subscription_id', $this->getId());
            $newLink->setProperty('contact_id', $contact->getId());
            if (!$newLink->save()) {
                return false;
            }
        }
        if (!$this->subscribeContactToOnlineService($contact)) {
            return false;
        }
        return true;
    }

    protected function subscribeContactToOnlineService(AbstractContact $contact) {
        return true;
    }

    public function unsubscribeContact(AbstractContact $contact) {
        $search = new GI_DataSearch('contact_has_subscription');
        $search->filter('contact_id', $contact->getId())
                ->filter('subscription_id', $this->getId());
        $results = $search->select();
        if (empty($results)) {
            return true;
        }
        if (!$this->unsubscribeContactFromOnlineService($contact)) {
            return false;
        }
        foreach ($results as $linkToDelete) {
            $linkToDelete->setProperty('status', 0);
            if (!$linkToDelete->save()) {
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

}
