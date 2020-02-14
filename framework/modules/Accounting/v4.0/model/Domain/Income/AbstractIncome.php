<?php
/**
 * Description of AbstractIncome
 *
 * @author General Internet
 * @copyright  2018 General Internet
 * @version    3.0.0
 */
abstract class AbstractIncome extends AbstractAccountingElement {
    
    /**
     * @var Currency
     */
    protected $currency = NULL;
    protected $netTotal = NULL;
    protected $netFinalized = NULL;
    protected $netInProgress = NULL;
    
    protected $taxTotals = NULL;

    /**
     * @return Currency
     */
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
    
    public function getCurrencyName() {
        $currency = $this->getCurrency();
        if (!empty($currency)) {
            return $currency->getProperty('name');
        }
        return '';
    }
    
    protected function formatAmountForDisplay($amount, $showCurrency = false){
        $currency = $this->getCurrency();
        $total = $currency->getProperty('symbol') . GI_StringUtils::formatMoney($amount);
        if($showCurrency && !GI_CSV::csvExporting()){
            $total .= ' (' . $currency->getProperty('name') . ')';
        }
        return $total;
    }
    
    public function linkModelToIncome(GI_Model $model) {
        return IncomeFactory::linkModelAndIncomeModel($model, $this);
    }

    public function getIncomeItems($includeInProgress = true) {
        $incomeItemSearch = IncomeItemFactory::search()
                ->filter('income_id', $this->getProperty('id'));
        if (!$this->getIsVoid()) {
            $incomeItemSearch->filter('void', 0);
        }
        if (!$this->getIsCancelled()) {
            $incomeItemSearch->filter('cancelled', 0);
        }        
        if (!$includeInProgress) {
            $incomeItemSearch->filter('in_progress', 0);
        }
        $incomeItems = $incomeItemSearch->select();
        return $incomeItems;
    }

    public function getInProgressIncomeItems() {
        $incomeItemSearch = IncomeItemFactory::search()
                ->filter('income_id', $this->getProperty('id'))
                ->filter('void', 0)
                ->filter('cancelled', 0)
                ->filter('in_progress', 1);
        $incomeItems = $incomeItemSearch->select();
        return $incomeItems;
    }

    /**
     * 
     * @return string
     */
    public function getNetAmount($formatForDisplay = false, $showCurrency = false, $includeInProgress = true) {
        $incomeId = $this->getProperty('id');
        if (empty($incomeId)) {
            return 0;
        }
        $incomeItemSearch = IncomeItemFactory::search()
                ->filter('income_id', $incomeId);
        if (!$this->getIsVoid()) {
            $incomeItemSearch->filter('void', 0);
        }
        if (!$this->getIsCancelled()) {
            $incomeItemSearch->filter('cancelled', 0);
        }        
                
        if (!$includeInProgress) {
            $incomeItemSearch->filter('in_progress', 0);
        }
        $incomeItemsSum = $incomeItemSearch->sum('net_amount');
        $sum = (float) $incomeItemsSum['net_amount'];
        if ($formatForDisplay) {
            return $this->formatAmountForDisplay($sum, $showCurrency);
        }
        return (string) $sum;
    }
    
    public function getInProgressNetAmount($formatForDisplay = false, $showCurrency = false) {
        $incomeId = $this->getProperty('id');
        if (empty($incomeId)) {
            return 0;
        }
        $incomeItemSearch = IncomeItemFactory::search()
                ->filter('income_id', $incomeId)
                ->filter('void', 0)
                ->filter('cancelled', 0)
                ->filter('in_progress', 1);
        $incomeItemsSum = $incomeItemSearch->sum('net_amount');
        $sum = (float) $incomeItemsSum['net_amount'];
        if ($formatForDisplay) {
            return $this->formatAmountForDisplay($sum, $showCurrency);
        }
        return $sum;
    }

    public function getTotalAmount($formatForDisplay = false) {
        $netAmount = $this->getNetAmount(false);
        $total = $netAmount;
        $taxTotals = $this->getTaxTotals();
        if (!empty($taxTotals)) {
            foreach ($taxTotals as $taxRateId=>$taxRateArray) {
                $total += $taxRateArray['amount'];
            }
        }

        if ($formatForDisplay) {
            return $this->formatAmountForDisplay($total);
        }
        return $total;
    }

