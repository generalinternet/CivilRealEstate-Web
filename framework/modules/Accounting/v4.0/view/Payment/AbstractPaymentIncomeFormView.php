<?php
/**
 * Description of AbstractPaymentIncomeFormView
 *
 * @author General Internet
 * @copyright  2018 General Internet
 * @version    3.0.0
 */
abstract class AbstractPaymentIncomeFormView extends AbstractPaymentFormView {

    public function __construct(\GI_Form $form, AbstractPaymentIncome $payment) {
        parent::__construct($form, $payment);
    }

    protected function addAutocompIdField() {
        $invoice = $this->payment->getInvoice();
        if (empty($invoice)) {
            $val = '';
        } else {
            $val = $invoice->getProperty('id');
        }
        $invoiceAutoCompURL = GI_URLUtils::buildURL(array(
                    'controller' => 'autocomplete',
                    'action' => 'invoice',
                    'type' => 'invoice',
                    'ajax' => 1
                        ), false, true);
        $this->form->addField($this->getFieldName('invoice_id'), 'autocomplete', array(
            'displayName' => 'Invoice',
            'placeHolder' => 'start typing an invoice number or amount',
            'autocompURL' => $invoiceAutoCompURL,
            'value' => $val,
            'required' => true,
            'hideDescOnError' => false,
            'fieldClass' => 'applicable_ac'
        ));
    }

}
