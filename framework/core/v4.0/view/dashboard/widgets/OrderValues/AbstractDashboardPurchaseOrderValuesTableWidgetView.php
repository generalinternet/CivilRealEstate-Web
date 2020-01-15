<?php
/**
 * Description of AbstractDashboardPurchaseOrderValuesTableWidgetView
 *
 * @author General Internet
 * @copyright  2019 General Internet
 * @version    4.0.0
 */
abstract class AbstractDashboardPurchaseOrderValuesTableWidgetView extends AbstractDashboardOrderValuesTableWidgetView {
    
    public function __construct($ref) {
        parent::__construct($ref);
        $this->setTitle('Purchase Order Values');
    }
    
    public function buildBodyContent() {
        if (!empty($this->report)) {
            $this->report->buildReport();
            $this->totals = $this->report->getTotals()['po'];
            $this->buildTable();
        }
    }
    
    protected function buildTable() {
        $this->addHTML('<div class="ui_table_wrap">');
        $this->addHTML('<table class="ui_table">');
        $this->buildTableHeader();
        $this->buildTableBody();
        $this->addHTML('</table>');
        $this->addHTML('</div>');       
    }

    protected function buildTableHeader() {
        $this->addHTML('<thead>')
                ->addHTML('<tr>')
                ->addHTML('<th></th>')
                ->addHTML('<th class="sml_col"></th>') 
                ->addHTML('<th>CAD</th>')
                ->addHTML('<th>USD</th>')
                ->addHTML('</tr>')
                ->addHTML('</thead>');
    }
    
    protected function buildTableBody() {
        $totals = $this->totals;
        if (!empty($totals)) {
            $this->buildPOTableRowPair('Daily', $totals['cad']['inp']['daily'], $totals['usd']['inp']['daily'], $totals['cad']['rec']['daily'], $totals['usd']['rec']['daily']);
            $this->buildPOTableRowPair('Week', $totals['cad']['inp']['week'], $totals['usd']['inp']['week'], $totals['cad']['rec']['week'], $totals['usd']['rec']['week']);
            $this->buildPOTableRowPair('Month', $totals['cad']['inp']['month'], $totals['usd']['inp']['month'], $totals['cad']['rec']['month'], $totals['usd']['rec']['month']);
            $this->buildPOTableRowPair('YTD', $totals['cad']['inp']['ytd'], $totals['usd']['inp']['ytd'], $totals['cad']['rec']['ytd'], $totals['usd']['rec']['ytd']);
        }
    }

    protected function buildPOTableRowPair($label, $cadInProgressValue, $usdInProgressValue, $cadReceivedValue, $usdReceivedValue) {
        $this->addHTML('<tr>');
        $this->buildTableRow($label, 'In Progress', $cadInProgressValue, $usdInProgressValue);
        $this->addHTML('</tr><tr>');
        $this->buildTableRow($label, 'Received', $cadReceivedValue, $usdReceivedValue);
        $this->addHTML('</tr>');
    }
    
    protected function determineIsViewable() {
        if (!Permission::verifyByRef('view_purchase_order_values_dashboard_widget')) {
            return false;
        }
        return parent::determineIsViewable();
    }

    protected function buildTableRow($label, $status, $cadValue, $usdValue) {
        if ($status == 'In Progress') {
            $status = '<span class="icon gray time" title="In Progress"></span>';
        } else {
            $status = '<span class="icon gray check" title="Received"></span>';
        }
        $formattedCADValue = GI_StringUtils::formatMoney($cadValue);
        $formattedUSDValue = GI_StringUtils::formatMoney($usdValue);
        $this->addHTML('<td>')
                ->addHTML($label)
                ->addHTML('</td>')
                ->addHTML('<td class="sml_col">')
                ->addHTML($status)
                ->addHTML('</td>')
                ->addHTML('<td title="$'.$formattedCADValue.'">')
                ->addHTML('$' . $formattedCADValue)
                ->addHTML('</td>')
                ->addHTML('<td title="$'.$formattedUSDValue.'">')
                ->addHTML('$' . $formattedUSDValue)
                ->addHTML('</td>');
    }

}
