<?php
/**
 * Description of AbstractPaymentExpenseFormView
 *
 * @author General Internet
 * @copyright  2018 General Internet
 * @version    3.0.0
 */
abstract class AbstractPaymentExpenseFormView extends AbstractPaymentFormView {

    public function __construct(\GI_Form $form, AbstractPaymentExpense $payment) {
        parent::__construct($form, $payment);
    }

    protected function addAutocompIdField() {
        $bill = $this->payment->getBill();
        if (empty($bill)) {
            $val = '';
        } else {
            $val = $bill->getProperty('id');
        }
        $billAutoCompURL = GI_URLUtils::buildURL(array(
                    'controller' => 'autocomplete',
                    'action' => 'bill',
                    'type' => 'bill',
                    'ajax' => 1,
                    'withBalanceOnly'=>'1',
        ), false, true);
        $this->form->addField($this->getFieldName('bill_id'), 'autocomplete', array(
            'displayName' => 'Bill',
            'placeHolder' => 'start typing a bill number or amount',
            'autocompURL' => $billAutoCompURL,
            'value' => $val,
            'required' => true,
            'hideDescOnError' => false,
            'fieldClass'=>'applicable_ac',
            'autocompMinLength'=>0,
        ));
    }

}
