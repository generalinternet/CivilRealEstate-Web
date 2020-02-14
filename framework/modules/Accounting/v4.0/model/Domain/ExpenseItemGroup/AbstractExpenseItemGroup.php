<?php
/**
 * Description of AbstractExpenseItemGroup
 *
 * @author General Internet
 * @copyright  2018 General Internet
 * @version    3.0.0
 */

abstract class AbstractExpenseItemGroup extends GI_Model {
    
    protected $expense = NULL;
    protected $expenseItems = NULL;
    protected $firstExpenseItem = NULL;
    
    public function getExpense() {
        if (empty($this->expense)) {
            $this->expense = ExpenseFactory::getModelById($this->getProperty('expense_id'));
        }
        return $this->expense;
    }
    
    /**
     * @return AbstractExpenseItem[]
     */
    public function getExpenseItems() {
        if (empty($this->expenseItems)) {
            $this->expenseItems = ExpenseItemFactory::getModelArrayByExpenseItemGroup($this);
        }
        return $this->expenseItems;
    }
    
    public function getFirstExpenseItem() {
        if (empty($this->firstExpenseItem)) {
            $array = ExpenseItemFactory::getModelArrayByExpenseItemGroup($this, 1);
            if (!empty($array)) {
                $this->firstExpenseItem = $array[0];
            }
        }
        return $this->firstExpenseItem;
    }
    
    
    public function updateNetAmount() {
        $netAmount = 0;
        $expenseItems = $this->getExpenseItems();
        $inProgress = false;
        if (!empty($expenseItems)) {
            $firstItemInProgress = $expenseItems[0]->getProperty('in_progress');
            if (!empty($firstItemInProgress)) {
                $inProgress = true;
            }
            foreach ($expenseItems as $expenseItem) {
                $netAmount += $expenseItem->getProperty('net_amount');
            }
        }
        $this->setProperty('net_amount', $netAmount);
        if ($inProgress) {
            $this->setProperty('in_progress', 1);
        }
        return $this->save();
    }

    public function cancel() {
        $expenseItems = $this->getExpenseItems();
        if (!empty($expenseItems)) {
            foreach ($expenseItems as $expenseItem) {
                if (empty($expenseItem->getProperty('cancelled'))) {
                    if (!$expenseItem->cancel(false)) {
                        return false;
                    }
                }
            }
        }
        $this->setProperty('void', 0);
        $this->setProperty('cancelled', 1);
        if (!$this->save()) {
            return false;
        }
        $expense = $this->getExpense();
        if (!empty($expense)) {
            if (!$expense->save()) {
                return false;
            }
        }
        return true;
    }
    
    public function isUnCancellable() {
        if(ProjectConfig::getIsQuickbooksIntegrated() && !empty($this->getProperty('cancelled'))) {
            return true;
        }
        return false;
    }
    
    public function unCancel() {
        if ($this->isUnCancellable()) {
            $items = $this->getExpenseItems();
            if (!empty($items)) {
                foreach ($items as $item) {
                    if (!empty($item->getProperty('cancelled')) && !$item->unCancel()) {
                        return false;
                    }
                }
            }
            $this->setProperty('cancelled', 0);
            if (!$this->save()) {
                return false;
            }
            
            return true;
        }
        return false;
    }

    public function void() {
        $expenseItems = $this->getExpenseItems();
        if (!empty($expenseItems)) {
            foreach ($expenseItems as $expenseItem) {
                if (empty($expenseItem->getProperty('void'))) {
                    if (!$expenseItem->void(false)) {
                        return false;
                    }
                }
            }
        }
        $this->setProperty('void', 1);
        $this->setProperty('cancelled', 0);
        if (!$this->save()) {
            return false;
        }
        $expense = $this->getExpense();
        if (!empty($expense)) {
            if (!$expense->save()) {
                return false;
            }
        }
        return true;
    }

    /**
     * 
     * @param int $taxRateQBId
     * @return String[]
     */
    public function getTaxTotal($taxRateQBId) {
        $taxTotals = $this->getTaxTotals();
        if (!empty($taxTotals) && isset($taxTotals[$taxRateQBId])) {
            $taxRateArray = $taxTotals[$taxRateQBId];
            return $taxRateArray;
        }
        return NULL;
    }
    
    /**
     * 
     * @return String[]
     */
    public function getTaxTotals() {
        if ($this->getIsVoidOrCancelled()) {
            return 0;
        }
        $taxCodeQBId = $this->getProperty('tax_code_qb_id');
        $applicableDate = $this->getProperty('applicable_date');
        $netAmount = $this->getNetTotal(false, false, false);
        $taxTotals = QBTaxCodeFactory::getQBTaxTotals($taxCodeQBId, $netAmount, $applicableDate, 'purchase');

        return $taxTotals;
    }

    public function getIsVoidOrCancelled() {
        if ($this->getIsVoid() || $this->getIsCancelled()) {
            return true;
        }
        return false;
    }
    
    public function getIsVoid() {
        if (!empty($this->getProperty('void'))) {
            return true;
        }
        return false;
    }
    
    public function getIsCancelled() {
        if (!empty($this->getProperty('cancelled'))) {
            return true;
        }
        return false;
    }

    public function getNetTotal($formatForDisplay = false, $showCurrency = false, $forceUpdate = false) {
        if ($forceUpdate) {
            $this->updateNetAmount();
        }
        $netAmount = $this->getProperty('net_amount');
        if ($formatForDisplay) {
            return $this->formatAmountForDisplay($netAmount, $showCurrency);
        }
        return $netAmount;
    }

    public function getTotal($formatForDisplay = false) {
        $sum = $this->getNetTotal();
        $taxTotals = $this->getTaxTotals();
        if (!empty($taxTotals)) {
            foreach ($taxTotals as $taxRateQBId=>$taxRateArray) {
                $sum += $taxRateArray['amount'];
            }
        }

        if ($formatForDisplay) {
            return $this->formatAmountForDisplay($sum);
        }
        return $sum;
    }
    
    public function getCurrency() {
        $expense = $this->getExpense();
        if (!empty($expense)) {
            return $expense->getCurrency();
        }
        return NULL;
    }

    protected function formatAmountForDisplay($amount, $showCurrency = false) {
        $currency = $this->getCurrency();
        if ($currency) {
            $total = $currency->getProperty('symbol') . GI_StringUtils::formatMoney($amount);
            if ($showCurrency && !GI_CSV::csvExporting()) {
                $total .= ' (' . $currency->getProperty('name') . ')';
            }
        } else {
            $total = GI_StringUtils::formatMoney($amount);
        }
        return $total;
    }
    
    public function markInProgress() {
        $expenseItems = $this->getExpenseItems();
        if (!empty($expenseItems)) {
            foreach ($expenseItems as $expenseItem) {
                $expenseItem->setProperty('in_progress', 1);
                if (!$expenseItem->save()) {
                    return false;
                }
            }
        }
        $this->setProperty('in_progress', 1);
        return $this->save();
    }
    
    public function unMarkInProgress() {
        $expenseItems = $this->getExpenseItems();
        if (!empty($expenseItems)) {
            foreach ($expenseItems as $expenseItem) {
                $expenseItem->setProperty('in_progress', 0);
                if (!$expenseItem->save()) {
                    return false;
                }
            }
        }
        $this->setProperty('in_progress', 0);
        return $this->save();
    }

}
