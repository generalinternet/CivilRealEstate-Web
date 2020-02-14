<?php
/**
 * Description of AbstractExpense
 *
 * @author General Internet
 * @copyright  2020 General Internet
 * @version    4.0.0
 */
abstract class AbstractExpense extends AbstractAccountingElement {
    
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
        if (!empty($currency)) {
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

    protected function formatAmountForDisplay($amount, $showCurrency = false) {
        $currency = $this->getCurrency();
        $total = $currency->getProperty('symbol') . GI_StringUtils::formatMoney($amount);
        if ($showCurrency && !GI_CSV::csvExporting()) {
            $total .= ' (' . $currency->getProperty('name') . ')';
        }
        return $total;
    }

    public function linkModelToExpense(GI_Model $model) {
        return ExpenseFactory::linkModelAndExpenseModel($model, $this);
    }

    public function getExpenseItems($includeInProgress = true, $includeGrouped = true) {
        $expenseItemsSearch = $this->getExpenseItemsDataSearch($includeInProgress, $includeGrouped);
        $expenseItems = $expenseItemsSearch->select();
        return $expenseItems;
    }

    protected function getExpenseItemsDataSearch($includeInProgress = true, $includeGrouped = true) {
        $expenseItemsSearch = ExpenseItemFactory::search()
                ->filter('expense_id', $this->getProperty('id'));
        if (!$this->getIsVoid()) {
            $expenseItemsSearch->filter('void', 0);
        }
        if ($this->getIsCancelled()) {
            $expenseItemsSearch->filter('cancelled', 0);
        }
        if (!$includeInProgress) {
            $expenseItemsSearch->filter('in_progress', 0);
        }
        if (!$includeGrouped) {
            $expenseItemsSearch->filterNull('expense_item_group_id');
        }
        return $expenseItemsSearch;
    }

    public function getExpenseItemGroups($includeInProgress = true) {
        $expenseItemGroupSearch = ExpenseItemGroupFactory::search()
                ->filter('expense_id', $this->getProperty('id'));
        if (!$this->getIsVoid()) {
            $expenseItemGroupSearch->filter('void', 0);
        }
        if ($this->getIsCancelled()) {
            $expenseItemGroupSearch->filter('cancelled', 0);
        }
        if (!$includeInProgress) {
            $expenseItemGroupSearch->filter('in_progress', 0);
        }
        $expenseItemGroups = $expenseItemGroupSearch->select();
        return $expenseItemGroups;
    }


    public function getNetAmount($formatForDisplay = false, $showCurrency = false, $includeInProgress = true) {
        $expenseId = $this->getProperty('id');
        if (empty($expenseId)) {
            return 0;
        }
        $expenseItemSearch = $this->getExpenseItemsDataSearch($includeInProgress, false);
        $expenseItemsSum = $expenseItemSearch->sum('net_amount');
        $sum = $expenseItemsSum['net_amount'];
        $expenseItemGroups = $this->getExpenseItemGroups($includeInProgress);
        if (!empty($expenseItemGroups)) {
            foreach ($expenseItemGroups as $expenseItemGroup) {
                $sum += $expenseItemGroup->getNetTotal(false, false, true);
            }
        }
        if ($formatForDisplay) {
            return $this->formatAmountForDisplay($sum, $showCurrency);
        }
        return $sum;
    }

    public function getPayments() {
        $paymentsArray = PaymentFactory::getPaymentsByExpense($this);
        return $paymentsArray;
    }
    
    public function getPaymentsSum() {
        $paymentsSum = PaymentFactory::getPaymentsByExpense($this, true);
        if (empty($paymentsSum)) {
            $paymentsSum = 0;
        }
        return $paymentsSum;
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
        if (empty($sortableNetSum)) {
            $sortableNetSum = 0;
        }
        $this->setProperty('sortable_net', $sortableNetSum);
        return $sortableNetSum;
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
        $balance = $this->getBalance();
        $this->setProperty('sortable_balance', $balance);
        if (($balance <= 0) && !empty($this->getExpenseItems())) {
            $paidInFull = $this->getProperty('paid_in_full');
            if (empty($paidInFull)) {
                $this->setProperty('paid_in_full', GI_Time::getDate());
            }
        } else {
            $this->setProperty('paid_in_full', NULL);
        }
        return $balance;
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
        return true;
    }

    public function save() {
        $this->updateSortableValues();
        $applicableDate = $this->getProperty('applicable_date');
        if (empty($applicableDate)) {
            $expenseDate = $this->getProperty('date');
            if (empty($expenseDate)) {
                $date = GI_Time::getDate();
                $this->setProperty('applicable_date', $date);
            } else {
                $this->setProperty('applicable_date', $expenseDate);
            }
        }
        return parent::save();
    }

    public static function getTotalSortableNetExpenses() {
        $sumArray = ExpenseFactory::search()
                ->sum('sortable_net');
        if (empty($sumArray)) {
            $totalSortableNet = 0;
        } else {
            $totalSortableNet = $sumArray['sortable_net'];
        }
        return $totalSortableNet;
    }

    public function getSortableBalance($formatForDisplay = false) {
        $sortableBalance = $this->getProperty('sortable_balance');
        if (!$formatForDisplay) {
            return $sortableBalance;
        }
        return $this->formatAmountForDisplay($sortableBalance, true);
    }

    public function getSortableTotal($formatForDisplay = false) {
        $sortableTotal = $this->getProperty('sortable_total');
        if (!$formatForDisplay) {
            return $sortableTotal;
        }
        return $this->formatAmountForDisplay($sortableTotal, true);
    }

    public function getIsLocked() {
        return false;
    }

    
    public function void($removedNote = '') {
        if (!$this->getIsVoidable()) {
            return false;
        }
        $items = $this->getExpenseItems();
        foreach ($items as $item) {
            $item->void(false);
        }
        $groups = $this->getExpenseItemGroups();
        if (!empty($groups)) {
            foreach ($groups as $group) {
                if (!$group->void($removedNote)) {
                    return false;
                }
            }
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
        $items = $this->getExpenseItems();
        foreach ($items as $item) {
            $item->cancel(false);
        }
        $groups = $this->getExpenseItemGroups();
        if (!empty($groups)) {
            foreach ($groups as $group) {
                if (!$group->cancel()) {
                    return false;
                }
            }
        }
        $payments = $this->getPayments();
        if (!empty($payments)) {
            foreach ($payments as $appliedPayment) {
                if (!$appliedPayment->void()) {
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
    
    public function isUnCancelleable() {
        if (ProjectConfig::getIsQuickbooksIntegrated() && !empty($this->getProperty('cancelled'))) {
            return true;
        }
        return false;
    }

    public function unCancel() {
        if ($this->isUnCancelleable()) {
            $expenseItemGroups = $this->getExpenseItemGroups();
            if (!empty($expenseItemGroups)) {
                foreach($expenseItemGroups as $group) {
                    if (!empty($group->getProperty('cancelled')) && !$group->unCancel()) {
                        return false;
                    }
                }
            }
            $expenseItemSearch = ExpenseItemFactory::search();
            $expenseItemSearch->filter('expense_id', $this->getId())
                    ->filter('cancelled', 1);
            $expenseItems = $expenseItemSearch->select();
            if (!empty($expenseItems)) {
                foreach ($expenseItems as $item) {
                    if (!$item->unCancel()) {
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

    public function getExportUITableCols($taxRateQBIds = array()) {
        $tableColArrays = array(
            array(
                'header_title' => 'Type',
                'method_name' => 'getTypeTitle'
            ),
            array(
                'header_title' => 'Bill #',
                'method_name' => 'getBillNumber',
            ),
            array(
                'header_title' => 'Date',
                'method_name' => 'getDate',
                'method_attributes' => 'false,true',
            ),
            array(
                'header_title' => 'Pay To',
                'method_name' => 'getPayToString'
            ),
            array(
                'header_title' => 'Currency',
                'method_name' => 'getCurrencyName'
            ),
        );
        foreach ($taxRateQBIds as $taxRateQBId) {
            $tableColArrays[] = array(
                'header_title' => QBTaxRateFactory::getQBTaxRateName($taxRateQBId),
                'method_name' => 'getExpenseTaxTotal',
                'method_attributes' => array(
                    $taxRateQBId
                )
            );
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
            'method_attributes'=>array(
                false,
                false,
                true
            )
        );

        $tableColArrays[] = array(
            'header_title' => 'Total',
            'method_name' => 'getTotalAmount',
            'method_attributes'=>array(
                false,
                false,
                true
            )
        );

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
            'method_name' => 'getBalance'
        );
        $UITableCols = array();
        foreach ($tableColArrays as $tableColArray) {
            $UITableCols[] = UITableCol::buildUITableColFromArray($tableColArray);
        }
        return $UITableCols;
    }
    
    public function getBillNumber() {
        return '';
    }
    
    public function getBill() {
        return NULL;
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
    
    protected function getTaxTotals() {
        if (empty($this->taxTotals)) {
            $totals = array();
            $ungroupedItems = $this->getExpenseItems(true, false);
            if (!empty($ungroupedItems)) {
                foreach ($ungroupedItems as $item) {
                    $itemTaxTotals = $item->getTaxTotals();
                    if (!empty($itemTaxTotals)) {
                        foreach ($itemTaxTotals as $taxRateId=>$taxRateArray) {
                            if (isset($totals[$taxRateId])) {
                                $totals[$taxRateId]['amount'] += $taxRateArray['amount'];
                            } else {
                                $totals[$taxRateId] = $taxRateArray;
                            }
                        }
                    }
                }
            }
            $groups = $this->getExpenseItemGroups();
            if (!empty($groups)) {
                foreach ($groups as $group) {
                    $groupTaxTotals = $group->getTaxTotals();
                    if (!empty($groupTaxTotals)) {
                        foreach ($groupTaxTotals as $taxRateId => $taxRateArray) {
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

    public function getExpenseTaxTotal($taxRateQBId = NULL, $formatForDisplay = false) {
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
    
    public function getPayToString() {
        if (dbConnection::isModuleInstalled('order')) {
            $orderHasExpenseArray = OrderHasExpenseFactory::getModelArrayByExpense($this);
            if (!empty($orderHasExpenseArray)) {
                $orderHasExpense = $orderHasExpenseArray[0];
                $contact = $orderHasExpense->getContact();
                if (!empty($contact)) {
                    return $contact->getName();
                }
            }
        }
        return '';
    }
    
    public function getLastPaymentDate() {
        $lastPaymentArray = PaymentFactory::getPaymentsByExpense($this, false, true);
        if (!empty($lastPaymentArray)) {
            $dateTime = new DateTime($lastPaymentArray[0]->getProperty('date'));
            return $dateTime->format('m/d/Y');
        }
        return '';
    }
    
    public function getNetTotal() {
        if (empty($this->netTotal)) {
            $this->netTotal = $this->getNetAmount(false);
        }
        return $this->netTotal;
    }

    public function getNetInProgress() {
        if (empty($this->netInProgress)) {
            $netTotal = (float) $this->getNetTotal();
            $netFinalzied = (float) $this->getNetFinalized();
            $this->netInProgress = $netTotal - $netFinalzied;
        }
        return $this->netInProgress;
    }

    public function getNetFinalized() {
        if (empty($this->netFinalized)) {
            $netFinalized = $this->getNetAmount(false, false, false);
            if (empty($netFinalized)) {
                $netFinalized = 0;
            }
            $this->netFinalized = $netFinalized;
        }

        return $this->netFinalized;
    }

    public function tagExpense(AbstractTag $tag) {
        return ExpenseFactory::linkExpenseAndTag($this, $tag);
    }

    public function untagExpense(AbstractTag $tag) {
        return ExpenseFactory::unlinkExpenseAndTag($this, $tag);
    }

    public function softDelete() {
        $id = $this->getId();
        if (parent::softDelete()) {
            if (dbConnection::isModuleInstalled('order')) {
                $search = OrderHasExpenseFactory::search();
                $search->filter('expense_id', $id);
                $results = $search->select();
                if (!empty($results)) {
                    foreach ($results as $orderHasExpense) {
                        $orderHasExpense->softDelete();
                    }
                }
            }
            return true;
        }
        return false;
    }

}