    protected function updateSortableNet() {
        $sortableNetSum = $this->getNetAmount();
        $this->setProperty('sortable_net', $sortableNetSum);
        return true;
    }
    
    public function getBalance() {
        $totalSum = $this->getTotalAmount();
        if (empty($totalSum)) {
            $totalSum = 0;
        }
        $paymentsSum = $this->getPaymentsSum();
        if (empty($paymentsSum)) {
            $paymentsSum = 0;
        }
        $val = $totalSum - $paymentsSum;
        return round($val, 2);
    }
    
    protected function updateSortableBalance() {
        $sortableBalance = $this->calculateBalance();
        $this->setProperty('sortable_balance', $sortableBalance);
        $incomeItems = $this->getIncomeItems();
        if (!empty($incomeItems) && $sortableBalance <= 0) {
            $paidInFull = $this->getProperty('paid_in_full');
            if (empty($paidInFull)) {
                $this->setProperty('paid_in_full', GI_Time::getDate());
            }
        } else {
            $this->setProperty('paid_in_full', NULL);
        }
        return true;
    }
    
    protected function updateSortableTotal() {
        $total = $this->getTotalAmount();
        $this->setProperty('sortable_total', $total);
        return $total;
    }
    
    public function updateSortableValues() {
        $this->updateSortableNet();
        $this->updateSortableTotal();
        $this->updateSortableBalance();
    }

    public function save() {
        $this->updateSortableValues();
        $applicableDate = $this->getProperty('applicable_date');
        if (empty($applicableDate)) {
            $incomeDate = $this->getProperty('date');
            if (empty($incomeDate)) {
                $date = GI_Time::getDate();
                $this->setProperty('applicable_date', $date);
            } else {
                $this->setProperty('applicable_date', $incomeDate);
            }
        }
        return parent::save();
    }

    public function calculateBalance($formatForDisplay = false, $showCurrency = false) {
        $totalSum = $this->getTotalAmount();
        if (empty($totalSum)) {
            $totalSum = 0;
        }
        $paymentsSum = $this->getPaymentsSum();
        if (empty($paymentsSum)) {
            $paymentsSum = 0;
        }
        $val = round($totalSum - $paymentsSum, 2);
        if ($formatForDisplay) {
            return $this->formatAmountForDisplay($val, $showCurrency);
        }
        return $val;
    }

    public function getSortableBalance($formatForDisplay = false, $showCurrency = false) {
        $sortableBalance = $this->getProperty('sortable_balance');
        if (!$formatForDisplay) {
            return $sortableBalance;
        }
        return $this->formatAmountForDisplay($sortableBalance, $showCurrency);
    }
    
    public function getSortableTotal($formatForDisplay = false, $showCurrency = false) {
        $sortableTotal = $this->getProperty('sortable_total');
        if (!$formatForDisplay) {
            return $sortableTotal;
        }
        return $this->formatAmountForDisplay($sortableTotal, $showCurrency);
    }

    public function getIsLocked() {
        return false;
    }

    public function void($removedNote = '') {
        if (!$this->getIsVoidable()) {
            return false;
        }
        $items = $this->getIncomeItems();
        foreach ($items as $item) {
            $item->void();
        }
        $payments = $this->getPayments();
        if (!empty($payments)) {
            foreach ($payments as $appliedPayment) {
                if (!$appliedPayment->void()) {
                    return false;
                }
                $groupPayment = $appliedPayment->getGroupPayment();
                if (!$groupPayment->save()) {
                    return false;
                }
            }
        }
        $removedById = Login::getUserId();
        $removedDate = GI_Time::getDate();
        $this->setProperty('removed_by_id', $removedById);
        $this->setProperty('removed_note', $removedNote);
        $this->setProperty('removed_date', $removedDate);
        $this->setProperty('void', 1);
        $this->setProperty('cancelled', 0);
        return $this->save();
    }

