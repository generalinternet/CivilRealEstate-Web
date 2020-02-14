<?php
/**
 * Description of AbstractGroupPaymentRefundFormView
 *
 * @author General Internet
 * @copyright  2018 General Internet
 * @version    3.0.0
 */
abstract class AbstractGroupPaymentRefundFormView extends AbstractGroupPaymentFormView {
    
    protected $invoice = NULL;
    
    public function setInvoice(AbstractInvoice $invoice) {
        $this->invoice = $invoice;
    }

    public function buildForm() {
        if (!empty($this->invoice)) {
            $this->form->addField('invoice_id', 'hidden', array(
                'value'=>$this->invoice->getProperty('id'),
            ));
        }
        $this->buildHeader();
        $this->form->addHTML('<div id="refund_body">');
        $this->buildTopFields();
        $this->buildBottomFields();
        $this->buildMemoAndUploaderFields();
        $this->form->addHTML('</div>');
        $this->addSubmitBtn();
        $this->formBuilt = true;
    }

    protected function buildHeader() {
        $string = '<h1>';
        if (empty($this->groupPayment->getProperty('id'))) {
            $string .= 'Add';
        } else {
            $string .= 'Edit';
        }
        $string .= ' Refund';
        if (!empty($this->invoice)) {
          //  $invoiceViewURL = $this->invoice->getViewURL();
            $string .= ' <span class="thin">for ' . $this->invoice->getTypeTitle() . ' ' . $this->invoice->getProperty('invoice_number') . '</span>';
        }
        $string .= '</h1>';
        $this->form->addHTML($string);
    }

    protected function addPaymentTypeField() {
        $this->form->addField('group_payment_type', 'hidden', array(
            'value' => $this->groupPayment->getTypeRef()
        ));
    }

    protected function addCurrencyField() {
        $currencyId = $this->groupPayment->getProperty('currency_id');
        $this->form->addDefaultCurrencyField($currencyId, 'currency_id');
        $currency = CurrencyFactory::getModelById($currencyId);
        $this->form->addHTML('<h3 class="currency_field">'.$currency->getProperty('name').'</h3>');
    }

    protected function buildTopFields() {
        $this->form->addHTML('<div class="columns thirds">');
        $this->form->addHTML('<div class="column">');
        $this->addPaymentTypeField();
        $this->addTransactionNumberField();
        $this->form->addHTML('</div>');
        $this->form->addHTML('<div class="column">');
        
        $this->form->addHTML('</div>');
        $this->form->addHTML('<div class="column">');
        $this->addDateField();
        $this->form->addHTML('</div>');
        $this->form->addHTML('</div>');
    }

    protected function buildBottomFields() {
        $this->form->addHTML('<div class="columns thirds">');
        $this->form->addHTML('<div class="column two_thirds label_column">');
        $this->addFromContactField();
        $this->form->addHTML('</div>');
        $this->form->addHTML('<div class="column single_column">');
        $this->addPaymentAmountField();
        $this->form->addHTML('<div class="right_currency_name">');
        $this->addCurrencyField();
        $this->form->addHTML('</div>');
        $this->form->addHTML('</div>');
        $this->form->addHTML('</div>');
    }

    protected function addFromContactField($displayName = '') {
        $this->form->addField('contact_id', 'hidden', array(
            'value' => $this->groupPayment->getProperty('contact_id')
        ));
        $contact = ContactFactory::getModelById($this->groupPayment->getProperty('contact_id'));
        if (!empty($contact)) {
            $this->form->addHTML('<label class="main">Pay To:</label> ');
            $this->form->addHTML('<h3 class="pay_to_field">'.$contact->getName().'</span></h3>');
        }
    }

    protected function buildMemoAndUploaderFields() {
        $this->form->addHTML('<div class="columns thirds">')
                ->addHTML('<div class="column two_thirds">');
        $this->buildMemoField();
        $this->form->addHTML('</div>')
                ->addHTML('<div class="column">');
        $this->buildTaxFields();
        $this->form->addHTML('</div>')
                ->addHTML('</div>');
    }

    protected function buildTaxFields() {
        $taxRegions = $this->invoice->getTaxRegions();
        $options = array();
        foreach ($taxRegions as $taxRegion) {
            $taxRegionId = $taxRegion->getProperty('id');
            $taxTitle = $taxRegion->getTaxTitle();
            $options[$taxRegionId] = $taxTitle;
        }
        $this->form->addField('tax_region_ids', 'checkbox', array(
            'displayName'=>'Included Taxes',
            'options'=>$options,
            'value'=>array(),
        ));
    }

}
