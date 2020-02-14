<?php
/**
 * Description of AbstractGroupPaymentFormView  
 *
 * @author General Internet
 * @copyright  2018 General Internet
 * @version    3.0.0
 */
abstract class AbstractGroupPaymentFormView extends GI_View {
    
    protected $groupPayment;
    protected $form;
    protected $examplePayment;
    /** @var AbstractPayment[] */
    protected $payments = array();
    protected $formBuilt = false;
    protected $uploader = NULL;
    protected $gpTypeLocked = false;
    
    public function __construct(GI_Form $form, AbstractGroupPayment $groupPayment, AbstractPayment $examplePayment) {
        parent::__construct();
        $this->groupPayment = $groupPayment;
        $this->form = $form;
        $this->examplePayment = $examplePayment;
        $this->addCSS('framework/modules/Accounting/' . MODULE_ACCOUNTING_VER . '/resources/accounting.css');
        $this->addJS('framework/modules/Accounting/' . MODULE_ACCOUNTING_VER . '/resources/accounting.js');
    }
    /** @param AbstractPayment[] $payments */
    public function setPayments($payments) {
        $this->payments = $payments;
    }
    
    public function setUploader(AbstractGI_Uploader $uploader) {
        $this->uploader = $uploader;
    }
    
    public function setGPTypeLocked($gpTypeLocked = false) {
        $this->gpTypeLocked = $gpTypeLocked;
    }
    
    public function buildForm() {
        $this->buildHeader();
        $this->buildTopFields();
        $this->buildBottomFields();
        $this->buildMemoAndUploaderFields();
        $this->form->addHTML('<hr />');
        $this->addPaymentsSection();
        $this->addSubmitBtn();
        $this->formBuilt = true;
    }
    
    protected function buildHeader() {
        if (empty($this->groupPayment->getProperty('id'))) {
            $verb = 'Add';
        } else {
            $verb = 'Edit';
        }
        $this->form->addHTML('<h1>'.$verb . ' ' .$this->examplePayment->getGroupPaymentFormTitle(false).'</h1>');
    }

    protected function buildTopFields() {
        $this->form->addHTML('<div class="columns thirds">');
        $this->form->addHTML('<div class="column">');
        $this->addPaymentTypeField();
        $this->form->addHTML('</div>');
        $this->form->addHTML('<div class="column">');
        $this->addPaymentAmountField();
        $this->form->addHTML('</div>');
        $this->form->addHTML('<div class="column">');
        $this->addFromContactField();
        $this->form->addHTML('</div>');
        $this->form->addHTML('</div>');
    }

    protected function addPaymentTypeField() {
        $types = GroupPaymentFactory::getTypesArray();
        if (isset($types['credit'])) {
            unset($types['credit']);
        }
        if (isset($types['imported'])) {
            unset($types['imported']);
        }
        $this->form->addField('group_payment_type', 'dropdown', array(
            'options' => $types,
            'value' => $this->groupPayment->getTypeRef(),
            'displayName' => 'Payment Type',
            'required' => true,
            'readOnly'=>$this->gpTypeLocked,
        ));
    }

    protected function addPaymentAmountField($displayName = 'Amount') {
        $this->form->addField('payment_amount', 'money', array(
            'value' => $this->groupPayment->getAmount(),
            'displayName' => $displayName,
            'required' => true
        ));
    }

    protected function addFromContactField($displayName = '') {
        $contactAutoCompURL = GI_URLUtils::buildURL(array(
            'controller' => 'contact',
            'action' => 'autocompContact',
            'type' => 'org,ind',
            'ajax' => 1
        ));
        if (empty($displayName)) {
            $displayName = $this->examplePayment->getGroupPaymentFormContactFieldDisplayName();
        }
        $this->form->addField('contact_id', 'autocomplete', array(
            'displayName' => $displayName,
            'placeHolder' => 'start typing a contact’s name',
            'autocompURL' => $contactAutoCompURL,
            'value' => $this->groupPayment->getProperty('contact_id'),
            'required' => true,
            'hideDescOnError' => false
        ));
    }

    protected function buildBottomFields() {
        $this->form->addHTML('<div class="columns thirds">');
        $this->form->addHTML('<div class="column">');
        $this->addDateField();
        $this->form->addHTML('</div>');
        $this->form->addHTML('<div class="column">');
        $this->addTransactionNumberField();
        $this->form->addHTML('</div>');
        $this->form->addHTML('<div class="column">');
        $this->addCurrencyField();
        $this->form->addHTML('</div>');
        $this->form->addHTML('</div>');
    }

    protected function addDateField($displayName = 'Date') {
        $this->form->addField('payment_date', 'date', array(
            'value' => $this->groupPayment->getProperty('date'),
            'required' => true,
            'displayName' => $displayName,
        ));
    }

