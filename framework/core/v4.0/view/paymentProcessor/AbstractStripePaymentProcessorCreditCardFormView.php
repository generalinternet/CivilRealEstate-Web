<?php
/**
 * Description of AbstractStripePaymentProcessorCreditCardFormView
 *
 * @author General Internet
 * @copyright  2020 General Internet
 * @version    4.0.0
 */
abstract class AbstractStripePaymentProcessorCreditCardFormView extends AbstractPaymentProcessorCreditCardFormView {

    public function __construct(\GI_Form $form) {
        parent::__construct($form);
        $this->addJS("https://js.stripe.com/v3/");
        $this->addJS('framework/core/' . FRMWK_CORE_VER . '/resources/js/payments/stripe_custom.js');
        $this->addCSS('https://cdnjs.cloudflare.com/ajax/libs/paymentfont/1.1.2/css/paymentfont.min.css');
    }

    protected function buildFormBody() {
        $this->addNameField();
        $this->addCardNumberField();
        $this->addExpAndCvcSection();
        $this->addPostalCodeField();
        $this->form->addHTML('<div id="card-errors" role="alert"></div>');
    }

    protected function addExpAndCvcSection() {
        $this->form->addHTML('<div class="columns halves">')
                ->addHTML('<div class="column">');
        $this->addExpField();
        $this->form->addHTML('</div>')
                ->addHTML('<div class="column">');
        $this->addCVCField();
        $this->form->addHTML('</div>')
                ->addHTML('</div>');
    }

    protected function addCardNumberField() {
        $this->form->addHTML('<div class="columns">');
        $this->form->addHTML('<div class="form_element">');
        $this->form->addHTML('<label for="card-number" class="main">')
                ->addHTML('Credit Card Number')
                ->addHTML('</label>')
                ->addHTML('<div class="field_content">')
                ->addHTML('<div id="card-number"></div>')
                ->addHTML('<span class="brand"><i class="pf pf-credit-card" id="brand-icon"></i></span>')
                ->addHTML('</div>');
        $this->form->addHTML('</div>');
        $this->form->addHTML('</div>');
    }
    protected function addExpField() {
        $this->form->addHTML('<div class="columns">');
        $this->form->addHTML('<div class="form_element">');
        $this->form->addHTML('<label for="card-exp" class="main">')
                ->addHTML('Expiry (MM/YY)')
                ->addHTML('</label>')
                ->addHTML('<div id="card-exp"></div>');
        $this->form->addHTML('</div>');
        $this->form->addHTML('</div>');
    }

    protected function addCVCField() {
        $this->form->addHTML('<div class="columns">');
        $this->form->addHTML('<div class="form_element">');
        $this->form->addHTML('<label for="card-cvc" class="main">')
                ->addHTML('CVC (code on the back)')
                ->addHTML('</label>')
                ->addHTML('<div id="card-cvc"></div>');
        $this->form->addHTML('</div>');
        $this->form->addHTML('</div>');
    }

    protected function addNameField($overwriteSettings = array()) {
        $defaultSettings = array(
            'displayName' => 'Cardholder Name'
        );
        $fieldSettings = GI_Form::overWriteSettings($defaultSettings, $overwriteSettings);
        $this->form->addHTML('<div class="columns">');
        $this->form->addField('name', 'text', $fieldSettings);
        $this->form->addHTML('</div>');
    }

    protected function addPostalCodeField($overwriteSettings = array()) {
        $defaultSettings = array(
            'displayName' => 'Postal/Zip Code'
        );
        $fieldSettings = GI_Form::overWriteSettings($defaultSettings, $overwriteSettings);
        $this->form->addHTML('<div class="columns">');
        $this->form->addField('addr_region', 'text', $fieldSettings);
        $this->form->addHTML('</div>');
    }

    protected function buildFormHeader() {

    }

    protected function buildFormFooter() {
        parent::buildFormFooter();
        if (!$this->isEmbedded) {
                        $this->form->addHTML('<div class="center_btns wrap_btns">');
            $this->addSubmitBtn();
            $this->addCancelBtn();
            $this->form->addHTML('</div>');
        }
    }

    
    public function addSubmitBtn() {
        $this->form->addHTML('<span class="submit_btn">Save</span>');
    }

    public function addCancelBtn() {
        $dataURLAttr = '';
        $this->form->addHTML('<span class="other_btn gray close_gi_modal" ' . $dataURLAttr . '>Cancel</span>');
    }

}
