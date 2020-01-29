<?php

/**
 * Description of AbstractStripePaymentProcessorPaymentHistoryView
 *
 * @author General Internet
 * @copyright  2020 General Internet
 * @version    4.0.0
 */
abstract class AbstractStripePaymentProcessorPaymentHistoryView extends AbstractPaymentProcessorPaymentHistoryView {

    protected function addViewBodyContent() {
        if (empty($this->payments)) {
            $this->addHTML('<p>No payments found</p>');
        } else {
            $this->buildChargesTable();
        }
    }
    
    protected function buildChargesTable() {
        $this->addHTML('<div class="flex_table ui_table">');
        $this->buildChargesTableHeader();
        $this->buildChargesTableBody();
        $this->buildChargesTableFooter();
        $this->addHTML('</div>');
    }

    protected function buildChargesTableHeader() {
        $this->addHTML('<div class="flex_row flex_head">');
        $this->addHTML('<div class="flex_col">Date/Time</div>')
                ->addHTML('<div class="flex_col">Description</div>')
                ->addHTML('<div class="flex_col">Amount</div>')
                ->addHTML('<div class="flex_col">Payment Method</div>')
                ->addHTML('<div class="flex_col">Result</div>')
                ->addHTML('<div class="flex_col">Receipt</div>');
        $this->addHTML('</div>');
    }

    protected function buildChargesTableBody() {
        foreach ($this->payments as $chargeArray) {
            $this->buildChargesTableRow($chargeArray);
        }
    }

    protected function buildChargesTableRow($chargeArray) {
        $this->addHTML('<div class="flex_row">')
                ->addHTML('<div class="flex_col">');
        if (isset($chargeArray['date_time'])) {
            $this->addHTML(GI_Time::formatDateTimeForDisplay($chargeArray['date_time']));
        }
        $this->addHTML('</div>')
                ->addHTML('<div class="flex_col">');
        $this->addHTML($chargeArray['description']);
        $this->addHTML('</div>')
                ->addHTML('<div class="flex_col">');
            if (isset($chargeArray['amount'])) {
                $this->addHTML('$' . GI_StringUtils::formatMoney($chargeArray['amount']));
            }
            $this->addHTML('</div>')
                    ->addHTML('<div class="flex_col">');
            if (isset($chargeArray['card_brand']) && isset($chargeArray['card_last_four'])) {
                $this->addHTML($chargeArray['card_brand'] . ' ****' . $chargeArray['card_last_four']);
            }
            $this->addHTML('</div>')
                    ->addHTML('<div class="flex_col">');
            $this->addHTML($chargeArray['status']);
            $this->addHTML('</div>')
                    ->addHTML('<div class="flex_col">');
            if (isset($chargeArray['receipt_url'])) {
                $receiptURL = $chargeArray['receipt_url'];
                $this->addHTML('<a href="' . $receiptURL . '" target="_blank">'.GI_StringUtils::getIcon('print', false).'</a>');
            }
            $this->addHTML('</div>')
                    ->addHTML('</div>');
    }
    
    protected function buildChargesTableFooter() {
        
    }

}