    protected function addTransactionNumberField() {
        $this->form->addField('transaction_number', 'text', array(
            'value' => $this->groupPayment->getProperty('transaction_number'),
            'displayName' => 'Transaction Number',
            'required' => true
        ));
    }

    protected function addCurrencyField() {
        $currencyId = $this->groupPayment->getProperty('currency_id');
        if (ProjectConfig::getHasMultipleCurrencies()) {
            $this->form->addField('dd_currency_id', 'dropdown', array(
                'displayName' => 'Currency',
                'options' => CurrencyFactory::getOptionsArray('name'),
                'value' => $currencyId,
                'hideNull' => true,
            ));
        }
        $this->form->addDefaultCurrencyField($currencyId, 'currency_id');
    }

    protected function buildMemoAndUploaderFields() {
        $this->form->addHTML('<div class="columns halves">')
                ->addHTML('<div class="column">');
        $this->buildMemoField();
        $this->form->addHTML('</div>')
                ->addHTML('<div class="column">');
        $this->buildUploader();
        $this->form->addHTML('</div>')
                ->addHTML('</div>');
    }
    
    protected function buildMemoField() {
        $this->form->addField('memo', 'textarea', array(
            'displayName'=>'Memo',
            'value'=>$this->groupPayment->getProperty('memo'),
        ));
    }
    
    protected function buildUploader() {
        if (!empty($this->uploader)) {
            $this->form->addHTML($this->uploader->getHTMLView());
        }
    }

    protected function addPaymentsSection() {
        $this->form->addHTML('<div id="apply_to">');
        $paymentTypeTitle = $this->examplePayment->getGroupPaymentFormPaymentTypeTitle();
        $this->form->addHTML('<h3>Apply to '.$paymentTypeTitle.'</h3>');
        $this->form->addHTML('<div class="form_rows_group">');
        $this->form->addHTML('<div id="payment_lines" class="form_rows labels_on_first_row">');
        $this->addPayments();
        $this->form->addHTML('</div>');
        $this->form->addHTML('<div class="wrap_btns">');
        $this->addAddPaymentBtn();
        $this->form->addHTML('</div>');
        $this->form->addHTML('<br>');
        $this->form->addHTML('</div>');
        $this->addTotalsTable();
        $this->form->addHTML('</div>');
    }

    protected function addPayments() {
        $formWasSubmitted = $this->form->wasSubmitted();
        $seqCount = 0;
        $payments = $this->groupPayment->getPayments($this->form);
        foreach ($payments as $payment) {
            if (!$formWasSubmitted) {
                $payment->setFieldSuffix($seqCount);
                $seqCount++;
            }
            $formView = $payment->getFormView($this->form);
            $formView->buildForm();
        }
    }

    protected function addAddPaymentBtn() {
        $addURL = GI_URLUtils::buildURL(array(
                    'controller' => 'accounting',
                    'action' => 'addPaymentLine',
                        ), false, true);
        $this->form->addHTML('<span class="custom_btn add_form_row" id="add_payment_line" data-add-to="payment_lines" data-add-type="'.$this->examplePayment->getTypeRef().'" data-add-url="' . $addURL . '"><span class="icon_wrap"><span class="icon plus"></span></span><span class="btn_text">'.$this->examplePayment->getGroupPaymentFormPaymentTypeTitle(false).'</span></span>');
        
    }

    protected function addSubmitBtn() {
        $this->form->addHTML('<span class="submit_btn" title="Submit" tabindex="0">Submit</span>');
    }

    protected function addTotalsTable() {
        $this->form->addHTML('<div class="columns thirds">')
        ->addHTML('<div class="column two_thirds">')
        ->addHTML('</div>')
        ->addHTML('<div class="column">')
        ->addHTML('<table class="ui_table">')
        ->addHTML('<tr>')
        ->addHTML('<th>Payment Amount</th>')
        ->addHTML('<td id="total_payment_amount"></td>')    
        ->addHTML('</tr>')
        ->addHTML('<tr>')
        ->addHTML('<th>Amount to Apply</th>')
        ->addHTML('<td id="total_apply_amount" data-value="0"></td>') //TODO - set data-value to amount already applied
        ->addHTML('</tr>')
        ->addHTML('<tr>')
        ->addHTML('<th>Remaining Balance</th>')
        ->addHTML('<td id="total_remaining_balance" data-value="0"></td>') //TODO - set data-value to existing balance
        ->addHTML('</tr>')
        ->addHTML('</table>')
        ->addHTML('</div>')
        ->addHTML('</div>');
    }
    
    protected function openViewWrap() {
        $this->addHTML('<div class="content_padding">');
    }
    
    protected function closeViewWrap() {
        $this->addHTML('</div>');
    }

    public function beforeReturningView() {
        $this->openViewWrap();
        if (!$this->formBuilt) {
            $this->buildForm();
        }
        $this->addHTML($this->form->getForm(''));
        $this->closeViewWrap();
    }

}