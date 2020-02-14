<?php

/**
 * Description of AbstractApplyGroupPaymentToBillsFormView
 *
 * @author General Internet
 * @copyright  2018 General Internet
 * @version    3.0.0
 */
abstract class AbstractApplyGroupPaymentToBillsFormView extends GI_View {

    protected $form;
    protected $groupPayment;
    protected $bills;
    protected $totalAppliedAmount = 0;
    protected $billValues = array();
    protected $title;
    protected $groupPaymentFieldsReadOnly = false;
    protected $gpTotal = 0;
    protected $gpPrevApplied = 0;
    protected $gpPrevBalance = 0;
    protected $cancelURL = NULL;

    public function __construct(GI_Form $form, AbstractGroupPayment $groupPayment, $bills) {
        parent::__construct();
        $this->form = $form;
        $this->groupPayment = $groupPayment;
        $this->bills = $bills;
        $typeTitle = $bills[0]->getTypeTitle();
        if (!empty($groupPayment->getProperty('id'))) {
            $this->groupPaymentFieldsReadOnly = true;
        }
        $transactionNumber = $groupPayment->getProperty('transaction_number');
        if (!empty($transactionNumber)) {
            $transactionNumber = '#' . $transactionNumber;
        }
        if (count($bills) > 1) {
            $this->title = 'Apply Payment ' . $transactionNumber . ' to ' . $typeTitle . 's';
        } else {
            $this->title = 'Apply Payment ' . $transactionNumber . ' to ' . $typeTitle . ' #' . $this->bills[0]->getProperty('bill_number');
        }
        $this->calculateValues();
        $this->setGroupPaymentValues();
        //  $this->buildForm();
        $this->addJS('framework/modules/Accounting/' . MODULE_ACCOUNTING_VER . '/resources/accounting.js');
    }

    public function setCancelURL($cancelURL) {
        $this->cancelURL = $cancelURL;
    }

    protected function calculateValues() {
        foreach ($this->bills as $bill) {
            $billId = $bill->getProperty('id');
            $billNumber = $bill->getProperty('bill_number');
            $billTotal = $bill->getTotal(true);
            $billBalance = $bill->getBalance(false);
            $this->totalAppliedAmount += $billBalance;
            $billBalanceDisplay = '$' . GI_StringUtils::formatMoney($billBalance);
            if ($billBalance > 0) {
                $amountToApply = $billBalance;
            } else {
                $amountToApply = 0;
            }
            $valuesArray = array(
                'number' => $billNumber,
                'total' => $billTotal,
                'balance' => $billBalance,
                'balance_display' => $billBalanceDisplay
            );
            $this->billValues[$billId] = $valuesArray;
        }
    }

    protected function setGroupPaymentValues() {
        if (empty($this->groupPayment->getProperty('id'))) {
            $this->groupPayment->setProperty('amount', $this->totalAppliedAmount);
            $this->groupPayment->setProperty('date', GI_Time::getDate());
        }
        $this->gpTotal = $this->groupPayment->getAmount();
        $this->gpPrevApplied = $this->groupPayment->getAppliedAmount();
        $this->gpPrevBalance = $this->groupPayment->getBalance();
    }

    public function buildForm() {
        $this->form->addHTML('<div class="columns thirds">');
        $this->form->addHTML('<div class="column">');
        $this->addPaymentTypeField();
        $this->addPaymentDateField();
        $this->form->addHTML('</div>');
        $this->form->addHTML('<div class="column">');
        $this->addPaymentAmountField();
        $this->addTransactionNumberField();
        $this->form->addHTML('</div>');
        $this->form->addHTML('<div class="column">');
        $this->addToContactField();
        $this->addCurrencyField();
        $this->form->addHTML('</div>');
        $this->form->addHTML('</div>');
        if (!empty($this->groupPayment->getProperty('id'))) {
            $this->buildGroupPaymentTable();
        }
        $this->buildTable();
        $this->addButtons();
    }

