<?php
/**
 * Description of AbstractIncomeItem
 *
 * @author General Internet
 * @copyright  2018 General Internet
 * @version    3.0.0
 */
abstract class AbstractIncomeItem extends AbstractAccountingElement {
    
    /** @var AbstractIncome */
    protected $income = NULL;

    /** @return AbstractIncome */
    public function getIncome() {
        if (empty($this->income)) {
            $incomeId = $this->getProperty('income_item.income_id');
            $income = IncomeFactory::getModelById($incomeId);
            $this->income = $income;
        }
        return $this->income;
    }

    public function getNumberOfModelsLinkedToIncomeItem() {
        return IncomeItemFactory::getNumberOfModelsLinkedToIncomeItem($this);
    }

    /** @return Currency */
    public function getCurrency(){
        $income = $this->getIncome();
        if($income){
            return $income->getCurrency();
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
        $sum  = $this->getNetTotal();
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
    
    public function getNetTotal($formatForDisplay = false, $showCurrency = false){
        $netAmount = $this->getProperty('net_amount');
        if ($formatForDisplay) {
            return $this->formatAmountForDisplay($netAmount, $showCurrency);
        }
        return $netAmount;
    }
    
    public function save() {
        $applicableDate = $this->getProperty('applicable_date');
        $income = $this->getIncome();
        if (empty($applicableDate)) {
            $incomeDate = $this->getProperty('inception');
            if (!empty($income)) {
                $incomeDate = $income->getProperty('applicable_date');
            }
            if (empty($incomeDate)) {
                $date = GI_Time::getDate();
                $this->setProperty('applicable_date', $date);
            } else {
                $this->setProperty('applicable_date', $incomeDate);
            }
        }
        return parent::save();
    }

    public function void($saveIncome = true) {
        if (!$this->getIsVoidable()) {
            return false;
        }
        $this->setProperty('void', 1);
        $this->setProperty('cancelled', 0);
        if (!$this->save()) {
            return false;
        }
        if ($saveIncome) {
            $income = $this->getIncome();
            return $income->save();
        }
        return true;
    }

    public function cancel($saveIncome = true) {
        if (!$this->getIsCancellable()) {
            return false;
        }
        $this->setProperty('void', 0);
        $this->setProperty('cancelled', 1);
        if (!$this->save()) {
            return false;
        }
        if ($saveIncome) {
            $income = $this->getIncome();
            return $income->save();
        }
        return true;
    }
    
    public function unCancel() {
        if (!empty($this->getProperty('cancelled'))) {
            $this->setProperty('cancelled', 0);
            if (!$this->save()) {
                return false;
            }
            return true;
        }
        return false;
    }

    public function softDelete() {
        $income = $this->getIncome();
        parent::softDelete();
        return $income->save();
    }

    public function getIsLocked() {
        return false;
    }

    public function getTaxTotals() {
        if ($this->getIsVoidOrCancelled()) {
            return 0;
        }
        $taxCodeQBId = $this->getProperty('tax_code_qb_id');
        $applicableDate = $this->getProperty('applicable_date');
        $netAmount = $this->getProperty('net_amount');
        $taxTotals = QBTaxCodeFactory::getQBTaxTotals($taxCodeQBId, $netAmount, $applicableDate, 'sales');

        return $taxTotals;
    }

}
