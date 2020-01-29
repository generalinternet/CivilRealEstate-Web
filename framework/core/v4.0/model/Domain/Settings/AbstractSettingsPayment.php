<?php
/**
 * Description of AbstractSettingsPayment
 *
 * @author General Internet
 * @copyright  2020 General Internet
 * @version    4.0.0
 */
abstract class AbstractSettingsPayment extends AbstractSettings {

    public function setDefaultPaymentMethodId($defaultPaymentMethodId) {
        //TODO - replace this w/ child class + overwrite when more functionality drives the need to have child class
        //this is bad practice.
        $typeRef = $this->getTypeRef();
        if ($typeRef === 'payment_stripe') {
            $this->setProperty('settings_payment_stripe.default_payment_method', $defaultPaymentMethodId);
        }
    }

    public function getDefaultPaymentMethodId() {
        //TODO - replace this w/ child class + overwrite when more functionality drives the need to have child class
        //this is bad practice.
        $typeRef = $this->getTypeRef();
        if ($typeRef === 'payment_stripe') {
            return $this->getProperty('settings_payment_stripe.default_payment_method');
        }
        return NULL;
    }

}
