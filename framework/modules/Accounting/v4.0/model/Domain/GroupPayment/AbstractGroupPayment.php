<?php
/**
 * Description of AbstractGroupPayment
 *
 * @author General Internet
 * @copyright  2018 General Internet
 * @version    3.0.0
 */
abstract class AbstractGroupPayment extends AbstractAccountingElement {

    /**  @var AbstractPayment[] */
    protected $payments = NULL;

    /** @var Contact */
    protected $contact = NULL;

    /** @var Currency */
    protected $currency = NULL;
    protected $uploaderEnabled = true;
    protected $tableWrapId = 'group_payment_table';
    protected static $searchFormId = 'group_payment_search';

    /** @return string */
    public function getTableWrapId() {
        return $this->tableWrapId;
    }

    /** @return string */
    public static function getSearchFormId() {
        return static::$searchFormId;
    }

    /** @return Currency */
    public function getCurrency() {
        if (empty($this->currency)) {
            $currencyId = $this->getProperty('currency_id');
            $currency = CurrencyFactory::getModelById($currencyId);
            if (!empty($currency)) {
                $this->currency = $currency;
            }
        }
        return $this->currency;
    }
    
    public function getCurrencyRef(){
        $currency = $this->getCurrency();
        if(!empty($currency)){
            return $currency->getProperty('ref');
        }
        return NULL;
    }
    
    public function formatAmountForDisplay($amount, $showCurrency = false){
        $currency = $this->getCurrency();
        $total = $currency->getProperty('symbol') . GI_StringUtils::formatMoney($amount);
        if($showCurrency && !GI_CSV::csvExporting()){
            $total .= ' (' . $currency->getProperty('name') . ')';
        }
        return $total;
    }
    
    /** @return AbstractContact */
    public function getContact() {
        if (empty($this->contact)) {
            $this->contact = ContactFactory::getModelById($this->getProperty('contact_id'));
        }
        return $this->contact;
    }
    
    public function getDetailView() {
        $view = new GroupPaymentDetailView($this);
        $uploader = $this->getUploader();
        $view->setUploader($uploader);
        return $view;
    }

    public function getVoidURL() {
        return GI_URLUtils::buildURL(array(
            'controller'=>'accounting',
            'action'=>'voidGroupPayment',
            'id'=>$this->getProperty('id')
        ));
    }

    public function getBalance($formatForDisplay = false, $showCurrency = false) {
        $paymentsSum = GroupPaymentFactory::getAppliedPaymentSumByGroupPayment($this);
        $amount = $this->getProperty('amount');
        $balance = $amount - $paymentsSum;
        if ($formatForDisplay) {
            $formattedAmount = $this->formatAmountForDisplay($balance, $showCurrency);
            if ($this->getIsVoid()) {
                $formattedAmount = '<del class="void">' . $formattedAmount . '</del>';
            }
            return $formattedAmount;
        }
        return $balance;
    }
    
    public function getAppliedAmount($formatForDisplay = false, $showCurrency = false) {
        $paymentsSum = GroupPaymentFactory::getAppliedPaymentSumByGroupPayment($this);
        if ($formatForDisplay) {
            return $this->formatAmountForDisplay($paymentsSum, $showCurrency);
        }
        return $paymentsSum;
    }

    public function getEditURL() {
        return GI_URLUtils::buildURL(array(
                    'controller' => 'accounting',
            'action'=>'editPayment',
            'id'=>$this->getProperty('id')
        ));
    }
    
    public function getViewURLAttrs() {
        return array(
            'controller'=>'accounting',
            'action'=>'viewPayment',
            'id'=>$this->getProperty('id')
        );
    }
    
    public function getIsEditable() {
        if ($this->getIsVoidOrCancelled() || $this->getIsLocked() || !Permission::verifyByRef('edit_payments')) {
            return false;
        }
        return true;
    }
    
    public function getIsLocked() {
        return AccountingReportFactory::isGroupPaymentPartOfLockedReport($this);
    }
    
    public function getIndexTitle($plural = false) {
        $title = 'Payment';
        if ($plural) {
            $title .= 's';
        }
        return $title;
    }
    
    public function getAPtabRef() {
        return 'payments';
    }
    
    public function getARTabRef() {
        return 'payments';
    }

    /**
     * @param GI_Form $form
     * @param AbstractPayment $payment
     * @return \AccountingGroupPaymentFormView
     */
    public function getFormView(GI_Form $form, AbstractPayment $payment) {
        $view = new AccountingGroupPaymentFormView($form, $this, $payment);
        $uploader = $this->getUploader($form);
        $view->setUploader($uploader);
        return $view;
    }

