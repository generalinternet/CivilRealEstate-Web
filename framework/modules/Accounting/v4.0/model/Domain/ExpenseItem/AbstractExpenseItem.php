<?php
/**
 * Description of AbstractExpenseItem
 *
 * @author General Internet
 * @copyright  2018 General Internet
 * @version    3.0.1
 */
abstract class AbstractExpenseItem extends AbstractAccountingElement {

    /** @var Expense */
    protected $expense = NULL;
    
    /** @var AbstractExpenseItemGroup */
    protected $expenseItemGroup = NULL;

    /** @return Expense */
    public function getExpense() {
        if (empty($this->expense)) {
            $expenseId = $this->getProperty('expense_item.expense_id');
            $expense = ExpenseFactory::getModelById($expenseId);
            $this->expense = $expense;
        }
        return $this->expense;
    }
    
    public function getExpenseItemGroup() {
        if (empty($this->expenseItemGroup)) {
            $this->expenseItemGroup = ExpenseItemGroupFactory::getModelById($this->getProperty('expense_item_group_id'));
        }
        return $this->expenseItemGroup;
    }

    public function getNumberOfModelsLinkedToExpenseItem() {
        return ExpenseItemFactory::getNumberOfModelsLinkedToExpenseItem($this);
    }

    /** @return Currency */
    public function getCurrency(){
        $expense = $this->getExpense();
        if($expense){
            return $expense->getCurrency();
        }
        return NULL;
    }
    
    public function getCurrencyTitle(){
        $currency = $this->getCurrency();
        if($currency){
            return $currency->getProperty('name');
        }
        return NULL;
    }
    
    protected function formatAmountForDisplay($amount, $showCurrency = false){
        $currency = $this->getCurrency();
            if($currency){
            $total = $currency->getProperty('symbol') . GI_StringUtils::formatMoney($amount);
            if($showCurrency && !GI_CSV::csvExporting()){
                $total .= ' (' . $currency->getProperty('name') . ')';
            }
        } else {
            $total = GI_StringUtils::formatMoney($amount);
        }
        return $total;
    }
    
    public function getTotal($formatForDisplay = false, $showCurrency = false) {
        $sum = $this->getNetTotal();
        $taxTotals = $this->getTaxTotals();
        if (!empty($taxTotals)) {
            foreach ($taxTotals as $taxRateQBId=>$taxRateArray) {
                $sum += $taxRateArray['amount'];
            }
        }

        if ($formatForDisplay) {
            return $this->formatAmountForDisplay($sum, $showCurrency);
        }
        return $sum;
    }

    public function getNetTotal($formatForDisplay = false, $showCurrency = false) {
        $netAmount = $this->getProperty('net_amount');
        if ($formatForDisplay) {
            return $this->formatAmountForDisplay($netAmount, $showCurrency);
        }
        return $netAmount;
    }

    public function getTaxTotals() {
        if ($this->getIsVoidOrCancelled()) {
            return 0;
        }
        $taxCodeQBId = $this->getProperty('tax_code_qb_id');
        $applicableDate = $this->getProperty('applicable_date');
        $netAmount = $this->getProperty('net_amount');
        $taxTotals = QBTaxCodeFactory::getQBTaxTotals($taxCodeQBId, $netAmount, $applicableDate, 'purchase');

        return $taxTotals;
    }
    
    public function save() {
        $applicableDate = $this->getProperty('applicable_date');
        $expense = $this->getExpense();
        if (empty($applicableDate)) {
            $expenseDate = $this->getProperty('inception');
            if (!empty($expense)) {
                $expenseDate = $expense->getProperty('applicable_date');
            }
            if (empty($expenseDate)) {
                $date = GI_Time::getDate();
                $this->setProperty('applicable_date', $date);
            } else {
                $this->setProperty('applicable_date', $expenseDate);
            }
        }
        return parent::save();
    }

    public function void($saveExpense = true) {
        if (!$this->getIsVoidable()) {
            return false;
        }
        $this->setProperty('void', 1);
        $this->setProperty('cancelled', 0);
        if (!$this->save()) {
            return false;
        }
        if ($saveExpense) {
            $expense = $this->getExpense();
            return $expense->save();
        }
        return true;
    }

    public function cancel($saveExpense = true) {
        if (!$this->getIsCancellable()) {
            return false;
        }
        $this->setProperty('void', 0);
        $this->setProperty('cancelled', 1);
        if (!$this->save()) {
            return false;
        }
        if ($saveExpense) {
            $expense = $this->getExpense();
            return $expense->save();
        }
        return true;
    }
    
    public function isUnCancellable() {
        if (ProjectConfig::getIsQuickbooksIntegrated() && !empty($this->getProperty('cancelled'))) {
            return true;
        }
        return false;
    }
    
    public function unCancel() {
        if ($this->isUnCancellable()) {
            $this->setProperty('cancelled', 0);
            if (!$this->save()) {
                return false;
            }
            return true;
        }
        return false;
    }

    public function softDelete() {
        $expense = $this->getExpense();
        $search = new GI_DataSearch('item_link_to_expense_item');
        $search->filter('expense_item_id', $this->getProperty('id'))
                ->filter('status', 1);
        $linkDAOs = $search->select();
        if (!empty($linkDAOs)) {
            foreach ($linkDAOs as $linkDAO) {
                $linkDAO->setProperty('status', 0);
                if (!$linkDAO->save()) {
                    return false;
                }
            }
        }
        parent::softDelete();
        return $expense->save();
    }

    public function getIsLocked() {
        return false;
    }

}
