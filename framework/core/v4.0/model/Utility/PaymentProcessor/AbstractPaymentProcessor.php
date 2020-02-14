<?php
/**
 * Description of AbstractPaymentProcessor
 * 
 * @author General Internet
 * @copyright  2019 General Internet
 * @version    4.0.0
 */

abstract class AbstractPaymentProcessor extends GI_Object {
    
    protected $contact = NULL;
    protected $charges = NULL;
    protected $paymentMethods = NULL;
    protected $creditCardFormValidated = false;
    protected $errorMessage = '';
    protected $errorCode = '';
    
    
    protected static $settingsPaymentTypeRef = 'payment';
    protected static $subscriptionTypeRef = 'subscription';
    
    public function getSettingsPaymentTypeRef() {
        return static::$settingsPaymentTypeRef;
    }


    public function getContact() {
        return $this->contact;
    }
    
    public function setContact(AbstractContact $contact) {
        $this->contact = $contact;
    }
    
    public function getCharges() {
        return array();
    }
    
    public function getPaymentMethods() {
        return array();
    }
    
    public function getCreditCardFormView(GI_Form $form) {
        return NULL;
    }
    
    public function getErrorMessage() {
        return $this->errorMessage;
    }
    
    public function getErrorCode() {
        return $this->errorCode;
    }
    
    protected function setErrorMessage($message  = '') {
        $this->errorMessage = $message;
    }
    
    protected function setErrorCode($code = '') {
        $this->errorCode = $code;
    }
    
    protected function resetErrors() {
        $this->setErrorCode('');
        $this->setErrorMessage('');
    }
    
    public function handleCreditCardFormSubmission(GI_Form $form) {
        if ($form->wasSubmitted() && $this->validateCreditCardForm($form)) {
            
            
            return true;
        }
        return false;
    }
    
    public function validateCreditCardForm(GI_Form $form) {
        if ($this->creditCardFormValidated) {
            return true;
        }
        if ($form->wasSubmitted() && $form->validate()) {

            
            $this->creditCardFormValidated = true;
        }
        return $this->creditCardFormValidated;
    }
    
    public static function getSubscriptionTypeRef() {
        return static::$subscriptionTypeRef;
    }
    
    public function getChargeHistoryView() {
        return NULL;
    }
    
}