    public function handleApplyPaymentToInvoicesFormSubmission(GI_Form $form, $invoices) {
        if ($form->wasSubmitted() && $form->validate()) {
            if (!$this->setPropertiesFromApplyToInvoicesForm($form)) {
                return false;
            }
            $paymentDate = $this->getProperty('applicable_date');
            $total = $this->getProperty('amount');
            $invoiceData = array();
            $sumOfAmountToApply = 0;
            foreach ($invoices as $invoice) {
                $amountToApply = filter_input(INPUT_POST, 'applied_amount_' . $invoice->getProperty('id'));
                $income = $invoice->getIncome();
                if (empty($income)) {
                    return NULL;
                }
                $invoiceData[$invoice->getProperty('id')] = array(
                    'invoice'=>$invoice,
                    'amount'=>$amountToApply,
                    'income'=>$income,
                );
                $sumOfAmountToApply += $amountToApply;
            }
            
            if (!GI_Math::floatEquals($sumOfAmountToApply, $total) && $sumOfAmountToApply > $total) {
                $form->addFieldError('payment_amount', '', 'The total applied amount exceeds the amount of the payment');
                return NULL;
            }
            $sortableBalance = $total - $sumOfAmountToApply;
            $this->setProperty('group_payment.sortable_balance', $sortableBalance);
            $this->setProperty('group_payment.default_payment_type_ref', 'income');
            if (!$this->save()) {
                return NULL;
            }
            $groupPaymentId = $this->getProperty('id');
            
            foreach ($invoiceData as $invoiceId => $valueArray) {
                $invoice = $valueArray['invoice'];
                $income = $valueArray['income'];
                $incomeId = $income->getProperty('id');
                $amountToApply = $valueArray['amount'];
                $payment = PaymentFactory::buildNewModel('income');
                $payment->setProperty('payment_income.income_id', $incomeId);
                $payment->setProperty('payment.amount', $amountToApply);
                $payment->setProperty('payment.date', $paymentDate);
                $payment->setProperty('payment.applicable_date', $paymentDate);
                $payment->setProperty('payment.group_payment_id', $groupPaymentId);
                if (!($payment->save() && $income->save())) {
                    return NULL;
                }
            }
            return $this;
        }
        return NULL;
    }

    public function handleApplyPaymentToBillsFormSubmission(GI_Form $form, $bills) {
        if ($form->wasSubmitted() && $form->validate()) {
            if (!$this->setPropertiesFromApplyToBillsForm($form)) {
                return false;
            }
            $paymentDate = $this->getProperty('applicable_date');
            $total = $this->getProperty('amount');
            $sumOfAmountToApply = 0;
            $billData = array();

            foreach ($bills as $bill) {
                $amountToApply = filter_input(INPUT_POST, 'applied_amount_' . $bill->getProperty('id'));
                $expense = $bill->getExpense();
                if (empty($expense)) {
                    return NULL;
                }
                $billData[$bill->getProperty('id')] = array(
                    'bill' => $bill,
                    'amount' => $amountToApply,
                    'expense' => $expense,
                );
                $sumOfAmountToApply += $amountToApply;
            }

            if (!GI_Math::floatEquals($sumOfAmountToApply, $total) && $sumOfAmountToApply > $total) {
                $form->addFieldError('payment_amount', '', 'The total applied amount exceeds the amount of the payment');
                return NULL;
            }
            $sortableBalance = $total - $sumOfAmountToApply;
            $this->setProperty('group_payment.sortable_balance', $sortableBalance);
            $this->setProperty('group_payment.default_payment_type_ref', 'expense');
            if (!$this->save()) {
                return NULL;
            }
            $groupPaymentId = $this->getProperty('id');
            foreach ($billData as $billId => $valueArray) {
                $bill = $valueArray['bill'];
                $expense = $valueArray['expense'];
                $expenseId = $expense->getProperty('id');
                $amountToApply = $valueArray['amount'];
                $payment = PaymentFactory::buildNewModel('expense');
                $payment->setProperty('payment_expense.expense_id', $expenseId);
                $payment->setProperty('payment.amount', $amountToApply);
                $payment->setProperty('payment.date', $paymentDate);
                $payment->setProperty('payment.applicable_date', $paymentDate);
                $payment->setProperty('payment.group_payment_id', $groupPaymentId);
                if (!($payment->save() && $expense->save())) {
                    return NULL;
                }
            }
            return $this;
        }
        return NULL;
    }
    