    public function cancel($removedNote = '') {
        if (!$this->getIsCancellable()) {
            return false;
        }
        $items = $this->getIncomeItems();
        foreach ($items as $item) {
            $item->cancel(false);
        }

        $payments = $this->getPayments();
        if (!empty($payments)) {
            foreach ($payments as $appliedPayment) {
                if (!$appliedPayment->void()) {
                    return false;
                }
                $groupPayment = $appliedPayment->getGroupPayment();
                if (!$groupPayment->save()) {
                    return false;
                }
            }
        }

        $removedById = Login::getUserId();
        $removedDate = GI_Time::getDate();
        $this->setProperty('removed_by_id', $removedById);
        $this->setProperty('removed_note', $removedNote);
        $this->setProperty('removed_date', $removedDate);
        $this->setProperty('void', 0);
        $this->setProperty('cancelled', 1);
        return $this->save();
    }

    public function getPayments() {
        $paymentsArray = PaymentFactory::getPaymentsByIncome($this);
        return $paymentsArray;
    }

    public function getMaxRefundableSum() {
        $creditType = TypeModelFactory::getTypeModelByRef('credit', 'group_payment_type');
        $paymentTableName = PaymentFactory::getDbPrefix() . 'payment';
        $paymentSearch = PaymentFactory::search();
        $paymentSearch->join('group_payment', 'id', $paymentTableName, 'group_payment_id', 'gp')
                ->filterNotEqualTo('gp.group_payment_type_id', $creditType->getProperty('id'))
                ->filterByTypeRef('income')
                ->filter('income.income_id', $this->getProperty('id'));
        $sumArray = $paymentSearch->sum(array('amount'));
        if (isset($sumArray['amount'])) {
            return $sumArray['amount'];
        }
        return 0;
    }

    public function getPaymentsSum() {
        $paymentsSum = PaymentFactory::getPaymentsByIncome($this, true);
        return $paymentsSum;
    }

    public function getExportUITableCols() {
        $tableColArrays = array(
            array(
                'header_title' => 'Type',
                'method_name' => 'getTypeTitle'
            ),
            array(
                'header_title' => 'Invoice #',
                'method_name' => 'getInvoiceNumber',
            ),
            array(
                'header_title' => 'Invoice Date',
                'method_name' => 'getDate',
                'method_attributes' => 'false,true',
            ),
            array(
                'header_title'=>'Finalized Date',
                'method_name'=>'getInvoiceFinalizedDate'
            ),
            array(
                'header_title' => 'Bill To',
                'method_name' => 'getBillToString'
            ),
            array(
                'header_title' => 'Currency',
                'method_name' => 'getCurrencyName'
            ),
        );
        $taxRateQBIds = IncomeFactory::getAllTaxRateQBIdsFromIncomes();
        if (!empty($taxRateQBIds)) {
            foreach ($taxRateQBIds as $taxRateQBId) {
                $tableColArrays[] = array(
                    'header_title' => QBTaxRateFactory::getQBTaxRateName($taxRateQBId),
                    'method_name' => 'getIncomeTaxTotal',
                    'method_attributes' => array(
                        $taxRateQBId
                    )
                );
            }
        }

        $tableColArrays[] = array(
            'header_title' => 'Net In Progress',
            'method_name' => 'getNetInProgress'
        );

        $tableColArrays[] = array(
            'header_title' => 'Net Finalized',
            'method_name' => 'getNetFinalized'
        );

        $tableColArrays[] = array(
            'header_title' => 'Net Total',
            'method_name' => 'getNetAmount',
            'method_attributes' => array(
                false,
                false,
                true
            ),
        );
        $tableColArrays[] = array(
            'header_title' => 'Total',
            'method_name' => 'getTotalAmount',
            'method_attributes' => array(
                false,
                false,
                true
            ),
        );
        if (!ProjectConfig::getIsQuickbooksIntegrated()) {
            $tableColArrays[] = array(
                'header_title' => 'Payments Total',
                'method_name' => 'getPaymentsSum'
            );
            $tableColArrays[] = array(
                'header_title' => 'Last Payment Date',
                'method_name' => 'getLastPaymentDate'
            );
            $tableColArrays[] = array(
                'header_title' => 'Balance',
                'method_name' => 'calculateBalance',
                'method_attributes' => array(
                    false,
                    false
                ),
            );
        }

        $UITableCols = array();
        foreach ($tableColArrays as $tableColArray) {
            $UITableCols[] = UITableCol::buildUITableColFromArray($tableColArray);
        }
        return $UITableCols;
    }
    
    public function getInvoice() {
        return NULL;
    }
    

    public function getInvoiceNumber() {
        return '';
    }
    
    public function getInvoiceFinalizedDate() {
        return '';
    }

