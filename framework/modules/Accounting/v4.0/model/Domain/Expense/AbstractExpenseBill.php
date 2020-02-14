<?php
/**
 * Description of AbstractExpenseBill
 *
 * @author General Internet
 * @copyright  2018 General Internet
 * @version    3.0.0
 */
Abstract class AbstractExpenseBill extends AbstractExpense {
    
    protected $bill = NULL;

    public function getBillNumber() {
        $bill = $this->getBill();
        if (!empty($bill)) {
            return $bill->getProperty('bill_number');
        }
        return '';
    }

    public function getBill() {
        if (empty($this->bill)) {
            $this->bill = BillFactory::getBillByExpense($this);
        }
        return $this->bill;
    }

    public function getDate($formatForDisplay = false, $formatForExport = false) {
        $bill = $this->getBill();
        if (empty($bill)) {
            return parent::getDate($formatForDisplay, $formatForExport);
        }
        $date = $bill->getProperty('date');
        if ($formatForDisplay) {
            $date = GI_Time::formatDateForDisplay($date);
        } else if ($formatForExport) {
            $dateTime = new DateTime($date . ' 00:00:00');
            $date = $dateTime->format('m/d/Y');
        }
        return $date;
    }
    
    public function getPayToString() {
        $bill = $this->getBill();
        if (empty($bill)) {
            return parent::getPayToString();
        }
        return $bill->getContactName();
    }

}