    public function handleFormSubmission(GI_Form $form, AbstractPayment $examplePayment) {
        if ($form->wasSubmitted() && $form->validate()) {
            $groupPaymentTypeRef = filter_input(INPUT_POST, 'group_payment_type');
            if ($groupPaymentTypeRef !== $this->getTypeRef()) {
                if (empty($this->getProperty('id'))) {
                    $updatedGroupPayment = GroupPaymentFactory::buildNewModel($groupPaymentTypeRef);
                } else {
                    $updatedGroupPayment = GroupPaymentFactory::changeModelType($this, $groupPaymentTypeRef);
                }
            } else {
                $updatedGroupPayment = $this;
            }
            if (!$updatedGroupPayment->setPropertiesFromForm($form)) {
                return false;
            }
            $updatedGroupPayment->setProperty('default_payment_type_ref', $examplePayment->getTypeRef());
            
            $uploader = $this->getUploader($form);
            if ($updatedGroupPayment->save()) {
                if (!$this->handlePaymentLineFormSubmission($form)) {
                    return NULL;
                }
                if ($updatedGroupPayment->save()) {
                    if (!empty($uploader)) {
                        $targetFolder = $updatedGroupPayment->getFolder();
                        $uploader->setTargetFolder($targetFolder);
                        $uploader->putUploadedFilesInTargetFolder();
                    }
                    return $updatedGroupPayment;
                }
            }
        }
        return NULL;
    }

    protected function setPropertiesFromForm(GI_Form $form) {
        if ($form->wasSubmitted() && $this->validateForm($form)) {
            $paymentAmount = filter_input(INPUT_POST, 'payment_amount');
            $contactId = filter_input(INPUT_POST, 'contact_id');
            $paymentDate = filter_input(INPUT_POST, 'payment_date');
            $transactionNumber = filter_input(INPUT_POST, 'transaction_number');
            $currencyId = filter_input(INPUT_POST, 'currency_id');
            $memo = filter_input(INPUT_POST, 'memo');

            $this->setProperty('date', $paymentDate);
            $this->setProperty('applicable_date', $paymentDate);
            $this->setProperty('amount', $paymentAmount);
            $this->setProperty('sortable_balance', $paymentAmount);
            $this->setProperty('currency_id', $currencyId);
            $this->setProperty('transaction_number', $transactionNumber);
            $this->setProperty('contact_id', $contactId);
            $this->setProperty('void', 0);
            $this->setProperty('cancelled', 0);
            $this->setProperty('memo', $memo);
            return true;
        }
        return false;
    }

    protected function setPropertiesFromApplyToBillsForm(GI_Form $form) {
        if ($form->wasSubmitted() && $this->validateForm($form)) {
            $paymentDate = filter_input(INPUT_POST, 'payment_date');
            $currencyId = filter_input(INPUT_POST, 'currency_id');
            $transactionNumber = filter_input(INPUT_POST, 'transaction_number');
            $total = filter_input(INPUT_POST, 'payment_amount');
            $contactId = filter_input(INPUT_POST, 'contact_id');

            $this->setProperty('group_payment.date', $paymentDate);
            $this->setProperty('group_payment.amount', $total);
            $this->setProperty('group_payment.currency_id', $currencyId);
            $this->setProperty('group_payment.transaction_number', $transactionNumber);
            $this->setProperty('group_payment.contact_id', $contactId);
            return true;
        }
        return false;
    }

    protected function setPropertiesFromApplyToInvoicesForm(GI_Form $form) {
        if ($form->wasSubmitted() && $this->validateForm($form)) {
            $paymentDate = filter_input(INPUT_POST, 'payment_date');
            $currencyId = filter_input(INPUT_POST, 'currency_id');
            $transactionNumber = filter_input(INPUT_POST, 'transaction_number');
            $total = filter_input(INPUT_POST, 'payment_amount');
            $contactId = filter_input(INPUT_POST, 'contact_id');

            $this->setProperty('date', $paymentDate);
            $this->setProperty('applicable_date', $paymentDate);
            $this->setProperty('amount', $total);
            $this->setProperty('currency_id', $currencyId);
            $this->setProperty('transaction_number', $transactionNumber);
            $this->setProperty('contact_id', $contactId);
            return true;
        }
        return false;
    }

