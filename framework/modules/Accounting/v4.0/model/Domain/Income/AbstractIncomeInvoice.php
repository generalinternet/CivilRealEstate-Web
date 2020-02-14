<?php

/**
 * Description of AbstractIncomeInvoice
 *
 * @author General Internet
 * @copyright  2018 General Internet
 * @version    3.0.0
 */
Abstract class AbstractIncomeInvoice extends AbstractIncome {
    
    protected $invoice;
    
    public function getInvoice() {
        if (empty($this->invoice)) {
            $this->invoice = InvoiceFactory::getInvoiceByIncome($this);
        }
        return $this->invoice;
    }

    public function getInvoiceNumber() {
        $invoice = $this->getInvoice();
        if (!empty($invoice)) {
            return $invoice->getProperty('invoice_number');
        }
        return '';
    }

    public function getInvoiceFinalizedDate() {
        $invoice = $this->getInvoice();
        if (!empty($invoice)) {
            $dateTimeString = $invoice->getProperty('finalized_date');
            if (!empty($dateTimeString)) {
                $dateTime = new DateTime($dateTimeString);
                return $dateTime->format('Y-m-d');
            }
        }
        return '';
    }

    public function getDate($formatForDisplay = false, $formatForExport = false) {
        $invoice = $this->getInvoice();
        if (empty($invoice)) {
            return parent::getDate($formatForDisplay, $formatForExport);
        }
        $date = $invoice->getProperty('date');
        if ($formatForDisplay) {
            $date = GI_Time::formatDateForDisplay($date);
        } else if ($formatForExport) {
            $dateTime = new DateTime($date . ' 00:00:00');
            $date = $dateTime->format('m/d/Y');
        }
        return $date;
    }

    public function getBillToString() {
        $invoice = $this->getInvoice();
        if (empty($invoice)) {
            return parent::getBillToString();
        }
        return $invoice->getBillToName();
    }
    
    public function save() {
        $invoice = $this->getInvoice();
        if (!empty($invoice)) {
            $invoiceDate = $invoice->getProperty('date');
            if (!empty($invoiceDate)) {
                $this->setProperty('date', $invoiceDate);
                $this->setProperty('applicable_date', $invoiceDate);
            }
        }
        return parent::save();
    }

}