    public function getDate($formatForDisplay = false, $formatForExport = false) {
        $date = $this->getProperty('applicable_date');
        if ($formatForDisplay) {
            $date = GI_Time::formatDateForDisplay($date);
        } else if ($formatForExport) {
            $dateTime = new DateTime($date . ' 00:00:00');
            $date = $dateTime->format('m/d/Y');
        }
        return $date;
    }
    
    public function getBillToString() {
        if (dbConnection::isModuleInstalled('order')) {
            $orderHasIncomeArray = OrderHasIncomeFactory::getModelArrayByIncome($this);
            if (!empty($orderHasIncomeArray)) {
                $orderHasIncome = $orderHasIncomeArray[0];
                $contact = $orderHasIncome->getContact();
                if (!empty($contact)) {
                    return $contact->getName();
                }
            }
        }
        return '';
    }

    public function getIncomeTaxTotal($taxRateQBId = NULL, $formatForDisplay = false) {
        $sum = 0;
        $taxTotals = $this->getTaxTotals();
        if (!empty($taxTotals)) {
            if (!empty($taxRateQBId) && isset($taxTotals[$taxRateQBId])) {
                $sum = $taxTotals[$taxRateQBId]['amount'];
            } else {
                foreach ($taxTotals as $taxRateId=>$taxRateArray) {
                    $sum += $taxRateArray['amount'];
                }
            }
        }
        if ($formatForDisplay) {
            $sum = '$' . GI_StringUtils::formatMoney($sum);
        }

        return $sum;
    }

    public function getLastPaymentDate() {
        $lastPaymentArray = PaymentFactory::getPaymentsByIncome($this, false, true);
        if (!empty($lastPaymentArray)) {
            $dateTime = new DateTime($lastPaymentArray[0]->getProperty('date'));
            return $dateTime->format('m/d/Y');
        }
        return '';
    }

    public function getNetTotal() {
        if (empty($this->netTotal)) {
            $this->netTotal = $this->getNetAmount(false, false, true);
        }
        return $this->netTotal;
    }

    public function getNetInProgress() {
        if (empty($this->netInProgress)) {
            $this->netInProgress = $this->getInProgressNetAmount(false, false);
        }
        return $this->netInProgress;
    }

    public function getNetFinalized() {
        if (empty($this->netFinalized)) {
            $netTotal = (float) $this->getNetTotal();
            $netInProgress = (float) $this->getNetInProgress();
            $netFinalized = $netTotal - $netInProgress;
            $this->netFinalized = $netFinalized;
        }
        return $this->netFinalized;
    }
    
    public function tagIncome(AbstractTag $tag) {
        return IncomeFactory::linkIncomeAndTag($this, $tag);
    }
    
    public function untagIncome(AbstractTag $tag) {
        return IncomeFactory::unlinkIncomeAndTag($this, $tag);
    }

    protected function getTaxTotals() {
        if (empty($this->taxTotals)) {
            $totals = array();
            $items = $this->getIncomeItems(true);
            if (!empty($items)) {
                foreach ($items as $item) {
                    $itemTaxTotals = $item->getTaxTotals();
                    if (!empty($itemTaxTotals)) {
                        foreach ($itemTaxTotals as $taxRateId => $taxRateArray) {
                            if (isset($totals[$taxRateId])) {
                                $totals[$taxRateId]['amount'] += $taxRateArray['amount'];
                            } else {
                                $totals[$taxRateId] = $taxRateArray;
                            }
                        }
                    }
                }
            }
            $this->taxTotals = $totals;
        }
        return $this->taxTotals;
    }
    
    public function isUnCancellable() {
        if ($this->isCancelled()) {
            return true;
        }
        return false;
    }
    
    public function unCancel() {
        if ($this->isUnCancellable()) {
            $items = $this->getIncomeItems();
            if (!empty($items)) {
                foreach ($items as $item) {
                    if (!empty($item->getProperty('cancelled')) && !$item->unCancel()) {
                        return false;
                    }
                }
            }
            $this->setProperty('cancelled', 0);
            $this->setProperty('removed_date', NULL);
            $this->setProperty('removed_by_id', NULL);
            $this->setProperty('removed_note', NULL);
            if (!$this->save()) {
                return false;
            }
            return true;
        } 
        return false;
    }

}
