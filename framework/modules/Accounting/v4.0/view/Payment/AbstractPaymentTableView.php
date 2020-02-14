<?php
/**
 * Description of AbstractPaymentTableView
 *
 * @author General Internet
 * @copyright  2018 General Internet
 * @version    3.0.0
 */
abstract class AbstractPaymentTableView extends UITableView {

    /**
     * @param AbstractPayment[] $payments
     * @param AbstractUITableCol[] $uiTableCols
     * @param GI_PageBarView $pageBar
     */
    public function __construct($payments, $uiTableCols = NULL, GI_PageBarView $pageBar = NULL) {
        parent::__construct($payments, $uiTableCols, $pageBar);
    }

    protected function buildTableFooter() {
        $paymentTotal = 0;
        if (!empty($this->models)) {
            foreach ($this->models as $payment) {
                $paymentTotal += $payment->getProperty('amount');
            }
        }
        $emptyPadSpan = count($this->uiTableCols) - 2;
        $this->addHTML('<tfoot>');
        $this->addHTML('<tr>');
        $this->addHTML('<td class="empty" colspan="' . $emptyPadSpan . '"></td>');
        $this->addHTML('<th>Total</th>');
        $this->addHTML('<td>$'.GI_StringUtils::formatMoney($paymentTotal).'</td>');
        $this->addHTML('</tr>');
        $this->addHTML('</tfoot>');
    }

}