    protected function addPaymentTypeField() {
        $this->form->addField('group_payment_type_ref', 'dropdown', array(
            'displayName' => 'Payment Type',
            'required' => true,
            'options' => GroupPaymentFactory::getTypesArray(),
            'readOnly' => $this->groupPaymentFieldsReadOnly,
            'value' => $this->groupPayment->getTypeRef(),
        ));
    }

    protected function addPaymentAmountField() {
        $this->form->addField('payment_amount', 'money', array(
            'displayName' => 'Payment Amount',
            'required' => true,
            'value' => GI_StringUtils::formatMoney($this->groupPayment->getProperty('amount'), false),
            'formElementClass' => 'payment_total',
            'readOnly' => $this->groupPaymentFieldsReadOnly
        ));
        $this->form->addField('previous_balance', 'hidden', array(
            'value' => $this->gpPrevBalance,
        ));
        $this->form->addField('previously_applied', 'hidden', array(
            'value' => $this->gpPrevApplied,
        ));
    }

    protected function addToContactField() {
        $contactAutoCompURL = GI_URLUtils::buildURL(array(
            'controller' => 'contact',
            'action' => 'autocompContact',
            'type' => 'org,ind',
            'ajax' => 1
        ));
        $this->form->addField('contact_id', 'autocomplete', array(
            'displayName' => 'Pay To Contact',
            'placeHolder' => 'start typing a contact’s name',
            'autocompURL' => $contactAutoCompURL,
            'value' => $this->groupPayment->getProperty('contact_id'),
            'required' => true,
            'hideDescOnError' => false,
            'readOnly' => $this->groupPaymentFieldsReadOnly
        ));
    }

    protected function addPaymentDateField() {
        $this->form->addField('payment_date', 'date', array(
            'displayName' => 'Payment Date',
            'required' => true,
            'value' => $this->groupPayment->getProperty('date'),
            'fieldClass' => 'autofocus_off',
            'readOnly' => $this->groupPaymentFieldsReadOnly
        ));
    }

    protected function addTransactionNumberField() {
        $this->form->addField('transaction_number', 'text', array(
            'displayName' => 'Transaction Number',
            'required' => true,
            'readOnly' => $this->groupPaymentFieldsReadOnly,
            'value' => $this->groupPayment->getProperty('transaction_number')
        ));
    }

    protected function addCurrencyField() {
        $currencyId = $this->groupPayment->getProperty('currency_id');
        if (!empty($currencyId)) {
            $currencyLocked = true;
        } else if ($this->groupPaymentFieldsReadOnly) {
            $currencyLocked = true;
        } else {
            $currencyLocked = false;
        }
        if (ProjectConfig::getHasMultipleCurrencies()) {
            $this->form->addField('currency_id', 'dropdown', array(
                'displayName' => 'Currency',
                'required' => true,
                'options' => CurrencyFactory::getOptionsArray('name'),
                'value' => $currencyId,
                'readOnly' => $currencyLocked
            ));
        } else {
            $this->form->addDefaultCurrencyField($currencyId, 'currency_id');
        }
    }

    protected function addButtons() {
        $this->form->addHTML('<div class="center_btns wrap_btns">');
        $this->form->addHTML('<span class="submit_btn" tabindex="0" >Apply Payment</span>');
        if (!empty($this->cancelURL)) {
            $this->form->addHTML('<a href="' . $this->cancelURL . '"><span class="other_btn gray" >Cancel</span></a>');
        }
        $this->form->addHTML('</div>');
    }

    protected function buildHeader() {
        $this->form->addHTML('<thead>')
                ->addHTML('<tr>')
                ->addHTML('<th>Bill Number</th>')
                ->addHTML('<th>Amount</th>')
                ->addHTML('<th>Current Balance</th>')
                ->addHTML('<th>Amount to Apply</th>')
                ->addHTML('</tr>')
                ->addHTML('</thead>');
    }

