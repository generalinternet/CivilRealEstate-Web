<?php

class ContactApplicationClient extends AbstractContactApplicationClient {
    protected function addPaymentProcessorSection() {
        $this->addJS("https://js.stripe.com/v3/");
        $this->addJS('framework/core/' . FRMWK_CORE_VER. '/resources/js/payments/stripe_custom.js');

        $this->addNameField(array(
            'displayName' => 'Cardholder Name'
        ));

        $this->addCardNumberField();
        $this->addExpAndCvcSection();
        $this->addPostalCodeField();
        $this->addEmailField(array(
            'displayName' => 'Email for Receipt'
        ));

        $this->form->addHTML('<div id="card-errors" role="alert"></div>');
    }
}