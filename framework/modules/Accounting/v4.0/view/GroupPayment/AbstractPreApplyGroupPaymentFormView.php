<?php
/**
 * Description of AbstractImportPaymentsFileFormView
 *
 * @author General Internet
 * @copyright  2018 General Internet
 * @version    3.0.0
 */
abstract class AbstractPreApplyGroupPaymentFormView extends GI_View {
    
    protected $form;
    protected $paymentTypeRef;
    protected $currency;
    protected $contact;
    
    public function __construct(GI_Form $form, $paymentTypeRef, AbstractCurrency $currency, AbstractContact $contact) {
        parent::__construct();
        $this->form = $form;
        $this->paymentTypeRef = $paymentTypeRef;
        $this->currency = $currency;
        $this->contact = $contact;
    }
    
    public function buildForm() {
        $this->form->addField('new_or_old', 'radio', array(
            'options' => array(
                'new' => 'New',
                'old' => 'Existing'),
            'displayName' => 'New or Existing payment?',
            'required' => true,
            'fieldClass'=>'radio_toggler',
            'value'=>'old'
        ));
        $this->form->addHTML('<div class="radio_toggler_element" data-group="new_or_old" data-element="old">');
        $gpAutoCompURL = GI_URLUtils::buildURL(array(
                    'controller' => 'autocomplete',
                    'action' => 'groupPayment',
                    'currencyId' => $this->currency->getProperty('id'),
                    'type' => $this->paymentTypeRef,
                    'contactId' => $this->contact->getProperty('id'),
                    'ajax' => 1
                        ), false, true);
        $this->form->addField('group_payment_id', 'autocomplete', array(
            'displayName' => 'Payment',
            'placeHolder' => 'start typing a transaction number or date',
            'autocompURL' => $gpAutoCompURL,
            'hideDescOnError' => false,
        ));
        $this->form->addHTML('</div>');
        $this->form->addHTML('<span class="submit_btn" tabindex="0" title="Continue">Continue</span>');
    }

    protected function buildView() {
        $this->addHTML('<h1>Apply Payment</h1>');
        $this->addHTML('<div class="content_padding">');
        $this->addHTML($this->form->getForm());
        $this->addHTML('</div>');
    }
    
    public function beforeReturningView() {
        $this->buildView();
    }
}