    protected function handlePaymentLineFormSubmission(GI_Form $form) {
        if ($form->wasSubmitted() && $form->validate()) {
            $existingPayments = $this->getPayments();
            $paymentsToRemove = array();
            if (!empty($existingPayments)) {
                foreach ($existingPayments as $existingPayment) {
                    $paymentsToRemove[$existingPayment->getProperty('id')] = $existingPayment;
                }
            }
            $seqNums = filter_input(INPUT_POST, 'payments', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY);
            if (!empty($seqNums)) {
                foreach ($seqNums as $seqNum) {
                    $paymentId = filter_input(INPUT_POST, 'payment_id_' . $seqNum);
                    $typeRef = filter_input(INPUT_POST, 'payment_type_' . $seqNum);
                    if (!empty($paymentId)) {
                        if (isset($paymentsToRemove[$paymentId])) {
                            $payment = $paymentsToRemove[$paymentId];
                            unset($paymentsToRemove[$paymentId]);
                        } else {
                            $payment = PaymentFactory::changeModelType(PaymentFactory::getModelById($paymentId), $typeRef);
                        }
                    } else {
                        $payment = PaymentFactory::buildNewModel($typeRef);
                    }
                    $payment->setFieldSuffix($seqNum);
                    if (!$payment->handleFormSubmission($form, $this)) {
                        return false;
                    }
                }
            }
            if (!empty($paymentsToRemove)) {
                foreach ($paymentsToRemove as $paymentToRemove) {
                    if (!$paymentToRemove->softDelete()) {
                        return false;
                    }
                }
            }
            return true;
        }
        return false;
    }

    public function getPayments(GI_Form $form = NULL) {
        if (!empty($form) && $form->wasSubmitted()) {
            $payments = array();
            $seqNums = filter_input(INPUT_POST, 'payments', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY);
            if (!empty($seqNums)) {
                foreach ($seqNums as $seqNum) {
                    $paymentId = filter_input(INPUT_POST, 'payment_id_' . $seqNum);
                    $typeRef = filter_input(INPUT_POST, 'payment_type_' . $seqNum);
                    if (!empty($paymentId)) {
                        $payment = PaymentFactory::changeModelType(PaymentFactory::getModelById($paymentId), $typeRef);
                    } else {
                        $payment = PaymentFactory::buildNewModel($typeRef);
                    }
                    $payment->setFieldSuffix($seqNum);
                    $payments[] = $payment;
                }
            }
            return $payments;
        } else {
            if (empty($this->payments)) {
                $payments = PaymentFactory::search()
                        ->filter('group_payment_id', $this->getProperty('id'))
                        ->filter('void', 0)
                        ->filter('cancelled', 0)
                        ->select();
                $this->payments = $payments;
            }
            return $this->payments;
        }
    }

    public function updateSortableBalance() {
        $sortableBalance = $this->getBalance();
        $this->setProperty('sortable_balance', $sortableBalance);
    }

    public function save() {
        $this->updateSortableBalance();
        $applicableDate = $this->getProperty('applicable_date');
        if (empty($applicableDate)) {
            $paymentDate = $this->getProperty('date');
            if (empty($paymentDate)) {
                $date = GI_Time::getDate();
                $this->setProperty('applicable_date', $date);
            } else {
                $this->setProperty('applicable_date', $paymentDate);
            }
        }
        if (!parent::save()) {
            return false;
        }
        $defaultAccountingLocTag = ProjectConfig::getDefaultAccoutingLocationTag();
        if (empty($defaultAccountingLocTag)) {
            return false;
        }
        if (!GroupPaymentFactory::linkGroupPaymentAndTag($this, $defaultAccountingLocTag)) {
            return false;
        }
        return true;
    }

    public static function getUITableCols() {
        $tableColArrays = array(
            array(
                'header_title' => 'Transaction Number',
                'method_name' => 'getTransactionNumber',
                'cell_url_method_name'=>'getViewURL',
            ),
            array(
                'header_title' => 'Date',
                'method_name' => 'getDate',
                'method_attributes' => 'true'
            ),
            array(
              'header_title'=>'Contact',
              'method_name'=>'getContactName',
              'cell_url_method_name' => 'getContactViewURL',
            ),
            array(
                'header_title' => 'Type',
                'method_name' => 'getTypeTitle'
            ),
            array(
                'header_title' => 'Amount',
                'method_name' => 'getAmount',
                'method_attributes' => array(true, true)
            ),
            array(
                'header_title'=>'Balance',
                'method_name'=>'getBalance',
                'method_attributes'=>array(true, true)
            ),
        );
        $UITableCols = array();
        foreach ($tableColArrays as $tableColArray) {
            $UITableCols[] = UITableCol::buildUITableColFromArray($tableColArray);
        }
        return $UITableCols;
    }