    protected function buildFooter() {
        $colSpan = 2;
        $this->form->addHTML('<tfoot>')
                ->addHTML('<tr class="table_total payment_to_apply_total">')
                ->addHTML('<td class="empty" colspan="' . $colSpan . '"></td>')
                ->addHTML('<th>Total</th>')
                ->addHTML('<td class="value">$'. GI_StringUtils::formatMoney($this->totalAppliedAmount).'</td>')
                ->addHTML('</tr>')
                
                ->addHTML('<tr class="payment_balance">')
                ->addHTML('<td class="empty" colspan="' . $colSpan . '"></td>')
                ->addHTML('<th>Payment Balance</th>')
                ->addHTML('<td class="value">$0.00</td>')
                ->addHTML('</tr>')
                ->addHTML('</tfoot>');
    }
    
    protected function buildGroupPaymentTable() {
        $this->form->addHTML('<div class="columns thirds">')
                ->addHTML('<div class="column two_thirds">')
                ->addHTML('</div>')
                ->addHTML('<div class="column">');
        $this->form->addHTML('<table class="ui_table">');
        $this->form->addHTML('<tr>');
        $this->form->addHTML('<th>Payment Total</th>');
        $this->form->addHTML('<td>');
        $this->form->addHTML($this->groupPayment->formatAmountForDisplay($this->gpTotal));
        $this->form->addHTML('</td>');
        $this->form->addHTML('</tr>');
        $this->form->addHTML('<tr>');
        $this->form->addHTML('<th>Previously Applied</th>');
        $this->form->addHTML('<td>');
        $this->form->addHTML($this->groupPayment->formatAmountForDisplay($this->gpPrevApplied));
        $this->form->addHTML('</td>');
        $this->form->addHTML('</tr>');
        $this->form->addHTML('<tr>');
        $this->form->addHTML('<th>Previous Balance</th>');
        $this->form->addHTML('<td>');
        $this->form->addHTML($this->groupPayment->formatAmountForDisplay($this->gpPrevBalance));
        $this->form->addHTML('</td>');
        $this->form->addHTML('</tr>');
        $this->form->addHTML('</table>');
        $this->form->addHTML('</div>')
                ->addHTML('</div>');
    }

    protected function buildTable() {
        $this->form->addHTML('<table class="ui_table form_table" id="to_apply_table">');
        $this->buildHeader();
        $this->form->addHTML('<tbody>');
        foreach ($this->bills as $bill) {
            $billId = $bill->getProperty('id');
            $valuesArray = $this->billValues[$billId];
            $billNumber = $valuesArray['number'];
            $billTotal = $valuesArray['total'];
            $billBalanceDisplay = $valuesArray['balance_display'];
            $billBalance = $valuesArray['balance'];
            $this->form->addHTML('<tr>');
            $this->form->addHTML('<td>#'.$billNumber.'</td>');
            $this->form->addHTML('<td>'.$billTotal.'</td>');
            $this->form->addHTML('<td>'.$billBalanceDisplay.'</td>');
            $this->form->addHTML('<td class="payment_to_apply">');
            if ($billBalance > 0) {
                $amountToApply = $billBalance;
            } else {
                $amountToApply = 0;
            }
            $this->form->addField('applied_amount_' . $billId, 'money', array(
                'required'=>true,
                'showLabel'=>false,
                'value'=>  GI_StringUtils::formatMoney($amountToApply, false),
            ));
            $this->form->addHTML('</td>');
            $this->form->addHTML('</tr>');
        }
        $this->form->addHTML('</tbody>');
        $this->buildFooter();
        $this->form->addHTML('</table>');
    }

    protected function openViewWrap(){
        $this->addHTML('<div class="content_padding">');
        return $this;
    }
    
    protected function closeViewWrap(){
        $this->addHTML('</div>');
        return $this;
    }
    
    protected function buildView() {
        $this->openViewWrap();
        $this->addHTML('<h1>'.$this->title .'</h1>');
        $this->addHTML($this->form->getForm());
        $this->closeViewWrap();
    }

    public function beforeReturningView() {
        $this->buildView();
    }
    
}