    public function getDate($formatForDisplay = false) {
        $date = $this->getProperty('date');
        if ($formatForDisplay) {
            $date = GI_Time::formatDateForDisplay($date);
        }
        return $date;
    }
    
    public function getContactName() {
        $contact = $this->getContact();
        if (!empty($contact)) {
            return $contact->getName();
        }
        return '';
    }
    
    public function getContactViewURL() {
        $contact = $this->getContact();
        if (!empty($contact)) {
            return $contact->getViewURL();
        }
        return '';
    }

    public function getTransactionNumber() {
        $transactionNumber = $this->getProperty('transaction_number');
        if (empty($transactionNumber)) {
            $transactionNumber = '--';
        }
        if ($this->getIsVoid()) {
            $transactionNumber .= ' <span class="red sub_status">Void</span>';
        }
        return $transactionNumber;
    }

    public function getAmount($formatForDisplay = false, $showCurrency = false) {
        $amount = $this->getProperty('amount');
        if ($formatForDisplay) {
            $formattedAmount = $this->formatAmountForDisplay($amount, $showCurrency);
            if ($this->isVoid()) {
                $formattedAmount = '<del class="void">' . $formattedAmount . '</del>'; 
            }
            return $formattedAmount;
        }
        return $amount;
    }

    public function void($notes = '') {
        $payments = $this->getPayments();
        if (!empty($payments)) {
            foreach ($payments as $appliedPayment) {
                if (!$appliedPayment->void(false)) {
                    return false;
                }
            }
        }
        $this->setProperty('void', 1);
        $this->setProperty('removed_by_id', Login::getUserId());
        $this->setProperty('removed_date', GI_Time::getDate());
        $this->setProperty('removed_note', $notes);
        if (!$this->save()) {
            return false;
        }
        return true;
    }

    public function cancel() {
        //TODO - implement this method
    }

    public function getBreadcrumbs() {
        $breadcrumbs = array();
        $breadcrumbs[] = array(
            'label' => 'Accounting',
            'link' => ''
        );
        return $breadcrumbs;
    }

    public function getUploader(GI_Form $form = NULL) {
        if (!$this->uploaderEnabled) {
            return NULL;
        }
        if ($this->getProperty('id')) {
            $appendName = 'edit_' . $this->getProperty('id');
        } else {
            $appendName = 'add';
        }
        $uploader = GI_UploaderFactory::buildUploader('group_payment_' . $appendName);
        $folder = $this->getFolder();
        $uploader->setTargetFolder($folder);
        if (!empty($form)) {
            $uploader->setForm($form);
        }
        return $uploader;
    }

    public function getSearchFormView(GI_Form $form, $searchValues = NULL, $type = NULL) {
        return new GroupPaymentSearchFormView($form, $searchValues, $type);
    }

    public function printOutput() {
        //do nothing
    }
    
    public function addCustomFiltersToDataSearch(GI_DataSearch $dataSearch) {
        if (!Permission::verifyByRef('view_payments')) {
            $dataSearch = $this->addViewPermissionFilterToDataSearch($dataSearch);
        }
        return $dataSearch;
    }
    
    protected function addViewPermissionFilterToDataSearch(GI_DataSearch $dataSearch) {
        $dataSearch->filter('uid', Login::getUserId());
        return $dataSearch;
    }

    protected function getIsViewable() {
        if (!Permission::verifyByRef('view_payments')) {
            $search = GroupPaymentFactory::search()
                    ->filter('id', $this->getProperty('id'));
            $search = $this->addViewPermissionFilterToDataSearch($search);
            $array = $search->select();
            if (empty($array)) {
                return false;
            }
        }
        return true;
    }
    
    public function isIndexViewable() {
        return true;
    }

    public function getIsAddable() {
        if (!Permission::verifyByRef('add_payments')) {
            return false;
        }
        return true;
    }

    public function isPrintable() {
        return false;
    }

    public function getIsVoidable() {
        if (Permission::verifyByRef('void_payments') && !$this->isVoid()) {
            return true;
        }
        return false;
    }

    public function getNonCreditAmount(AbstractPayment $payment) {
        return $payment->getProperty('amount');
    }
    
    public function getCreditAmount(AbstractPayment $payment) {
        return NULL;
    }
    
}
